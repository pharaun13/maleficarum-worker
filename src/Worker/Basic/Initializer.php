<?php
/**
 * This class contains default initializers used as Maleficarum bootstrap methods.
 */
declare (strict_types=1);

namespace Maleficarum\Worker\Basic;

class Initializer {
    /* ------------------------------------ Class Methods START ---------------------------------------- */

    /**
     * Set up error/exception handling.
     *
     * @return string
     */
    static public function setUpErrorHandling(): string {
        \set_exception_handler([\Maleficarum\Ioc\Container::get('Maleficarum\Handler\CommandLine\ExceptionHandler'), 'handle']);
        \set_error_handler([\Maleficarum\Ioc\Container::get('Maleficarum\Handler\ErrorHandler'), 'handle']);

        // return initializer name
        return __METHOD__;
    }

    /**
     * Detect application environment.
     *
     * @param array $opts
     *
     * @throws \RuntimeException
     * @return string
     */
    static public function setUpDebugLevel(array $opts = []): string {
        try {
            $environment = \Maleficarum\Ioc\Container::retrieveShare('Maleficarum\Environment');
            $environment = $environment->getCurrentEnvironment();
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Environment object not initialized. \%s', __METHOD__));
        }

        \preg_match('/^production|development|sandbox|staging|uat|local/', $environment, $matches);

        // set handler debug level and error display value based on env
        switch (\reset($matches)) {
            case 'local':
            case 'development':
            case 'staging':
                \Maleficarum\Handler\AbstractHandler::setDebugLevel(\Maleficarum\Handler\AbstractHandler::DEBUG_LEVEL_FULL);
                break;
            case 'uat':
            case 'sandbox':
                \Maleficarum\Handler\AbstractHandler::setDebugLevel(\Maleficarum\Handler\AbstractHandler::DEBUG_LEVEL_LIMITED);
                break;
            case 'production':
                \Maleficarum\Handler\AbstractHandler::setDebugLevel(\Maleficarum\Handler\AbstractHandler::DEBUG_LEVEL_CRUCIAL);
                break;
            default:
                throw new \RuntimeException(sprintf('Unrecognised environment. \%s', __METHOD__));
        }

        // since this is a worker app we can turn on all error reporting regardless of environment
        ini_set('display_errors', '1');
        error_reporting(-1);

        // return initializer name
        return __METHOD__;
    }

    /**
     * Prepare and register worker handlers.
     *
     * @param array $opts
     *
     * @return string
     */
    static public function setUpHandler(array $opts = []): string {
        // load default builder if skip not requested
        $builders = $opts['builders'] ?? [];
        is_array($builders) or $builders = [];
        isset($builders['handler']['skip']) or \Maleficarum\Ioc\Container::get('Maleficarum\Worker\Basic\Builder')->register('handler');

        // return initializer name
        return __METHOD__;
    }

    /**
     * Prepare and register worker process.
     *
     * @param array $opts
     *
     * @return string
     */
    static public function setUpProcess(array $opts = []): string {
        // load default builder if skip not requested
        $builders = $opts['builders'] ?? [];
        is_array($builders) or $builders = [];
        isset($builders['process']['skip']) or \Maleficarum\Ioc\Container::get('Maleficarum\Worker\Basic\Builder')->register('process');

        // return initializer name
        return __METHOD__;
    }

    /**
     * Prepare and register logger.
     *
     * @param array $opts
     *
     * @return string
     */
    static public function setUpLogger(array $opts = []): string {
        // load default builder if skip not requested
        $builders = $opts['builders'] ?? [];
        is_array($builders) or $builders = [];
        isset($builders['logger']['skip']) or \Maleficarum\Ioc\Container::get('Maleficarum\Worker\Basic\Builder')->register('logger');

        // return initializer name
        return __METHOD__;
    }

    /* ------------------------------------ Class Methods END ------------------------------------------ */
}
