<?php
/**
 * This class is responsible for main worker setup and handler dispatch.
 */

namespace Maleficarum\Worker\Process;

class Master {
    /* ------------------------------------ Class Traits START ----------------------------------------- */
    
    /**
     * Use \Maleficarum\Worker\Logger\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Worker\Logger\Dependant;
    
    /**
     * Use \Maleficarum\Rabbitmq\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Rabbitmq\Dependant;

    /* ------------------------------------ Class Traits END ------------------------------------------- */

    /* ------------------------------------ Class Property START --------------------------------------- */

    /**
     * Define source execution strategy constants.
     * @const
     */
    private const EXEC_STRATEGY_SINGLE = 0;
    private const EXEC_STRATEGY_MULTI = 1;
    
    /**
     * Name of this worker - used when sending output to logger.
     *
     * @var string
     */
    private $name = '';

    /**
     * Internal storage for the subscribe channel ID
     *
     * @var int
     */
    private $channel = 1;

    /* ------------------------------------ Class Methods START ---------------------------------------- */
    
    /**
     * Initialize the master process - connect to the rabbitMQ broker.
     *
     * @param string $name
     * @param string $channel
     *
     * @return \Maleficarum\Worker\Process\Master
     */
    public function init(string $name, string $channel): \Maleficarum\Worker\Process\Master {
        $this->name = $name;
        $this->channel = $channel;

        return $this;
    }
    
    /**
     * This is the main loop that fetches messages from the rabbitMQ broker and dispenses them into command handlers.
     *
     * @throws \RuntimeException
     * @return \Maleficarum\Worker\Process\Master
     */
    public function execute(): \Maleficarum\Worker\Process\Master {
        // get all source connections - this will be used to figure out which execution strategy to follow
        $sources = $this->getQueue()->fetchSources();
        
        // the worker master process cannot operate without any active sources
        if (!count($sources)) throw new \RuntimeException(sprintf('No command sources available - terminating. %s', __METHOD__));
        
        // based on the source list - establish which strategy to use 
        $strategy = self::EXEC_STRATEGY_SINGLE;
        
        // more than one priority - multi connection strategy
        count($sources) > 1 and $strategy = self::EXEC_STRATEGY_MULTI;
        
        // more than one connection in the top priority - multi connection strategy
        $strategy === self::EXEC_STRATEGY_SINGLE && count(array_shift($sources)) > 1 and $strategy = self::EXEC_STRATEGY_MULTI;
        
        if (self::EXEC_STRATEGY_SINGLE === $strategy) $this->executeSingle();
        if (self::EXEC_STRATEGY_MULTI === $strategy) $this->executeMulti();

        return $this;
    }
    
    /**
     * Execute the worker process loop in single connection mode.
     * 
     * @return \Maleficarum\Worker\Process\Master
     */
    private function executeSingle() : \Maleficarum\Worker\Process\Master {
        // extract the single connection to use - double array shift is necessary due to PHP limitations on references
        $connection = $this->getQueue()->fetchSources();
        $connection = array_shift($connection);
        $connection = array_shift($connection);
        
        // get the channel from the connection
        $channel = $connection->getChannel($this->channel);
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($connection->getQueueName(), '', false, false, false, false, [$this, 'handleCommand']);

        // log the current operations mode
        $this->getLogger()->log('[' . $this->name . '] Worker initialized in single source mode.', 'PHP Worker Info');
        
        // execute the consumer loop
        while (count($channel->callbacks)) {
            $channel->wait();
        }

        return $this;
    }
    
    /**
     * Execute the worker process loop in multi connection mode.
     * 
     * @return \Maleficarum\Worker\Process\Master
     */
    private function executeMulti() : \Maleficarum\Worker\Process\Master {
        // initialize all the necessary variables
        $writeSockets = null;
        $exceptSockets = null;
        $sockets = [];
        $channels = [];
        
        // fetch all source connections for manipulation 
        $connections = $this->getQueue()->fetchSources();
        
        // get channels and sockets for each source connection
        foreach ($connections as $priority_key => $priority) {
            foreach ($priority as $source) {
                $sockets[] = $source->getConnection()->getSocket();
                $chan          = $source->getChannel($this->channel);

                array_key_exists($priority_key, $channels) or $channels[$priority_key] = [];
                $channels[$priority_key][]    = $chan;
                $chan->basic_qos(null, 1, null);
                $chan->basic_consume($source->getQueueName(), '', false, false, false, false, [$this, 'handleCommand']);
            }
        }

        // log the current operations mode
        $this->getLogger()->log('[' . $this->name . '] Worker initialized in multi source mode. (sources: '.count($sockets).', pririoties: '.count($channels).')', 'PHP Worker Info');
        
        // execute the consumer loop
        while (true) {
            // we need to recover the full list of sockets since stream_select will overwrite it with a list of sockets that changed
            $readSockets = $sockets;
            
            // select sockets that are ready for reads (from the list of all source sockets)
            if (false === ($numberChangedSockets = stream_select($readSockets, $writeSockets, $exceptSockets, null))) {
                $this->getLogger()->log('[' . $this->name . '] Multi source mode error - stream select call has failed.', 'PHP Worker Error');
            } elseif ($numberChangedSockets > 0) {
                // iterate over the channel structure - since it carries the priority structure we will iterate over channels within a priority and break the loop one something in that priority happens
                // this way channels with higher priority will consume first (round robin distribution for all channels with the same priority as the first channel that was ready to consume) while
                // lower priority channels will have to wait (the upper loop break ensures that)
                foreach ($channels as $key => $prio) {
                    $executed = false;
                    foreach ($prio as $channel) {
                        if (in_array($channel->getConnection()->getSocket(), $readSockets, true)) {
                            $executed = true;
                            $channel->wait();
                        }
                    }

                    if ($executed) break;
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Handle an incoming command.
     *
     * @param \PhpAmqpLib\Message\AMQPMessage $message
     *
     * @return \Maleficarum\Worker\Process\Master|bool
     */
    public function handleCommand(\PhpAmqpLib\Message\AMQPMessage $message) {
        try {
            $command = \Maleficarum\Command\AbstractCommand::decode($message->body);
        } catch (\Throwable $t) {
            $this->getLogger()->log('[' . $this->name . '] Received command of unknown structure (NOT JSON). [content: '.$message->body.']', 'PHP Worker Error');
            $message->delivery_info['channel']->basic_nack($message->delivery_info['delivery_tag']);

            return false;
        }

        // received message is a command of unsupported type
        if (!$command instanceof \Maleficarum\Command\AbstractCommand) {
            $this->getLogger()->log('[' . $this->name . '] Received command of unknown type.', 'PHP Worker Error');
            $message->delivery_info['channel']->basic_nack($message->delivery_info['delivery_tag']);

            return false;
        }

        //$this->getProfiler('time')->clear()->begin();
        /* @var \Maleficarum\Worker\Handler\AbstractHandler $handler */
        $handler = \Maleficarum\Ioc\Container::get('Handler\\' . $command->getType());
        $handler
            ->setWorkerId($this->name)
            ->setHandlerId(uniqid('HID-'))
            ->setCommand($command);

        if ($handler->process()) {
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        } else {
            $message->delivery_info['channel']->basic_nack($message->delivery_info['delivery_tag'], false, true);
        }

        return $this;
    }

    /**
     * Perform any cleanup before terminating.
     *
     * @return \Maleficarum\Worker\Process\Master
     */
    public function conclude(): \Maleficarum\Worker\Process\Master {
        return $this;
    }
    
    /* ------------------------------------ Class Methods END ------------------------------------------ */
}
