# Instant Analytics Changelog

## 1.0.10 - 2018.08.25
### Changed
* Fixed an issue integrating with SEOmatic

## 1.0.9 - 2018.08.25
### Changed
* Fixed an issue where the return type-hinting was incorrect
* Handle cases where a `null` IAnalytics object is returned

## 1.0.8 - 2018.08.24
### Changed
* Fixed an issue where manually using the `{% hook isSendPageView %}` would throw an error

## 1.0.7 - 2018.08.24
### Added
* Added welcome screen after install
* Automatically set the `documentTitle` from the SEOmatic `<title>` tag, if SEOmatic is installed
* Automatically set the `affiliation` from the SEOmatic site name, if SEOmatic is installed

### Changed
* Lots of code cleanup
* Moved to a modern webpack build config for the AdminCP
* Added install confetti

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
