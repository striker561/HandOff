# Changelog

All notable changes to HandOff will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Initial admin workspace: projects, clients, milestones, deliverables, credentials, meetings
- Service-based architecture with typed DTOs (`Save{Domain}Data`)
- Domain event system with consolidated listeners for activity logging and notifications
- `Save*` Livewire modal pattern — one component per domain for create + edit
- Agency project hub with middleware-guarded sections
- UUID-based route keys to prevent enumeration attacks
- `BaseCRUDService` with standardized filtering, search, and pagination
- `composer setup` one-command install
- `composer dev` concurrent development server
- Pest test suite (113 tests, all passing)
- Laravel Pint code style enforcement
- PHPStan static analysis
- CI pipeline (GitHub Actions)

### Planned

- Redesign for UX and UI consistency 
- Client portal (client-facing views and routes)
- File upload / approval workflows for deliverables
- Email notifications
- Two-factor authentication
- Passkey support
