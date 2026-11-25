# Changelog

All notable changes to this module are documented here.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Initial release: SiteConfig settings (`UmamiActive`, `UmamiScriptURL`, `UmamiWebsiteID`)
  in a CMS "Analytics" tab.
- `UmamiTracker` include template, rendered only in configured `track_environments`.
- Optional vanilla-cookieconsent integration: registers the `analytics` category and
  `umami` service at runtime via the `updateCCJSConfig` hook. With active consent the
  tracker uses `data-src` instead of `src`, so the script is only fetched after consent.
- Translations (`lang/de.yml`, `lang/en.yml`) for the consent dialog's `analytics` category,
  stating that Umami is privacy-friendly and stores no cookies.
- PHPUnit tests: unit tests (`tests/UmamiTrackerTest.php`) and a `FunctionalTest`
  (`tests/UmamiTrackerHttpTest.php`, home page via `tests/fixtures.yml`, Fluent disabled via
  `$illegal_extensions`) plus a module-level `phpunit.xml.dist`; projects can include the
  module tests via their root `phpunit.xml.dist`.
