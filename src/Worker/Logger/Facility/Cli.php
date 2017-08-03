<?php
/**
 * This logger facility logs provided data to the STDOUT facility - proper for CLI context.
 *
 * @implements \Maleficarum\Worker\Logger\Facility\Facility
 */

namespace Maleficarum\Worker\Logger\Facility;

class Cli implements Facility {
    /**
     * Internal constant for the "End-Of-Line" element.
     *
     * @var string
     */
    const FACILITY_EOL = \PHP_EOL;

    /**
     * Write the specified data to the logging facility.
     *
     * @see \Maleficarum\Worker\Logger\Facility\Facility::write()
     *
     * @param string $data
     * @param string $level
     *
     * @return \Maleficarum\Worker\Logger\Facility\Facility
     */
    public function write($data, string $level): \Maleficarum\Worker\Logger\Facility\Facility {
        if (!is_string($level)) {
            throw new \InvalidArgumentException(sprintf('Incorrect debug level provided - string expected. \%s::write()'));
        }

        if (is_object($data) || is_array($data)) {
            echo (strlen($level) ? '[' . $level . ']:' : '') . ' [date: ' . date('Y-m-d H-i-s') . '] ' . var_export($data, true) . self::FACILITY_EOL;
        } else {
            echo (strlen($level) ? '[' . $level . ']:' : '') . ' [date: ' . date('Y-m-d H-i-s') . '] ' . $data . self::FACILITY_EOL;
        }

        return $this;
    }
}
