<?php
/**
 * This encapsulator will attempt to requeue the message if the handler has returned false.
 */
declare (strict_types=1);

namespace Maleficarum\Worker\Handler\Encapsulator;

class Retry extends \Maleficarum\Worker\Handler\Encapsulator\AbstractEncapsulator {
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
        // only try to requeue the command if the main handler failed
        if (false === $result) {
            $registry = $this->getHandler()->getRegistry();
            
            // skip the retry attempt if the attempt limit was not specified in handler registry
            if (!isset($registry['retry']['limit'])) {
                $this->log('Retry encapsulator activated but handler registry did not specify the attempt limit - message was NOT requeued.');
                return true;
            }

            // skip the retry attempt if the attempt limit was incorrectly specified in handler registry
            if (!is_int($registry['retry']['limit']) || $registry['retry']['limit'] <= 0) {
                $this->log('Retry encapsulator activated but handler registry specified an incorrect attempt limit ('.$registry['retry']['limit'].') - message was NOT requeued.');
                return true;
            }
            
            // skip the retry attempt if the retry connection name was not specified
            if (!isset($registry['retry']['connection'])) {
                $this->log('Retry encapsulator activated but handler registry did not specify the retry connection - message was NOT requeued.');
                return true;
            }

            // skip the retry attempt if explicitly requested
            if (isset($registry['retry']['skip'])) {
                $this->log('Retry encapsulator overridden - message was NOT requeued.');
                return true;
            }
            
            // skip the retry attempt if it would exceed the attempt limit
            $command = clone $this->getHandler()->getCommand();
            $meta = $command->getCommandMetaData();
            $attempCount = isset($meta['retry']['attempts']) ? (int)$meta['retry']['attempts'] : 0;
            
            if ($attempCount >= $registry['retry']['limit']) {
                $this->log('Retry encapsulator activated but attemp count exceeds the registry attemp limit - message was NOT requeued.');
                return true;
            }
            
            // update the command meta data            
            $meta['retry']['attempts'] = $attempCount + 1;
            $command->setCommandMetaData($meta);
            
            // requeue the command
            try {
                $this->getHandler()->addCommand($command, $registry['retry']['connection']);
                $this->log('Retry encapsulator activated - message requeued. Retry: '.($attempCount+1).' of '.$registry['retry']['limit']);
            } catch (\InvalidArgumentException $e) {
                $this->log('Retry encapsulator activated but the retry connection was not configured - message was NOT requeued.');
            } catch (\PhpAmqpLib\Exception\AMQPProtocolConnectionException $e) {
                $this->log('Retry encapsulator activated but the retry connection was not properly configured - message was NOT requeued.');
            } catch (\RuntimeException $e) {
                $this->log('Retry encapsulator activated but the command validation failed - message was NOT requeued.');
            }
            
            // a retry will explicitly override the deadletter encapsulator
            $registry['deadletter']['skip'] = true;
            $this->getHandler()->setRegistry($registry);
        }
        
        return true;
    }

    /* ------------------------------------ Interface methods END -------------------------------------- */
}