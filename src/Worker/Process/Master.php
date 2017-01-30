<?php
/**
 * This class is responsible for main worker setup and handler dispatch.
 */

namespace Maleficarum\Worker\Process;

class Master
{
    /**
     * Use \Maleficarum\Config\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Config\Dependant;

    /**
     * Use \Maleficarum\Worker\Logger\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Worker\Logger\Dependant;

    /**
     * Use \Maleficarum\Profiler\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Profiler\Dependant;

    /**
     * Use \Maleficarum\Rabbitmq\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Rabbitmq\Dependant;

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

    /**
     * Initialize the master process - connect to the rabbitMQ broker.
     *
     * @param string $name
     * @param string $channel
     *
     * @return \Maleficarum\Worker\Process\Master
     */
    public function init(string $name, string $channel) : \Maleficarum\Worker\Process\Master {
        $this->getQueue()->init();
        $this->name = $name;
        $this->channel = $channel;

        return $this;
    }

    /**
     * This is the main loop that fetches messages from the rabbitMQ broker and dispenses them into command handlers.
     *
     * @return \Maleficarum\Worker\Process\Master
     */
    public function execute() : \Maleficarum\Worker\Process\Master {
        $channel = $this->getQueue()->getChannel($this->channel);
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($this->getConfig()['queue']['commands']['queue-name'], '', false, false, false, false, [$this, 'handleCommand']);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        return $this;
    }

    /**
     * Perform any cleanup before terminating.
     *
     * @return \Maleficarum\Worker\Process\Master
     */
    public function conclude() : \Maleficarum\Worker\Process\Master {
        $this->getQueue()->close();

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
            $command = \Maleficarum\Worker\Command\AbstractCommand::decode($message->body);
        } catch (\Exception $e) {
            $this->getLogger()->log('[' . $this->name . '] Received command of unknown structure (NOT JSON).', 'PHP Worker Error');
            $message->delivery_info['channel']->basic_nack($message->delivery_info['delivery_tag']);

            return false;
        }

        // received message is a command of unsupported type
        if (!$command instanceof \Maleficarum\Worker\Command\AbstractCommand) {
            $this->getLogger()->log('[' . $this->name . '] Received command of unknown type.', 'PHP Worker Error');
            $message->delivery_info['channel']->basic_nack($message->delivery_info['delivery_tag']);

            return false;
        }

        $this->getProfiler('time')->clear()->begin();
        /* @var \Maleficarum\Worker\Handler\AbstractHandler $handler */
        $handler = \Maleficarum\Ioc\Container::get('Handler\\' . $command->getType());
        $handler
            ->setWorkerId($this->name)
            ->setHandlerId(uniqid('HID-'))
            ->setCommand($command);

        $this->getLogger()->log('[' . $this->name . '] [' . $handler->getHandlerId() . '] Received command. Type: ' . $command->getType() . ' Data: ' . $command, 'PHP Worker Info');

        if ($handler->handle()) {
            $exec = round($this->getProfiler('time')->end()->getProfile(), 4);
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
            $this->getLogger()->log('[' . $this->name . '] [' . $handler->getHandlerId() . '] Command handler COMPLETE. Type: ' . $command->getType() . ' [Exec time: ' . $exec . 's]', 'PHP Worker Info');
        } else {
            $exec = round($this->getProfiler('time')->end()->getProfile(), 4);
            $message->delivery_info['channel']->basic_nack($message->delivery_info['delivery_tag'], false, true);
            $this->getLogger()->log('[' . $this->name . '] [' . $handler->getHandlerId() . '] Command handler FAILED - command requeued. Type: ' . $command->getType() . ' [Exec time: ' . $exec . 's]', 'PHP Worker Info');
        }

        return $this;
    }
}
