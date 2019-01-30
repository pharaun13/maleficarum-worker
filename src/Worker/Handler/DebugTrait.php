<?php
declare (strict_types=1);

namespace Maleficarum\Worker\Handler;

/**
 * Class DebugTrait
 * @package Maleficarum\Worker\Handler
 */
trait DebugTrait {
    /**
     * Add debug message
     *
     * @param string $message
     * @param array $options
     *
     * @return $this
     */
    public function debug(string $message, array $options = []): self {
        $registry = $this->getRegistry();
        $timeDifference = \round(\microtime(true) - $registry['debugInfo']['startTime'],6);
        $memoryDifference = \round(((\memory_get_usage() - $registry['debugInfo']['startMemory']) / 1024 / 1024),6);
        $registry['debugMessages'][] = \array_merge($options, [
            'message' => $message,
            'time' => $timeDifference . ' sek',
            'memory' => $memoryDifference . ' MB'
        ]);
        $this->setRegistry($registry);

        return $this;
    }

    /**
     * Set time in seconds when debug information will be printed to log
     *
     * @param int $timeInSec
     *
     * @return $this
     */
    public function setTimeAfterPrintToLog(int $timeInSec): self {
        $registry = $this->getRegistry();
        $registry['debugInfo']['timeAfterPrintToLog'] = $timeInSec;
        $this->setRegistry($registry);

        return $this;
    }

    /**
     * Set memory usage in MB when debug information will be printed to log
     *
     * @param int $memoryUsageInMB
     *
     * @return $this
     */
    public function setMemoryUsageAfterPrintToLog(int $memoryUsageInMB): self {
        $registry = $this->getRegistry();
        $registry['debugInfo']['memoryUsageAfterPrintToLog'] = $memoryUsageInMB;
        $this->setRegistry($registry);

        return $this;
    }
}