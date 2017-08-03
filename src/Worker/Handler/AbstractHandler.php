<?php
/**
 * This class provides a code basis for all worker command handlers.
 *
 * @abstract
 */

namespace Maleficarum\Worker\Handler;

abstract class AbstractHandler {
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
    private $handlerId = '__';

    /**
     * Internal storage for worker id.
     *
     * @var string
     */
    private $workerId;

    /**
     * Internal storage for a command object.
     *
     * @var \Maleficarum\Command\AbstractCommand
     */
    private $command = null;

    /* ------------------------------------ AbstractHandler methods START ------------------------------ */
    /**
     * Send specified message to the log.
     *
     * @param string $message
     *
     * @return \Maleficarum\Worker\Handler\AbstractHandler
     */
    protected function log(string $message): \Maleficarum\Worker\Handler\AbstractHandler {
        $this
            ->getLogger()
            ->log('[' . $this->getWorkerId() . '] ' . '[' . $this->getHandlerId() . '] ' . $message, 'PHP Worker Info');

        return $this;
    }
    /* ------------------------------------ AbstractHandler methods END -------------------------------- */

    /* ------------------------------------ Abstract methods START ------------------------------------- */
    /**
     * Handle the incoming command. Return true on success, false otherwise. If false is returned the command is not considered handled so it will not be acknowledged.
     *
     * @return bool
     */
    abstract public function handle(): bool;
    /* ------------------------------------ Abstract methods END --------------------------------------- */

    /* ------------------------------------ Setters & Getters START ------------------------------------ */
    /**
     * Set current command handler id string.
     *
     * @param string $handlerId
     *
     * @return \Maleficarum\Worker\Handler\AbstractHandler
     */
    public function setHandlerId(string $handlerId): \Maleficarum\Worker\Handler\AbstractHandler {
        $this->handlerId = $handlerId;

        return $this;
    }

    /**
     * Return current command handler id string.
     *
     * @return string
     */
    public function getHandlerId(): string {
        if ($this->getCommand() instanceof \Maleficarum\Command\AbstractCommand) {
            $parent = $this->getCommand()->getParentHandlerId();
        } else {
            $parent = null;
        }

        if (!empty($parent)) {
            $return[] = $parent;
        }

        $return[] = $this->handlerId;

        return implode('/', $return);
    }

    /**
     * Set current worker id.
     *
     * @param string $workerId
     *
     * @return \Maleficarum\Worker\Handler\AbstractHandler
     */
    public function setWorkerId(string $workerId): \Maleficarum\Worker\Handler\AbstractHandler {
        $this->workerId = $workerId;

        return $this;
    }

    /**
     * Fetch current worker id.
     *
     * @return null|string
     */
    private function getWorkerId(): ?string {
        return $this->workerId;
    }

    /**
     * Set current command.
     *
     * @param \Maleficarum\Command\AbstractCommand $command
     *
     * @return \Maleficarum\Worker\Handler\AbstractHandler
     */
    public function setCommand(\Maleficarum\Command\AbstractCommand $command): \Maleficarum\Worker\Handler\AbstractHandler {
        $this->command = $command;

        return $this;
    }

    /**
     * Add a new command to the queue (this will automatically attach parent handler id)
     *
     * @param \Maleficarum\Command\AbstractCommand $cmd
     *
     * @return \Maleficarum\Worker\Handler\AbstractHandler
     */
    public function addCommand(\Maleficarum\Command\AbstractCommand $cmd): \Maleficarum\Worker\Handler\AbstractHandler {
        $this
            ->getQueue()
            ->addCommand($cmd->setParentHandlerId($this->getHandlerId()));

        return $this;
    }

    /**
     * Add collection of commands to the queue
     *
     * @param array|\Maleficarum\Command\AbstractCommand[] $commands
     *
     * @return \Maleficarum\Worker\Handler\AbstractHandler
     */
    public function addCommands(array $commands): \Maleficarum\Worker\Handler\AbstractHandler {
        foreach ($commands as $command) {
            $command->setParentHandlerId($this->getHandlerId());
        }

        $this
            ->getQueue()
            ->addCommands($commands);

        return $this;
    }

    /**
     * Fetch current command.
     *
     * @return \Maleficarum\Command\AbstractCommand|null
     */
    public function getCommand(): ?\Maleficarum\Command\AbstractCommand {
        return $this->command;
    }
    /* ------------------------------------ Setters & Getters END -------------------------------------- */
}
