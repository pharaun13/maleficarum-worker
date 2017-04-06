<?php
/**
 * PHP 7.0 compatible
 */
declare (strict_types = 1);

/**
 * This class contains default builders for most Maleficarum internal components.
 */

namespace Maleficarum\Worker\Basic;

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
    public function register(string $type, $opts = []) : \Maleficarum\Worker\Basic\Builder {
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
    private function registerProcess(array $opts = []) : \Maleficarum\Worker\Basic\Builder {
        \Maleficarum\Ioc\Container::register('Maleficarum\Worker\Process\Master', function ($dep) {
            return (new \Maleficarum\Worker\Process\Master())
                ->setQueue($dep['Maleficarum\CommandQueue'])
                ->setLogger($dep['Maleficarum\Logger'])
                ->setConfig($dep['Maleficarum\Config'])
                ->addProfiler($dep['Maleficarum\Profiler\Time'], 'time');
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
    private function registerHandler(array $opts = []) : \Maleficarum\Worker\Basic\Builder {
        \Maleficarum\Ioc\Container::register('Handler', function ($dep, $opts) {
            /** @var \Maleficarum\Worker\Handler\AbstractHandler $handler */
            $handler = new $opts['__class']();
            if (!$handler instanceof \Maleficarum\Worker\Handler\AbstractHandler) throw new \RuntimeException('Handler builder function used to create a non handler class. \Maleficarum\Ioc\Container::get()');
            $handler
                ->setQueue($dep['Maleficarum\CommandQueue'])
                ->setLogger($dep['Maleficarum\Logger'])
                ->addProfiler($dep['Maleficarum\Profiler\Time'], 'time');

            (method_exists($handler, 'setRedis') && isset($dep['Maleficarum\Redis'])) and $handler->setRedis($dep['Maleficarum\Redis']);

            return $handler;
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
    private function registerLogger(array $opts = []) : \Maleficarum\Worker\Basic\Builder {
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
