<?php
/**
 * This encapsulator will check the handler result and if it was marked as false will attempt to add the handler command to a deadletter queue.
 */
declare (strict_types=1);

namespace Maleficarum\Worker\Handler\Encapsulator;

class Deadletter extends \Maleficarum\Worker\Handler\Encapsulator\AbstractEncapsulator {
    /* ------------------------------------ Class Traits START ----------------------------------------- */

    /**
     * Use \Maleficarum\Rabbitmq\Dependant functionality.
     *
     * @trait
     */
    use \Maleficarum\Rabbitmq\Dependant;

    /* ------------------------------------ Class Traits END ------------------------------------------- */

    /* ------------------------------------ Interface methods START ------------------------------------ */

    /**
     * @see \Maleficarum\Worker\Handler\Encapsulator\Encapsulator::beforeHandle()
     */
    public function beforeHandle() : bool {
        return true;
    }

    /**
     * @see \Maleficarum\Worker\Handler\Encapsulator\Encapsulator::afterHandle()
     */
    public function afterHandle(bool $result): bool {
        // only try to add the command to a deadletter queue if the main handler failed
        if (false === $result) {
            $registry = $this->getHandler()->getRegistry();
            
            // add to deadletter - only if the registry does not explicitly stats that it should be skipped.
            if (!isset($registry['deadletter']['skip'])) {
                try {
                    $this->getQueue()->addCommand($this->getHandler()->getCommand(), 'deadletter');
                    $this->log('Deadletter encapsulator activated - message was added to the deadletter queue.');
                } catch (\InvalidArgumentException $e) {
                    $this->log('Deadletter encapsulator activated but the deadletter connection was not configured - message was not added to the deadletter queue.');
                }
            } else {
                $this->log('Deadletter encapsulator overridden - message was not added to the deadletter queue.');
            }
        }
        
        return true;
    }

    /* ------------------------------------ Interface methods END -------------------------------------- */
}