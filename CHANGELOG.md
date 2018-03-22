# Instant Analytics Changelog

All notable changes to this project will be documented in this file.

## 1.0.6 - 2018.03.22
### Added
* Send only the path, not the full URL to Google Analytics via `eventTrackingUrl()`
* Gutted the Commerce service, pending Craft Commerce 2

## 1.0.5 - 2018.02.01
### Added
* Renamed the composer package name to `craft-instantanalytics`

## 1.0.4 - 2018.01.10
### Changed
* Set the documentPath for events, too

## 1.0.3 - 2018.01.08
### Changed
* Fixed an issue with parsing of the `_ga`_ cookie

## 1.0.2 - 2018.01.02
### Changed
* Fixed the `eventTrackingUrl` to work properly

## 1.0.1 - 2017.12.06
### Changed
* Updated to require craftcms/cms `^3.0.0-RC1`
* Switched to `Craft::$app->view->registerTwigExtension` to register the Twig extension

## 1.0.0 - 2017-10-27
### Added
- Initial release
