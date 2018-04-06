<?php
/**
 * PHP 7.0 compatible
 */
declare (strict_types=1);

/**
 * This class contains default builders for most Maleficarum internal components.
 */

namespace Maleficarum\Worker\Basic;

use function foo\func;

class Builder {
    /* ------------------------------------ Class Constant START --------------------------------------- */

    const builderList = [
        'process',
        'handler',
        'logger'
    ];

    /* ------------------------------------ Class Constant END ----------------------------------------- */

    /* ------------------------------------ Class Methods START ---------------------------------------- */

    /**
     * Attach default implementation of Maleficarum builder functions to the IOC container.
     *
     * @param string $type
     * @param array $opts
     *
     * @return \Maleficarum\Worker\Basic\Builder
     */
    public function register(string $type, array $opts = []): \Maleficarum\Worker\Basic\Builder {
        if (in_array($type, self::builderList)) {
            $type = 'register' . ucfirst($type);
            $this->$type($opts);
        }

        return $this;
    }

    /**
     * Register process builders.
     *
     * @param array $opts
     *
     * @return \Maleficarum\Worker\Basic\Builder
     */
    private function registerProcess(array $opts = []): \Maleficarum\Worker\Basic\Builder {
        \Maleficarum\Ioc\Container::register('Maleficarum\Worker\Process\Master', function ($dep) {
            return (new \Maleficarum\Worker\Process\Master())
                ->setQueue($dep['Maleficarum\CommandRouter'])
                ->setLogger($dep['Maleficarum\Logger']);
        });

        return $this;
    }

    /**
     * Register handler builders.
     *
     * @param array $opts
     *
     * @return \Maleficarum\Worker\Basic\Builder
     */
    private function registerHandler(array $opts = []): \Maleficarum\Worker\Basic\Builder {
        \Maleficarum\Ioc\Container::register('Handler', function ($dep, $opts) {
            /** @var \Maleficarum\Worker\Handler\AbstractHandler $handler */
            $handler = new $opts['__class']();
            if (!$handler instanceof \Maleficarum\Worker\Handler\AbstractHandler) throw new \RuntimeException('Handler builder function used to create a non handler class. \Maleficarum\Ioc\Container::get()');
            
            (method_exists($handler, 'setQueue') && isset($dep['Maleficarum\CommandRouter'])) and $handler->setQueue($dep['Maleficarum\CommandRouter']);
            (method_exists($handler, 'setLogger') && isset($dep['Maleficarum\Logger'])) and $handler->setLogger($dep['Maleficarum\Logger']);
            (method_exists($handler, 'setRedis') && isset($dep['Maleficarum\Redis'])) and $handler->setRedis($dep['Maleficarum\Redis']);
            (method_exists($handler, 'setConfig') && isset($dep['Maleficarum\Config'])) and $handler->setConfig($dep['Maleficarum\Config']);

            return $handler;
        });

        \Maleficarum\Ioc\Container::register('Maleficarum\Worker\Handler\Encapsulator', function ($dep, $opts) {
            $enc = new $opts['__class']($opts[0]);
            if (!$enc instanceof \Maleficarum\Worker\Handler\Encapsulator\AbstractEncapsulator) throw new \RuntimeException('Encapsulator builder function used to create a non encapsulator class. \Maleficarum\Ioc\Container::get()');

            method_exists($enc, 'addProfiler') and $enc->addProfiler(\Maleficarum\Ioc\Container::get('Maleficarum\Profiler\Time\Generic'), 'time');
            (method_exists($enc, 'setQueue') && isset($dep['Maleficarum\CommandRouter'])) and $enc->setQueue($dep['Maleficarum\CommandRouter']);
            
            return $enc;
        });
        
        return $this;
    }

    /**
     * Register logger builders.
     *
     * @param array $opts
     *
     * @return \Maleficarum\Worker\Basic\Builder
     */
    private function registerLogger(array $opts = []): \Maleficarum\Worker\Basic\Builder {
        \Maleficarum\Ioc\Container::register('Maleficarum\Worker\Logger\Logger', function ($dep) {
            if (!isset($dep['Maleficarum\Config']['logger']['facilities'])) {
                throw new \RuntimeException('Cannot access the logger object - missing logger configuration. \Maleficarum\Ioc\Container::get()');
            }

            $logger = (new \Maleficarum\Worker\Logger\Logger());
            foreach ($dep['Maleficarum\Config']['logger']['facilities'] as $class) {
                $class = 'Maleficarum\Worker\Logger\Facility\\' . ucfirst($class);
                $facility = \Maleficarum\Ioc\Container::get($class);

                $logger->attachFacility($facility);
            }

            return $logger;
        });

        $logger = \Maleficarum\Ioc\Container::get('Maleficarum\Worker\Logger\Logger');
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Logger', $logger);

        return $this;
    }

    /* ------------------------------------ Class Methods END ------------------------------------------ */
}
