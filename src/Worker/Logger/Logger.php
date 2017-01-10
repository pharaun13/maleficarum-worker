<?php
/**
 * This class provides logging capacity with any number of specific logging facilities.
 */

namespace Maleficarum\Worker\Logger;

class Logger
{
    /**
     * Internal storage for assigned facilities.
     *
     * @var array
     */
    private $facilities = null;

    /**
     * Initialize a new logger without any facilities.
     */
    public function __construct() {
        $this->facilities = [];
    }

    /**
     * Log provided data with all the assigned facilities.
     *
     * @param mixed $data
     * @param string $level
     *
     * @throws \InvalidArgumentException
     * @return \Maleficarum\Worker\Logger\Logger
     */
    public function log($data, $level = '') {
        if (!is_string($level)) {
            throw new \InvalidArgumentException('Incorrect debug level provided - string expected. \Maleficarum\Worker\Logger\Logger');
        }

        foreach ($this->facilities as $facility) {
            $facility->write($data, $level);
        }

        return $this;
    }

    /**
     * Attach a new facility to this logger service.
     *
     * @param \Maleficarum\Worker\Logger\Facility\Facility $facility
     *
     * @return \Maleficarum\Worker\Logger\Logger
     */
    public function attachFacility(Facility\Facility $facility) {
        $this->facilities[\get_class($facility)] = $facility;

        return $this;
    }

    /**
     * Detach a facility from this logger service.
     *
     * @param \Maleficarum\Worker\Logger\Facility\Facility $facility
     *
     * @return \Maleficarum\Worker\Logger\Logger
     */
    public function detachFacility(Facility\Facility $facility) {
        unset($this->facilities[\get_class($facility)]);

        return $this;
    }
}
