# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).


## [3.0.1] - 2026-06-21

### Fixed
- Font is dynamically reduced based on character count to stop long entries overflowing the widget box.


## [3.0.0] - 2026-06-18

### Added
- "On This Day" API from ZenQuotes.io API as additional provider of content 
- Personal Settings screen to select what the widget content - the options are Daily Zen Quote (default), On This Day - Events, On This Day - Notable Births and Deaths, On This Day combined
- Selected option is stored as a per-user preference

### Changed
- The widget title and content depends on the user's saved preference
- The widget shows a wrap-around carousel with next/back arrows when there is more than one item to show (i.e. On This Day content)


## [2.0.4] - 2026-06-13

### Changed
- Removed support for Nextcloud 31


## [2.0.3] - 2026-06-08

### Changed
- More legible error message when quote cannot be loaded
- Bumped front-end and PHP dependencies
- Bumped Nextcloud max version


## [2.0.2] - 2026-05-14

### Changed
- Added screenshot and additional information to show on the NextCloud App Store listing


## [2.0.1] - 2026-05-13

### Changed
- Frontend and PHP dependencies bumped


## [2.0.0] - 2026-04-29

### Removed
- The main screen has been removed, this is now purely a dashboard widget


## [1.0.0] - 2026-04-25

### Added
- Main app screen and dashboard widget, both pulling the daily quote from ZenQuotes.io
