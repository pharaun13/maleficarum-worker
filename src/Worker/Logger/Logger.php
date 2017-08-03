<?php
/**
 * This class provides logging capacity with any number of specific logging facilities.
 */

namespace Maleficarum\Worker\Logger;

class Logger {
    /**
     * Internal storage for assigned facilities.
     *
     * @var array|\Maleficarum\Worker\Logger\Facility\Facility[]
     */
    private $facilities = null;

    /* ------------------------------------ Magic methods START ---------------------------------------- */
    /**
     * Initialize a new logger without any facilities.
     */
    public function __construct() {
        $this->facilities = [];
    }
    /* ------------------------------------ Magic methods END ------------------------------------------ */

    /* ------------------------------------ Logger methods START --------------------------------------- */
    /**
     * Log provided data with all the assigned facilities.
     *
     * @param mixed $data
     * @param string $level
     *
     * @throws \InvalidArgumentException
     * @return \Maleficarum\Worker\Logger\Logger
     */
    public function log($data, string $level = null): \Maleficarum\Worker\Logger\Logger {
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
    public function attachFacility(\Maleficarum\Worker\Logger\Facility\Facility $facility): \Maleficarum\Worker\Logger\Logger {
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
    public function detachFacility(\Maleficarum\Worker\Logger\Facility\Facility $facility): \Maleficarum\Worker\Logger\Logger {
        unset($this->facilities[\get_class($facility)]);

        return $this;
    }
    /* ------------------------------------ Logger methods END ----------------------------------------- */
}
