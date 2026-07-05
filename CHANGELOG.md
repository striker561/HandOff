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
- `AuthorizesProjectHubResources` trait for hub Save\* modals and list row actions
- Scoped service finders (`findDeliverableForProject`, `findCredentialForProject`, etc.)
- Policy tests under `tests/Feature/Policies/` and hub Livewire authorization tests (`ProjectHubAuthorizationTest`)
- `composer setup` one-command install
- `composer dev` concurrent development server
- Pest test suite with policy and hub authorization coverage
- Laravel Pint code style enforcement
- PHPStan static analysis
- CI pipeline (GitHub Actions)

### Changed

- **Deliverable review workflow:** agency submits deliverables for review (`draft`/`rejected` → `in_review`); only the project's client can approve or reject while `in_review`. Admins cannot approve or reject.
- **Agency edit lock:** deliverables in `in_review` or `approved` cannot be edited or receive file uploads by agency users.
- **Authorization at the edge:** policies enforce who can act; services perform state transitions only (no duplicate auth guards in services).
- **Hub Livewire pattern:** lists dispatch modals or handle row actions directly (e.g. `DeliverablesList::submitForReview()`); Save\* modals authorize on open (`view`, edit only) and save (`create`/`update`).
- **Milestone completion:** all milestone status writes go through `MilestoneService::updateStatus()`; deliverable changes trigger `syncFromDeliverables()` synchronously (not via listeners).

### Planned

- Redesign for UX and UI consistency
- Multiple clients for one project
- Client portal (client-facing views and routes, including approve/reject UI)
- Email notifications
- Invoice and Receipt Future
- Expenses and Break Down
