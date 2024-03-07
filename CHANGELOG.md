
# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [v1.0.2] - 2024-03-07

### Added

- Add local file "`plugins/geoportail/autoconf.custom.json`" to avoid API termination.

### Fixed

- Remove the `apiKey` parameter from `geoportalLayer` to use the correct API URL.

## [v1.0.1] - 2023-09-07

### Added

- This CHANGELOG file.

### Fixed

- Replace From header in sended email by Reply-To to allow use of MTA
  like Msmtp.

### Removed

- Displayed Choris link to description for each taxa. The Choris web
  interface no longer exists.

## [v1.0.0] - 2022-08-13

### Added

- Use of Github repo and release system.

### Changed

- Cleaned all files (indenting).
- Renamed files an directory.

### Fixed

- Update library used for the map.

[unreleased]: https://github.com/cbn-alpin/cbna-saisie-flore/compare/v1.0.1...HEAD
[v1.0.1]: https://github.com/cbn-alpin/cbna-saisie-flore/compare/v1.0.0...v1.0.1
[v1.0.0]: https://github.com/cbn-alpin/cbna-saisie-flore/releases/tag/v1.0.0
