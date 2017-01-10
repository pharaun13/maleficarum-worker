<?php
/**
 * This class manages all bootstrap operations for the application.
 */

namespace Maleficarum\Worker;

class Bootstrap
{
    /**
     * Internal storage for the time profiler
     *
     * @var \Maleficarum\Profiler\Time|null
     */
    public $timeProfiler = null;

    /* ------------------------------------ Bootstrap methods START ------------------------------------ */
    /**
     * Bootstrap step method - set up error/exception handling.
     *
     * @return \Maleficarum\Worker\Bootstrap
     */
    final public function setUpErrorHandling() {
        \set_exception_handler([\Maleficarum\Ioc\Container::get('Maleficarum\Handler\ExceptionHandler'), 'handle']);
        \set_error_handler([\Maleficarum\Ioc\Container::get('Maleficarum\Handler\ErrorHandler'), 'handle']);

        return $this;
    }

    /**
     * Bootstrap step method - set up profiler objects.
     *
     * @param int|null $start
     *
     * @return \Maleficarum\Worker\Bootstrap
     */
    final public function setUpProfilers($start = null) {
        $this->timeProfiler = \Maleficarum\Ioc\Container::get('Maleficarum\Profiler\Time');
        $this->timeProfiler->begin($start);
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Profiler\Time', $this->timeProfiler);

        $this->timeProfiler->addMilestone('profiler_init', 'All profilers initialized.');

        return $this;
    }

    /**
     * Bootstrap step method - detect application environment.
     *
     * @throws \RuntimeException
     * @return \Maleficarum\Worker\Bootstrap
     */
    final public function setUpEnvironment() {
        /* @var \Maleficarum\Environment\Server $env */
        $env = \Maleficarum\Ioc\Container::get('Maleficarum\Environment\Server');
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Environment', $env);

        // fetch current env
        $env = $env->getCurrentEnvironment();

        // set handler debug level based on env
        if (in_array($env, ['local', 'development', 'staging'])) {
            \Maleficarum\Handler\AbstractHandler::setDebugLevel(\Maleficarum\Handler\AbstractHandler::DEBUG_LEVEL_FULL);
        } elseif ($env === 'uat') {
            \Maleficarum\Handler\AbstractHandler::setDebugLevel(\Maleficarum\Handler\AbstractHandler::DEBUG_LEVEL_LIMITED);
        } elseif ($env === 'production') {
            \Maleficarum\Handler\AbstractHandler::setDebugLevel(\Maleficarum\Handler\AbstractHandler::DEBUG_LEVEL_CRUCIAL);
        } else {
            throw new \RuntimeException('Unrecognised environment. \Maleficarum\Worker\Bootstrap::setUpEnvironment()');
        }

        // since this is a worker app we can turn on all error reporting regardless of environment
        ini_set('display_errors', '1');
        error_reporting(-1);

        !is_null($this->timeProfiler) && $this->timeProfiler->addMilestone('env_init', 'Environment initialized.');

        return $this;
    }

    /**
     * Bootstrap step method - prepare, load and register the config object.
     *
     * @throws \RuntimeException
     * @return \Maleficarum\Worker\Bootstrap
     */
    final public function setUpConfig() {
        /* @var \Maleficarum\Config\Ini\Config $config */
        $config = \Maleficarum\Ioc\Container::get('Maleficarum\Config\Ini\Config', ['id' => 'config.ini']);
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Config', $config);

        !is_null($this->timeProfiler) && $this->timeProfiler->addMilestone('conf_init', 'Config initialized.');

        return $this;
    }

    /**
     * Bootstrap step method - prepare and register the logger object.
     *
     * @return \Maleficarum\Worker\Bootstrap
     */
    final public function setUpLogger() {
        $logger = \Maleficarum\Ioc\Container::get('Maleficarum\Worker\Logger\Logger');
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Logger', $logger);

        !is_null($this->timeProfiler) && $this->timeProfiler->addMilestone('logger_init', 'Logger initialized.');

        return $this;
    }

    /**
     * Bootstrap step method - prepare and register the command queue connection object.
     *
     * @return \Maleficarum\Worker\Bootstrap
     */
    final public function setUpQueue() {
        $rab = \Maleficarum\Ioc\Container::get('Maleficarum\Rabbitmq\Connection');
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\CommandQueue', $rab);

        !is_null($this->timeProfiler) && $this->timeProfiler->addMilestone('queue_init', 'RabbitMQ broker connection initialized.');

        return $this;
    }

    /**
     * Perform full worker init.
     *
     * @param int $start
     *
     * @return \Maleficarum\Worker\Bootstrap
     */
    final public function init($start = 0) {
        return $this
            ->setUpErrorHandling()
            ->setUpProfilers($start)
            ->setUpEnvironment()
            ->setUpConfig()
            ->setUpLogger()
            ->setUpQueue();
    }

    /**
     * Perform any final maintenance actions. This will be called at the end of the worker run.
     *
     * @return \Maleficarum\Worker\Bootstrap
     */
    public function conclude($name = '[anonymous-worker]') {
        echo $name . ' Worker operations concluded!!!' . PHP_EOL;

        return $this;
    }
    /* ------------------------------------ Bootstrap methods END -------------------------------------- */
}
