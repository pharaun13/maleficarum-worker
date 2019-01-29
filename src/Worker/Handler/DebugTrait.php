<?php
declare (strict_types=1);

namespace Maleficarum\Worker\Handler;

/**
 * Class DebugTrait
 * @package Maleficarum\Worker\Handler
 */
trait DebugTrait {
    /**
     * @param string $message
     */
    public function debug(string $message): self {
        $registry = $this->getRegistry();
        $timeDifference = \microtime(true) - $registry['debugInfo']['startTime'];
        $memoryDifference = ((\memory_get_usage() - $registry['debugInfo']['startMemory']) / 1024 / 1024);
        $registry['debugMessages'][] = [
            'message' => $message,
            'time' => $timeDifference . 'sek|'. \round($timeDifference / $registry['debugInfo']['startTime'], 2) . '%',
            'memory' => $memoryDifference . ' MB|' . \round($memoryDifference/ $registry['debugInfo']['startMemory'],2) . '%'
        ];
        $this->setRegistry($registry);

        return $this;
    }
}