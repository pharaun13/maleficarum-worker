<?php
declare(strict_types = 1);

/**
 * Tests for the \Maleficarum\Worker\Process\Master class.
 */

namespace Maleficarum\Worker\Tests\Process;

class MasterTest extends \Maleficarum\Tests\TestCase
{
    /* ------------------------------------ Method: execute START -------------------------------------- */
    public function testMainLoop() {
        \Maleficarum\Ioc\Container::get('Maleficarum\Worker\Process\Master')->execute();
    }
    /* ------------------------------------ Method: execute END ---------------------------------------- */

    /* ------------------------------------ Method: handleCommand STAR---------------------------------- */
    public function testCommandHandlingWithIncorrectCommandStructure() {
        $master = \Maleficarum\Ioc\Container::get('Maleficarum\Worker\Process\Master');
        $message = \Maleficarum\Ioc\Container::get('PhpAmqpLib\Message\AMQPMessage');
        $result = $master->handleCommand($message);

        $this->assertFalse($result);
    }

    public function testCommandHandlingWithNonExistentCommandDefinition() {
        $master = \Maleficarum\Ioc\Container::get('Maleficarum\Worker\Process\Master');
        $message = \Maleficarum\Ioc\Container::get('PhpAmqpLib\Message\AMQPMessage');
        $result = $master->handleCommand($message);

        $this->assertFalse($result);
    }

    public function testCommandHandlingWithCorrectCommand() {
        $master = \Maleficarum\Ioc\Container::get('Maleficarum\Worker\Process\Master');
        $message = \Maleficarum\Ioc\Container::get('PhpAmqpLib\Message\AMQPMessage');
        $result = $master->handleCommand($message);

        $this->assertFalse($result);
    }
    /* ------------------------------------ Method: handleCommand END ---------------------------------- */
}
