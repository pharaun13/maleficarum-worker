<?php
/**
 * This abstract class defines functionalities common to all handler encapsulators.
 */
declare (strict_types=1);

namespace Maleficarum\Worker\Handler\Encapsulator;

abstract class AbstractEncapsulator {
    /* ------------------------------------ Class Property START --------------------------------------- */

    /**
     * Internal storage for the handler object.
     * 
     * @var \Maleficarum\Worker\Handler\AbstractHandler
     */
    private $handler = null;
    
    /* ------------------------------------ Class Property END ----------------------------------------- */
    
    /* ------------------------------------ Abstract methods START ------------------------------------- */
    
    /**
     * Perform any logic that this encapsulator needs to execute before the handle logic is called.
     *
     * @return bool
     */
    abstract public function beforeHandle() : bool;

    /**
     * Perform any logic that this encapsulator needs to execute after the handle logic is called.
     *
     * @param bool $result
     * @return bool
     */
    abstract public function afterHandle(bool $result) : bool;
    
    /* ------------------------------------ Abstract methods END --------------------------------------- */

    /* ------------------------------------ Class Methods START ---------------------------------------- */

    /**
     * Send a message to the logger.
     * 
     * @param string $message
     * @param string $level
     * @return \Maleficarum\Worker\Handler\Encapsulator\AbstractEncapsulator
     */
    public function log(string $message, string $level = 'PHP Worker Info') : \Maleficarum\Worker\Handler\Encapsulator\AbstractEncapsulator {
        $this->getHandler()->log($message, $level);
        return $this;
    }
    
    /* ------------------------------------ Class Methods END ------------------------------------------ */
    
    /* ------------------------------------ Magic methods START ---------------------------------------- */

    /**
     * AbstractEncapsulator constructor.
     *
     * @param \Maleficarum\Worker\Handler\AbstractHandler $handler
     */
    public function __construct(\Maleficarum\Worker\Handler\AbstractHandler $handler) {
        $this->setHandler($handler);
    }

    /* ------------------------------------ Magic methods END ------------------------------------------ */
    
    /* ------------------------------------ Setters & Getters START ------------------------------------ */

    /**
     * @return \Maleficarum\Worker\Handler\AbstractHandler
     */
    public function getHandler() : \Maleficarum\Worker\Handler\AbstractHandler {
        return $this->handler;
    }
    
    /**
     * @param \Maleficarum\Worker\Handler\AbstractHandler $handler
     * @return \Maleficarum\Worker\Handler\Encapsulator\AbstractEncapsulator
     */
    public function setHandler(\Maleficarum\Worker\Handler\AbstractHandler $handler) : \Maleficarum\Worker\Handler\Encapsulator\AbstractEncapsulator {
        $this->handler = $handler;
        return $this;
    }

    /* ------------------------------------ Setters & Getters END -------------------------------------- */
}