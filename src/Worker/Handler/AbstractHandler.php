<?php
/**
 * This class provides a code basis for all worker command handlers.
 *
 * @abstract
 */

namespace Maleficarum\Worker\Handler;

abstract class AbstractHandler
{
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
     * Internal storage for command handler id.
     *
     * @var string
     */
    private $chId = '__';

    /**
     * Internal storage for worker id.
     *
     * @var string
     */
    private $workerId;

    /**
     * Internal storage for a command object.
     *
     * @var \Maleficarum\Worker\Command\AbstractCommand
     */
    private $command = null;

    /**
     * Handle the incoming command. Return true on success, false otherwise. If false is returned the command is not considered handled so it will not be acknowledged.
     *
     * @return bool
     */
    abstract public function handle();

    /**
     * Add a new command to the queue (this will automatically attach parent handler id)
     *
     * @param \Maleficarum\Worker\Command\AbstractCommand $cmd
     *
     * @return \Maleficarum\Worker\Handler\AbstractHandler
     */
    public function addCommand(\Maleficarum\Worker\Command\AbstractCommand $cmd) {
        $this
            ->getQueue()
            ->addCommand($cmd->setParentHandlerId($this->getChId()));

        return $this;
    }

    /**
     * Add collection of commands to the queue
     * 
     * @param array|\Maleficarum\Worker\Command\AbstractCommand[] $cmds
     *
     * @return \Maleficarum\Worker\Handler\AbstractHandler
     */
    public function addCommands(array $cmds) {
        foreach ($cmds as $cmd) {
            $cmd->setParentHandlerId($this->getChId());
        }

        $this
            ->getQueue()
            ->addCommands($cmds);

        return $this;
    }

    /**
     * Set current worker id.
     *
     * @param string $workerId
     *
     * @return \Maleficarum\Worker\Handler\AbstractHandler
     */
    public function setWorkerId($workerId) {
        $this->workerId = $workerId;

        return $this;
    }

    /**
     * Fetch current worker id.
     *
     * @return string
     */
    private function getWorkerId() {
        return $this->workerId;
    }

    /**
     * Send specified message to the log.
     *
     * @param string $message
     *
     * @return \Maleficarum\Worker\Handler\AbstractHandler
     */
    protected function log($message) {
        $this
            ->getLogger()
            ->log('[' . $this->getWorkerId() . '] ' . '[' . $this->getChId() . '] ' . $message, 'PHP Worker Info');

        return $this;
    }

    /**
     * Set current command handler id string.
     *
     * @param string $chId
     *
     * @return \Maleficarum\Worker\Handler\AbstractHandler
     */
    public function setChId($chId) {
        $this->chId = $chId;

        return $this;
    }

    /**
     * Return current command handler id string.
     *
     * @return string
     */
    public function getChId() {
        if ($this->getCommand() instanceof \Maleficarum\Worker\Command\AbstractCommand) {
            $parent = $this->getCommand()->getParentHandlerId();
        } else {
            $parent = null;
        }

        if (!empty($parent)) {
            $return[] = $parent;
        }

        $return[] = $this->chId;

        return implode('/', $return);
    }

    /**
     * Set current command.
     *
     * @param \Maleficarum\Worker\Command\AbstractCommand $cmd
     *
     * @return \Maleficarum\Worker\Handler\AbstractHandler
     */
    public function setCommand(\Maleficarum\Worker\Command\AbstractCommand $cmd) {
        $this->command = $cmd;

        return $this;
    }

    /**
     * Fetch current command.
     *
     * @return \Maleficarum\Worker\Command\AbstractCommand
     */
    public function getCommand() {
        return $this->command;
    }
}
