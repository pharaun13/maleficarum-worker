<?php
declare (strict_types=1);

namespace Maleficarum\Worker\Handler\Encapsulator\Debugger;

/**
 * Trait DebugTrait
 *
 * @package Maleficarum\Worker\Handler
 */
trait DebugTrait {
    /**
     * Add debug message
     *
     * @param string $message
     * @param array $options
     *
     * @return \Maleficarum\Worker\Handler\Encapsulator\Debugger\DebugTrait
     */
    public function debug(string $message, array $options = []): self {
        $registry = $this->getRegistry();
        $timeDifference = \round(\microtime(true) - $registry['debugInfo']['startTime'],6);
        $memoryDifference = \round(((\memory_get_usage() - $registry['debugInfo']['startMemory']) / 1024 / 1024),6);
        $registry['debugMessages'][] = \array_merge($options, [
            'message' => $message,
            'time' => $timeDifference . ' sec.',
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
     * @return \Maleficarum\Worker\Handler\Encapsulator\Debugger\DebugTrait
     */
    public function setTimeAfterPrintToLog(int $timeInSec): self {
        if ($timeInSec < 0) {
            throw \InvalidArgumentException('The time parameter must be greater or equal 0');
        }

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
     * @return \Maleficarum\Worker\Handler\Encapsulator\Debugger\DebugTrait
     */
    public function setMemoryUsageAfterPrintToLog(int $memoryUsageInMB): self {
        if ($memoryUsageInMB < 0) {
            throw \InvalidArgumentException('The memory parameter must be greater or equal 0');
        }

        $registry = $this->getRegistry();
        $registry['debugInfo']['memoryUsageAfterPrintToLog'] = $memoryUsageInMB;
        $this->setRegistry($registry);

        return $this;
    }
}