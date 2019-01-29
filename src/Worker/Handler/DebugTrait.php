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
        $timeDifference = \round(\microtime(true) - $registry['debugInfo']['startTime'],6);
        $memoryDifference = \round(((\memory_get_usage() - $registry['debugInfo']['startMemory']) / 1024 / 1024),6);
        $registry['debugMessages'][] = [
            'message' => $message,
            'time' => $timeDifference . ' sek',
            'memory' => $memoryDifference . ' MB'
        ];
        $this->setRegistry($registry);

        return $this;
    }
}