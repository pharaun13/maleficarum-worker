<?php
/**
 * This encapsulator will print to log debug information stored in registry
 */
declare (strict_types=1);

namespace Maleficarum\Worker\Handler\Encapsulator;

/**
 * Class Debugger
 *
 * @package Maleficarum\Worker\Handler\Encapsulator
 */
class Debugger extends \Maleficarum\Worker\Handler\Encapsulator\AbstractEncapsulator {
    /**
     * Default time in seconds when debug information will be printed to log
     */
    CONST DEFAULT_TIME_AFTER_PRINT_TO_LOG = 600; //10 min

    /**
     * Default memory usage in MB when debug information will be printed to log
     */
    CONST DEFAULT_MEMORY_USAGE_AFTER_PRINT_TO_LOG = 80; //80MB

    /**
     * @see \Maleficarum\Worker\Handler\Encapsulator\Encapsulator::beforeHandle()
     */
    public function beforeHandle() : bool {
        $registry = $this->getHandler()->getRegistry();
        $registry['debugMessages'] = [];
        $registry['debugInfo'] = [
            'timeAfterPrintToLog'=> self::DEFAULT_TIME_AFTER_PRINT_TO_LOG,
            'memoryUsageAfterPrintToLog'=> self::DEFAULT_MEMORY_USAGE_AFTER_PRINT_TO_LOG,
            'startTime' => \microtime(true),
            'startMemory' => \memory_get_usage() / 1024 /1024
        ];
        $this->getHandler()->setRegistry($registry);

        return true;
    }

    /**
     * @see \Maleficarum\Worker\Handler\Encapsulator\Encapsulator::afterHandle()
     */
    public function afterHandle(bool $result): bool {
        $registry = $this->getHandler()->getRegistry();
        isset($registry['debugMessages']) or $registry['debugMessages'] = [];
        if(\count($registry['debugMessages']) === 0){
            return true;
        }

        $endTime = \microtime(true);
        $endMemory = \memory_get_usage() / 1024 / 1024;

        if (
            $endTime - $registry['debugInfo']['startTime'] >= $registry['debugInfo']['timeAfterPrintToLog']  ||
            $endMemory - $registry['debugInfo']['startMemory'] >= $registry['debugInfo']['memoryUsageAfterPrintToLog']
        ) {
            $this->printToLog($registry['debugMessages']);
        }

        return true;
    }

    /**
     * Print all messages to log
     *
     * @param array $messages
     */
    private function printToLog(array $messages): void {
        $i=1;
        foreach($messages as $messageData) {
            $this->log('[DEBUG] ' . $i . '. ' . \json_encode($messageData));
            $i++;
        }
    }

    /* ------------------------------------ Interface methods END -------------------------------------- */
}