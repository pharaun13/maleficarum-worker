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

    /**
     * Internal storage for config
     *
     * @var \Maleficarum\Config\AbstractConfig
     */
    private $config;

    /* ------------------------------------ Bootstrap methods START ------------------------------------ */
    /**
     * Bootstrap step method - set up error/exception handling.
     *
     * @return \Maleficarum\Worker\Bootstrap
     */
    final public function setUpErrorHandling() : \Maleficarum\Worker\Bootstrap {
        \set_exception_handler([\Maleficarum\Ioc\Container::get('Maleficarum\Handler\CommandLine\ExceptionHandler'), 'handle']);
        \set_error_handler([\Maleficarum\Ioc\Container::get('Maleficarum\Handler\ErrorHandler'), 'handle']);

        return $this;
    }

    /**
     * Bootstrap step method - set up profiler objects.
     *
     * @param float|null $start
     *
     * @return \Maleficarum\Worker\Bootstrap
     */
    final public function setUpProfilers(float $start = null) : \Maleficarum\Worker\Bootstrap {
        $this->timeProfiler = \Maleficarum\Ioc\Container::get('Maleficarum\Profiler\Time');
        $this->timeProfiler->begin($start);
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Profiler\Time', $this->timeProfiler);

        $this->timeProfiler->addMilestone('profiler_init', 'All profilers initialized.');

        return $this;
    }

    /**
     * Bootstrap step method - detect application environment.
     *
     * @return \Maleficarum\Worker\Bootstrap
     * @throws \RuntimeException
     */
    final public function setUpEnvironment() : \Maleficarum\Worker\Bootstrap {
        /* @var \Maleficarum\Environment\Server $environment */
        $environment = \Maleficarum\Ioc\Container::get('Maleficarum\Environment\Server');
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Environment', $environment);

        // fetch current env
        $environment = $environment->getCurrentEnvironment();

        // set handler debug level based on env
        if (in_array($environment, ['local', 'development', 'staging'])) {
            \Maleficarum\Handler\AbstractHandler::setDebugLevel(\Maleficarum\Handler\AbstractHandler::DEBUG_LEVEL_FULL);
        } elseif ($environment === 'uat') {
            \Maleficarum\Handler\AbstractHandler::setDebugLevel(\Maleficarum\Handler\AbstractHandler::DEBUG_LEVEL_LIMITED);
        } elseif ($environment === 'production') {
            \Maleficarum\Handler\AbstractHandler::setDebugLevel(\Maleficarum\Handler\AbstractHandler::DEBUG_LEVEL_CRUCIAL);
        } else {
            throw new \RuntimeException(sprintf('Unrecognised environment. \%s::setUpEnvironment()', static::class));
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
     * @return \Maleficarum\Worker\Bootstrap
     */
    final public function setUpConfig() : \Maleficarum\Worker\Bootstrap {
        /* @var \Maleficarum\Config\Ini\Config $config */
        $config = \Maleficarum\Ioc\Container::get('Maleficarum\Config\Ini\Config', ['id' => 'config.ini']);
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Config', $config);

        $this->config = $config;

        !is_null($this->timeProfiler) && $this->timeProfiler->addMilestone('conf_init', 'Config initialized.');

        return $this;
    }

    /**
     * Bootstrap step method - prepare and register the logger object.
     *
     * @return \Maleficarum\Worker\Bootstrap
     */
    final public function setUpLogger() : \Maleficarum\Worker\Bootstrap {
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
    final public function setUpQueue() : \Maleficarum\Worker\Bootstrap {
        $host = $this->config['queue']['broker']['host'];
        $port = $this->config['queue']['broker']['port'];
        $username = $this->config['queue']['broker']['username'];
        $password = $this->config['queue']['broker']['password'];
        $queueName = $this->config['queue']['commands']['queue-name'];

        $rabbitmq = \Maleficarum\Ioc\Container::get('Maleficarum\Rabbitmq\Connection', [$queueName, $host, $port, $username, $password]);
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\CommandQueue', $rabbitmq);

        !is_null($this->timeProfiler) && $this->timeProfiler->addMilestone('queue_init', 'RabbitMQ broker connection initialized.');

        return $this;
    }

    /**
     * Perform full worker init.
     *
     * @param float $start
     *
     * @return \Maleficarum\Worker\Bootstrap
     */
    final public function init(float $start = 0.0) : \Maleficarum\Worker\Bootstrap {
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
     * @param string $name
     *
     * @return \Maleficarum\Worker\Bootstrap
     */
    public function conclude(string $name = '[anonymous-worker]') : \Maleficarum\Worker\Bootstrap {
        echo $name . ' Worker operations concluded!!!' . PHP_EOL;

        return $this;
    }
    /* ------------------------------------ Bootstrap methods END -------------------------------------- */
}
