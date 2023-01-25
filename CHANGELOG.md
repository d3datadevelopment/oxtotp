# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased](https://git.d3data.de/D3Public/oxtotp/compare/2.0.0.1...rel_2.x)
- add compatibility to D3 Webauthn plugin
- installable in OXID 6.5.1

## [2.0.0.1](https://git.d3data.de/D3Public/oxtotp/compare/2.0.0.0...2.0.0.1) - 2022-11-09
### Fixed
- Further protection of the login

## [2.0.0.0](https://git.d3data.de/D3Public/oxtotp/compare/1.1.0.0...2.0.0.0) - 2022-09-30
### Added
- installable in OXID 6.3 to 6.5
- tests completed
### Removed
- support for OXID < 6.3

## [1.1.0.0](https://git.d3data.de/D3Public/oxtotp/compare/1.0.0.0...1.1.0.0) - 2022-09-30
### Added
- installable in OXID 6.0 to 6.2
- mandatory 2FA use for all admin accounts

## [1.0.0.0](https://git.d3data.de/D3Public/oxtotp/releases/tag/1.0.0.0) - 2019-08-19
### Added
- 2-factor authentication for logins in front- and backend in addition to username and password
- Activation and setup possible in the front and back end
- Authentication is shown for user accounts that have this enabled - otherwise the usual default login.
- Access can be set up in the Auth app by scannable QR code or copyable character string
- Validation of one-time passwords and generation of QR codes are only carried out within the shop - no communication to the outside necessary
- static backup codes also allow (limited) login without access to the generation tool
