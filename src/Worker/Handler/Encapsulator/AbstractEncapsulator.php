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
     * @param \Maleficarum\Worker\Handler\AbstractHandler $handler
     * @return bool
     */
    abstract public function beforeHandle(\Maleficarum\Worker\Handler\AbstractHandler $handler) : bool;

    /**
     * Perform any logic that this encapsulator needs to execute after the handle logic is called.
     *
     * @param \Maleficarum\Worker\Handler\AbstractHandler $handler
     * @param bool $result
     * @return bool
     */
    abstract public function afterHandle(\Maleficarum\Worker\Handler\AbstractHandler $handler, bool $result) : bool;
    
    /* ------------------------------------ Abstract methods END --------------------------------------- */

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