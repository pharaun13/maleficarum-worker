<?php
declare(strict_types = 1);

/**
 * Tests for the \Maleficarum\Logger\Logger class.
 */

namespace Maleficarum\Worker\Tests\Logger;

class LoggerTest extends \Maleficarum\Tests\TestCase
{
    /* ------------------------------------ Method: log START ------------------------------------------ */
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testLogIncorrect() {
        $logger = new \Maleficarum\Worker\Logger\Logger();
        $logger->log('', null);
    }

    public function testLogCorrect() {
        $facility = $this->createMock('Maleficarum\Worker\Logger\Facility\Facility');
        $facility
            ->expects($this->once())
            ->method('write')
            ->with('test message', 'test level');

        $logger = new \Maleficarum\Worker\Logger\Logger();
        $logger
            ->attachFacility($facility)
            ->log('test message', 'test level')
            ->detachFacility($facility)
            ->log('test message', 'test level');
    }
    /* ------------------------------------ Method: log END -------------------------------------------- */
}
