<?php
/**
 * This logger facility logs provided data to the STDOUT facility - proper for CLI context.
 *
 * @implements \Maleficarum\Worker\Logger\Facility\Facility
 */

namespace Maleficarum\Worker\Logger\Facility;

class Cli implements Facility
{
    /**
     * Internal constant for the "End-Of-Line" element.
     *
     * @var string
     */
    const FACILITY_EOL = \PHP_EOL;

    /**
     * @see \Maleficarum\Worker\Logger\Facility\Facility::write()
     */
    public function write($data, $level) {
        if (!is_string($level)) {
            throw new \InvalidArgumentException('Incorrect debug level provided - string expected. \Maleficarum\Worker\Logger\Facility\Cli');
        }

        if (is_object($data) || is_array($data)) {
            echo (strlen($level) ? '[' . $level . ']:' : '') . " [date: " . date('Y-m-d H-i-s') . '] ' . var_export($data, true) . self::FACILITY_EOL;
        } else {
            echo (strlen($level) ? '[' . $level . ']:' : '') . " [date: " . date('Y-m-d H-i-s') . '] ' . $data . self::FACILITY_EOL;
        }

        return $this;
    }
}
