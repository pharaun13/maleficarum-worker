<?php
/**
 * PHP 7.0 compatible
 */
declare (strict_types = 1);

/**
 * This class manages all bootstrap operations for the application.
 */

namespace Maleficarum\Worker;

class Bootstrap {

    /* ------------------------------------ Class Constant START --------------------------------------- */

    const INITIALIZER_ERRORS = ['Maleficarum\Worker\Basic\Initializer', 'setUpErrorHandling'];
    const INITIALIZER_DEBUG_LEVEL = ['Maleficarum\Worker\Basic\Initializer', 'setUpDebugLevel'];
    const INITIALIZER_HANDLER = ['Maleficarum\Worker\Basic\Initializer', 'setUpHandler'];
    const INITIALIZER_PROCESS = ['Maleficarum\Worker\Basic\Initializer', 'setUpProcess'];
    const INITIALIZER_LOGGER = ['Maleficarum\Worker\Basic\Initializer', 'setUpLogger'];

    /* ------------------------------------ Class Constant END ----------------------------------------- */

    /* ------------------------------------ Class Property START --------------------------------------- */

    /**
     * Internal storage for worker component initializers to run during bootstrap execution.
     *
     * @var array
     */
    private $initializers = [];

    /**
     * Internal storage for bootstrap initializer param container.
     *
     * @var array
     */
    private $paramContainer = [];

    /* ------------------------------------ Class Property END ----------------------------------------- */

    /**
     * Run all defined bootstrap initializers.
     *
     * @return \Maleficarum\Worker\Bootstrap
     */
    public function initialize() : \Maleficarum\Worker\Bootstrap {
        // register bootstrap as dependency for use in initializer steps
        \Maleficarum\Ioc\Container::registerDependency('Maleficarum\Bootstrap', $this);

        // validate and execute initializers
        foreach ($this->getInitializers() as $key => $initializer) {
            if (!is_callable($initializer)) throw new \LogicException(sprintf('Invalid initializer passed to the bootstrap initialization process. \%s()', __METHOD__));
            $init_name = $initializer($this->getParamContainer());

            try {
                \Maleficarum\Ioc\Container::getDependency('Maleficarum\Profiler\Time')->addMilestone('initializer_' . $key, 'Initializer executed (' . $init_name . ').');
            } catch (\RuntimeException $e) {
            }
        }

        return $this;
    }

    /**
     * Perform any final maintenance actions. This will be called at the end of a request.
     *
     * @param string $name
     *
     * @return \Maleficarum\Worker\Bootstrap
     */
    public function conclude($name = '[anonymous-worker]') : \Maleficarum\Worker\Bootstrap {
        echo $name . ' Worker operations concluded!!!' . PHP_EOL;

        return $this;
    }

    /* ------------------------------------ Setters & Getters START ------------------------------------ */

    /**
     * Get container parameters
     *
     * @return array
     */
    public function getParamContainer() {
        return $this->paramContainer;
    }

    /**
     * Set container parameters
     *
     * @param array $paramContainer
     *
     * @return \Maleficarum\Worker\Bootstrap
     */
    public function setParamContainer(array $paramContainer = []) : \Maleficarum\Worker\Bootstrap {
        $this->paramContainer = $paramContainer;

        return $this;
    }

    /**
     * Get initializers
     *
     * @return array
     */
    protected function getInitializers() : array {
        return $this->initializers;
    }

    /**
     * Set initializers
     *
     * @param array $initializers
     *
     * @return \Maleficarum\Worker\Bootstrap
     */
    public function setInitializers(array $initializers) : \Maleficarum\Worker\Bootstrap {
        $this->initializers = $initializers;

        return $this;
    }

    /* ------------------------------------ Setters & Getters END -------------------------------------- */
}
