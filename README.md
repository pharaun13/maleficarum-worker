# Change Log
This is the Maleficarum Worker component implementation. 

## [7.1.4] - 2018-05-17
### Changed
- Increased the hardcoded wait value for the multi source consumer mode to 10000 micro seconds.

## [7.1.3] - 2018-05-14
### Fixed
- Commands that do not pass internal validation will no longer cause an uncaught exception in the Deadletter and Retry encapsulators.

## [7.1.2] - 2018-05-14
### Added
- Added a mandatory wait period between attempting to execute the master process loop from scratch when in multi source consumer mode. This will be parameterized in up comming releases - for now it's a hardcoded wait value.

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
