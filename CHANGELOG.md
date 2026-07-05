# Changelog

All notable changes to HandOff will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed

- **Deliverable review workflow:** agency submits deliverables for review (`draft`/`rejected` → `in_review`); only the project's client can approve or reject while `in_review`. Admins cannot approve or reject.
- **Agency edit lock:** deliverables in `in_review` or `approved` cannot be edited or receive file uploads by agency users.
- **Authorization at the edge:** policies enforce who can act; services perform state transitions only (no duplicate auth guards in services).
- **Hub Livewire pattern:** lists dispatch modals or handle row actions directly; Save\* modals authorize on open (`view`) and save (`create`/`update`).
- **Milestone completion:** all milestone status writes go through `MilestoneService::updateStatus()`; deliverable changes trigger `syncFromDeliverables()` synchronously (not via listeners).
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
- Multiple clients for one project
- Client portal (client-facing views and routes, including approve/reject UI)
- Email notifications
- Invoice and Receipt Future
- Expenses and Break Down
