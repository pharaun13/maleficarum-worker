# Change Log
This is the Maleficarum Worker component implementation. 

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
