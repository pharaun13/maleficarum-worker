<?php
/**
 * This interface is to be implemented by each and every class that is supposed to act as as logger facility.
 */

namespace Maleficarum\Worker\Logger\Facility;

interface Facility
{
    /**
     * Write the specified data to the logging facility.
     *
     * @param Mixed $data
     * @param Integer $level
     *
     * @throws \InvalidArgumentException
     * @return \Maleficarum\Worker\Logger\Facility\Facility
     */
    public function write($data, $level);
}
