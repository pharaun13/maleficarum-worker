# Change Log
This is the Maleficarum Worker component implementation. 


## [10.0.2] - 2020-06-15
### Fix
- Use maleficarum-command 3.2.0

## [10.0.1] - 2020-02-18
### Added
- added information about memory usage

## [10.0.0] - 2020-02-18
### Fixed
- bump maleficarum/rabbitmq version to 10.0.0

## [9.3.1] - 2020-02-06
### Fixed
- return empty array when `application_headers` key doesn't exist

## [9.3.0] - 2020-02-04
### Added
- added amqp headers to command

## [9.2.0] - 2019-09-02
### Added
- added support for testMode command parameter

## [9.1.2] - 2019-04-25
### Fixed
- Add sandbox environment

## [9.1.1] - 2019-02-04
### Fixed
- Incorrect memory format in debugger encapsulator

## [9.1.0] - 2019-01-29
### Added
- Add Debugger encapsulator 
- Add debug trait for Worker Handler. 

When Handler has `\Maleficarum\Worker\Handler\Encapsulator\Debugger` and use `\Maleficarum\Worker\Handler\Encapsulator\Debugger\DebugTrait` 
you can use method `$this->debug(string $message, array $options)` inside the Handler code. In outcome there is an additional debug entry in logs like `[DEBUG] 1. {"message":"This is a test message","time":"2,408634 sec.","memory":"0,000584 MB"}`. 
Each of the message has automatically added an information about the execution time and the memory usage.

[EXAMPLE](docs/encapsulators/debbuger.md)


## [9.0.0] - 2018-10-05    
### Changed    
- Upgraded IoC component to version 3.x   
- Upgraded phpunit version   
- Removed repositories section from composer file
- Fixed unit tests   

## [8.0.0] - 2018-06-18
### Changed
- Introduced the adaptive multi source consumer mode:
    - This mode replaces the previous multi source consumer mode.
    - This greatly improves the reliability of the priority system - queues in higher priority will now have a fixed amount of time to provide the next command message before control is passed to lower priority queues
      but unlike the previous implementation the new command will be handled as soon as it is available instead of having a fixed wait time between handlers. This way there will be no performance impact on queues
      that carry multiple commands before a priority switch is necessary.
    - The grace period between priority switches can be defined via a parameter and carries the default value of 0.1 seconds. This parameter is ignored in single source mode.

## [7.2.0] - 2018-06-14
### Changed
- The wait period added in 7.1.2 is now no longer mandatory and can be parametrized via the process init method.

## [7.1.4] - 2018-05-17
### Changed
- Increased the hardcoded wait value for the multi source consumer mode to 10000 micro seconds.

## [7.1.3] - 2018-05-14
### Fixed
- Commands that do not pass internal validation will no longer cause an uncaught exception in the Deadletter and Retry encapsulators.

## [7.1.2] - 2018-05-14
### Added
- Added a mandatory wait period between attempting to execute the master process loop from scratch when in multi source consumer mode. This will be parameterized in up coming releases - for now it's a hardcoded wait value.

## [7.1.1] - 2018-04-10
### Fixed
- Incorrect date format in README entries.

## [7.1.0] - 2018-04-10
### Added
- Added a new encapsulator: "Retry" that will attempt to requeue a failed command a specific number of times.
- Reintroduced addCommand and addCommands helper methods to the abstract handler. [CAUTION: they have a new interface]
### Changed
- The Information encapsulator will now include message meta data information in the initial log entry.

## [7.0.1] - 2018-04-09
### Changed
- Added additional protection in the Deadletter encapsulation that will keep the worker process from exiting if the deadletter connection was misconfigured. 

## [7.0.0] - 2018-04-06
### Changed   
- Added the multi source consumer mode: 
    - It is now possible to consume commands from multiple source queues over multiple independent connections without blocking other connections.
    - Each connection/queue has it's own priority that can be set via the config file. 
    - Queues with matching priority setting will be served in the round robin fashion.
    - Queues with lower priority will be used only when all higher priority queues have been emptied.
- Reworked worker implementation to use the new Maleficarum RabbitMQ connection manager.
    - Persistent connections will be automatically added as command sources and trigger the multi source consumer mode if more than one are defined.
    - Transient connections will be available in command handlers via the connection manager to easily add commands to external queues.
- Complete rework on how handlers operate.
    - Added access to the rabbitmq connection manager to allow for easy command propagation.
    - Implemented handler encapsulation logic. This way each individual handler can define a list of encapsulators it wants called both prior to and after the handle process. 
    - Added an encapsulator interface for application specific encapsulator implementations.
    - Implemented the "Information" encapsulator - it adds basic command handle information to the logs. 
    - Implemented the "Deadletter" encapsulator - it adds the command to a deadletter queue when the handler returns false as the handle result. 

## [6.0.1] - 2017-11-14
### Changed
- Change getWorkerId method visibility

## [6.0.0] - 2017-08-03
### Changed
- Make use of nullable types provided in PHP 7.1 (http://php.net/manual/en/migration71.new-features.php)
- Fix tests

## [5.1.0] - 2017-05-10
### Added
- Pass config to handler object

## [5.0.0] - 2017-04-06
### Changed
- Bump components version
- Moved default initializers for external components into those components - they are no longer defined within this project.
- Added internal builder definitions and a mechanism to skip their loading in specific initializers.
- Decoupled bootstrap initialization functionalities from the main bootstrap object. As of know when using the bootstrap object one can and must provide a list of valid PHP callable types that will be run in order when the initialization process is executed.
- Default bootstrap initializers were separed from the main class as static methods to be used as needed on a case-by-case basis.

## [4.0.1] - 2017-03-08
### Changed
- Bump rabbitmq component version

## [4.0.0] - 2017-03-07
### Changed
- Bump rabbitmq component version

## [3.0.2] - 2017-02-22
### Changed
- Bump handler component version
- Make use of new command line handler
- Add tests

## [3.0.1] - 2017-02-13
### Fixed
- Change abstract command class namespace

## [3.0.0] - 2017-01-30
### Changed
- Moved command component to external repository

## [2.0.0] - 2017-01-30
### Changed
- Add return and argument types declaration

## [1.0.0] - 2017-01-10
### Added
- This was an initial release based on the code written by pharaun13 and added to the repo by me
