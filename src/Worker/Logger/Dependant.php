<?php
/**
 * This trait provides functionality common to all classes dependant on the \Maleficarum\Worker\Logger namespace
 */

namespace Maleficarum\Worker\Logger;

trait Dependant {
    /**
     * Internal storage for the cache provider object.
     *
     * @var \Maleficarum\Worker\Logger\Logger
     */
    protected $loggerStorage = null;

    /**
     * Inject a new logger provider object into this object.
     *
     * @param \Maleficarum\Worker\Logger\Logger $logger
     *
     * @return \Maleficarum\Worker\Logger\Dependant
     */
    public function setLogger(\Maleficarum\Worker\Logger\Logger $logger) {
        $this->loggerStorage = $logger;

        return $this;
    }

    /**
     * Fetch the currently assigned logger provider object.
     *
     * @return \Maleficarum\Worker\Logger\Logger|null
     */
    public function getLogger(): ?\Maleficarum\Worker\Logger\Logger {
        return $this->loggerStorage;
    }

    /**
     * Detach the currently assigned logger provider object.
     *
     * @return \Maleficarum\Worker\Logger\Dependant
     */
    public function detachLogger() {
        $this->loggerStorage = null;

        return $this;
    }
}
