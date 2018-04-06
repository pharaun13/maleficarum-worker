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

    /**
     * Internal storage for the registry array used to pass data between the handler and its encapsulators.
     * 
     * @var array 
     */
    private $registry = [];
    
    /**
     * Internal storage for the list 
     * 
     * @var array 
     */
    private $encapsulators = [
        'Maleficarum\Worker\Handler\Encapsulator\Information'
    ];

    /* ------------------------------------ Abstract methods START ------------------------------------- */
    /**
     * Handle the incoming command. Return true on success, false otherwise. If false is returned the command is not considered handled so it will not be acknowledged.
     *
     * @return bool
     */
    abstract public function handle() : bool;
    /* ------------------------------------ Abstract methods END --------------------------------------- */

    /* ------------------------------------ Class Methods START ---------------------------------------- */

    /**
     * Send specified message to the log.
     *
     * @param string $message
     *
     * @return \Maleficarum\Worker\Handler\AbstractHandler
     */
    public function log(string $message): \Maleficarum\Worker\Handler\AbstractHandler {
        $this
            ->getLogger()
            ->log('[' . $this->getWorkerId() . '] ' . '[' . $this->getHandlerId() . '] ' . $message, 'PHP Worker Info');

        return $this;
    }
    
    /**
     * Process the handler. This executes any pre encapsulator logic, proceeds to the handle logic and follows with the encapsulator post logic.
     * 
     * @return bool
     */
    public function process() : bool {
        // create all encapsulators
        $encs = [];
        foreach ($this->getEncapsulators() as $enc) {
            $enc = \Maleficarum\Ioc\Container::get($enc, [$this]);
            if (!$enc instanceof \Maleficarum\Worker\Handler\Encapsulator\AbstractEncapsulator) throw new \RuntimeException(sprintf('Classes specified as handler encapsulators MUST implement the \Maleficarum\Worker\Handler\Encapsulator\Encapsulator interface. %s', __METHOD__));
            $encs[] = $enc;
        }
        
        // pre handle encapsulation
        foreach ($encs as $enc) $enc->beforeHandle();
        
        $result = $this->handle();
        
        // post handle encapsulation
        foreach (array_reverse($encs) as $enc) $enc->afterHandle($result);
        
        return $result;
    }
    
    /* ------------------------------------ Class Methods END ------------------------------------------ */
    
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
    public function getWorkerId(): ?string {
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
     * Fetch current command.
     *
     * @return \Maleficarum\Command\AbstractCommand|null
     */
    public function getCommand(): ?\Maleficarum\Command\AbstractCommand {
        return $this->command;
    }
    
    /**
     * Fetch current registry object.
     * 
     * @return array
     */
    public function getRegistry() : array {
        return $this->registry;
    }
    
    /**
     * Set a new registry object.
     * 
     * @param array $registry
     * @return \Maleficarum\Worker\Handler\AbstractHandler
     */
    public function setRegistry(array $registry) : \Maleficarum\Worker\Handler\AbstractHandler {
        $this->registry = $registry;
        return $this;
    }

    /**
     * Fetch all encapsulators.
     * 
     * @return array
     */
    protected function getEncapsulators() : array {
        return $this->encapsulators;
    }

    /**
     * Set new encapsulators.
     * 
     * @param array $encapsulators
     */
    private function setEncapsulators(array $encapsulators) : \Maleficarum\Worker\Handler\AbstractHandler {
        $this->encapsulators = $encapsulators;
        return $this;
    }
    
    /* ------------------------------------ Setters & Getters END -------------------------------------- */
}
