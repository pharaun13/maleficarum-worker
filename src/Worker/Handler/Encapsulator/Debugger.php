<?php
/**
 * This encapsulator will check the handler result and if it was marked as false will attempt to add the handler command to a deadletter queue.
 */
declare (strict_types=1);

namespace Maleficarum\Worker\Handler\Encapsulator;

class Debugger extends \Maleficarum\Worker\Handler\Encapsulator\AbstractEncapsulator {
    /**
     * @see \Maleficarum\Worker\Handler\Encapsulator\Encapsulator::beforeHandle()
     */
    public function beforeHandle() : bool {
        $registry = $this->getHandler()->getRegistry();
        $registry['debugMessages'] = [];
        $registry['debugInfo'] = [
            'printToLog' => true,
            'startTime' => \microtime(true),
            'startMemory' => \memory_get_usage()
        ];
        $this->getHandler()->setRegistry($registry);

        return true;
    }

    /**
     * @see \Maleficarum\Worker\Handler\Encapsulator\Encapsulator::afterHandle()
     */
    public function afterHandle(bool $result): bool {
        $registry = $this->getHandler()->getRegistry();
        if ($registry['debugInfo']['printToLog'] === true && \count($registry['debugMessages']) > 0) {
            $i=1;
            foreach($registry['debugMessages'] as $messageData) {
                $this->log('[DEBUG] ' . $i . '. ' . \json_encode($messageData));
                $i++;
            }
        }

        return true;
    }

    /* ------------------------------------ Interface methods END -------------------------------------- */
}