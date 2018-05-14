<?php
/**
 * This encapsulator will check the handler result and if it was marked as false will attempt to add the handler command to a deadletter queue.
 */
declare (strict_types=1);

namespace Maleficarum\Worker\Handler\Encapsulator;

class Deadletter extends \Maleficarum\Worker\Handler\Encapsulator\AbstractEncapsulator {
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
                    $this->getHandler()->addCommand(clone $this->getHandler()->getCommand(), 'deadletter');
                    $this->log('Deadletter encapsulator activated - message was added to the deadletter queue.');
                } catch (\InvalidArgumentException $e) {
                    $this->log('Deadletter encapsulator activated but the deadletter connection was not configured - message was NOT added to the deadletter queue.');
                } catch (\PhpAmqpLib\Exception\AMQPProtocolConnectionException $e) {
                    $this->log('Deadletter encapsulator activated but the deadletter connection was not properly configured - message was NOT added to the deadletter queue.');
                } catch (\RuntimeException $e) {
                    $this->log('Deadletter encapsulator activated but the command validation failed - message was NOT added to the deadletter queue.');
                }
            } else {
                $this->log('Deadletter encapsulator overridden - message was NOT added to the deadletter queue.');
            }
        }
        
        return true;
    }

    /* ------------------------------------ Interface methods END -------------------------------------- */
}