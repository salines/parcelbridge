# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.2] - 2026-07-13

### Added

- Server-side invoice upload inspection for PDF, JPEG, and PNG content.
- Regression coverage for API CSRF protection, spoofed invoice uploads, password length, invoice replacement cleanup, and protected operational records.

### Changed

- Composer dependencies and GitHub Actions were updated to their current compatible releases.
- Dependabot version update pull requests now target `develop`.
- Password validation now requires at least eight characters.
- Package and ship-request workflow updates now use transactional conditional ORM updates to prevent concurrent duplicate processing.

### Fixed

- CakePHP ORM generic types and entity identifier access for PHPStan compatibility.
- CSRF protection for state-changing session-authenticated API endpoints; only the API login endpoint is exempt.
- Invoice MIME validation no longer trusts client-provided metadata or filename extensions.
- Replacing an invoice now removes the superseded generated file after a successful transaction.
- Failed package status transitions now roll back the complete ship-request operation.
- Attempts to delete users, clients, or packages with related operational records now return a controlled error instead of a database exception.

## [1.0.1] - 2026-05-20

### Added

- CI, PHP, CakePHP, and license badges.
- Composer `create-project` installation documentation and project package metadata.
- ParcelBridge project banner.

### Changed

- Updated Composer dependencies, including CakePHP migrations, FriendsOfCake Search and CsvView, Swagger UI, IDE Helper, and PHPStan integration.
- Updated GitHub Actions dependencies, including `actions/checkout` and `ramsey/composer-install`.
- CI now tests the supported PHP 8.3+ matrix and uses the project-local PHPStan executable.
- Environment loading preserves variables already supplied by the runtime.

### Fixed

- Removed the tracked local environment file and documented environment setup.
- Stabilized PHPStan dependency versions and CI execution.
- Removed already installed packages from Composer suggestions.

## [1.0.0] - 2026-05-11

- Initial ParcelBridge release.

[1.0.2]: https://github.com/salines/parcelbridge/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/salines/parcelbridge/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/salines/parcelbridge/releases/tag/v1.0.0
