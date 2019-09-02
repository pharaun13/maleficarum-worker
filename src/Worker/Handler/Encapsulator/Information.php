<?php
/**
 * This encapsulator will time the execution of the command and log it after the handling is complete.
 */
declare (strict_types=1);

namespace Maleficarum\Worker\Handler\Encapsulator;

class Information extends \Maleficarum\Worker\Handler\Encapsulator\AbstractEncapsulator {
    /* ------------------------------------ Class Traits START ----------------------------------------- */

    /**
     * Use \Maleficarum\Profiler\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Profiler\Dependant;
    
    /* ------------------------------------ Class Traits END ------------------------------------------- */
    
    /* ------------------------------------ Interface methods START ------------------------------------ */
    
    /**
     * @see \Maleficarum\Worker\Handler\Encapsulator\Encapsulator::beforeHandle()
     */
    public function beforeHandle() : bool {
        $this->log('Received command. Type: ' . $this->getHandler()->getCommand()->getType() . ' || Meta: ' . json_encode($this->getHandler()->getCommand()->getCommandMetaData()) . ' || Data: ' . $this->getHandler()->getCommand() . ' || Test Mode: ' . json_encode($this->getHandler()->getCommand()->getTestMode()));
        $this->getProfiler('time')->begin();

        return true;
    }

    /**
     * @see \Maleficarum\Worker\Handler\Encapsulator\Encapsulator::afterHandle()
     */
    public function afterHandle(bool $result): bool {
        $exec = round($this->getProfiler('time')->end()->getProfile(), 4);

        $result and $this->log('Command handler COMPLETE. Type: ' . $this->getHandler()->getCommand()->getType() . ' [Exec time: ' . $exec . 's]');
        $result or $this->log('Command handler FAILED - command dropped. Type: ' . $this->getHandler()->getCommand()->getType() . ' [Exec time: ' . $exec . 's]');
        return true;
    }
    
    /* ------------------------------------ Interface methods END -------------------------------------- */
}