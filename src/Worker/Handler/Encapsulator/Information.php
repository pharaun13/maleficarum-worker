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

    /* ------------------------------------ Class Property START --------------------------------------- */

    /**
     * Internal storage for the handle execution start time.
     * 
     * @var int
     */
    private $start = 0;
    
    /* ------------------------------------ Class Property END ----------------------------------------- */
    
    /* ------------------------------------ Interface methods START ------------------------------------ */
    
    /**
     * @see \Maleficarum\Worker\Handler\Encapsulator\Encapsulator::beforeHandle()
     */
    public function beforeHandle(\Maleficarum\Worker\Handler\AbstractHandler $handler): bool {
        $handler->log('Received command. Type: ' . $handler->getCommand()->getType() . ' || Data: ' . $handler->getCommand(), 'PHP Worker Info');
        $this->getProfiler('time')->begin();

        return true;
    }

    /**
     * @see \Maleficarum\Worker\Handler\Encapsulator\Encapsulator::afterHandle()
     */
    public function afterHandle(\Maleficarum\Worker\Handler\AbstractHandler $handler, bool $result): bool {
        $exec = round($this->getProfiler('time')->end()->getProfile(), 4);

        $result and $handler->log('Command handler COMPLETE. Type: ' . $handler->getCommand()->getType() . ' [Exec time: ' . $exec . 's]', 'PHP Worker Info');
        $result or $handler->log('Command handler FAILED - command dropped. Type: ' . $handler->getCommand()->getType() . ' [Exec time: ' . $exec . 's]', 'PHP Worker Info');
        return true;
    }
    
    /* ------------------------------------ Interface methods END -------------------------------------- */
}