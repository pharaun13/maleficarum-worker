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
    public function init($name, $channel) {
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
    public function execute() {
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
    public function conclude() {
        $this->getQueue()->close();

        return $this;
    }

    /**
     * Handle an incoming command.
     *
     * @param \PhpAmqpLib\Message\AMQPMessage $msg
     *
     * @return \Maleficarum\Worker\Process\Master
     */
    public function handleCommand(\PhpAmqpLib\Message\AMQPMessage $msg) {
        try {
            $cmd = \Maleficarum\Worker\Command\AbstractCommand::decode($msg->body);
        } catch (\Exception $e) {
            $this->getLogger()->log('[' . $this->name . '] Received command of unknown structure (NOT JSON).', 'PHP Worker Error');
            $msg->delivery_info['channel']->basic_nack($msg->delivery_info['delivery_tag']);

            return false;
        }

        // received message is a command of unsupported type
        if (!$cmd instanceof \Maleficarum\Worker\Command\AbstractCommand) {
            $this->getLogger()->log('[' . $this->name . '] Received command of unknown type.', 'PHP Worker Error');
            $msg->delivery_info['channel']->basic_nack($msg->delivery_info['delivery_tag']);

            return false;
        }

        $this->getProfiler('time')->clear()->begin();
        $handler = \Maleficarum\Ioc\Container::get('Handler\\' . $cmd->getType());
        /* @var \Maleficarum\Worker\Handler\AbstractHandler $handler */
        $handler
            ->setWorkerId($this->name)
            ->setChId(uniqid('HID-'))
            ->setCommand($cmd);

        $this->getLogger()->log('[' . $this->name . '] [' . $handler->getChId() . '] Received command. Type: ' . $cmd->getType() . ' Data: ' . $cmd, 'PHP Worker Info');

        if ($handler->handle()) {
            $exec = round($this->getProfiler('time')->end()->getProfile(), 4);
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            $this->getLogger()->log('[' . $this->name . '] [' . $handler->getChId() . '] Command handler COMPLETE. Type: ' . $cmd->getType() . ' [Exec time: ' . $exec . 's]', 'PHP Worker Info');
        } else {
            $exec = round($this->getProfiler('time')->end()->getProfile(), 4);
            $msg->delivery_info['channel']->basic_nack($msg->delivery_info['delivery_tag'], false, true);
            $this->getLogger()->log('[' . $this->name . '] [' . $handler->getChId() . '] Command handler FAILED - command requeued. Type: ' . $cmd->getType() . ' [Exec time: ' . $exec . 's]', 'PHP Worker Info');
        }

        return $this;
    }
}
