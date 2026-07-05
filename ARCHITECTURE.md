# HandOff Architecture

This document describes how the **admin workspace** is structured. It complements [`CONTRIBUTING.md`](CONTRIBUTING.md) (development workflow) and is the source of truth for layering, naming, and UI conventions.

> **AI agents:** See [`AGENTS.md`](AGENTS.md) for code-generation rules. This file is the human-readable reference.

## Directory Tour

New to the codebase? Start here.

```
app/
├── Actions/Fortify/          # Fortify auth actions (CreateNewUser, etc.)
├── Concerns/                 # Shared traits (WithActionRateLimiting, WithNotifications)
├── Data/                     # DTOs — typed input/output at service boundaries
│   ├── Clients/
│   ├── Credentials/
│   ├── Deliverables/
│   ├── Meetings/
│   ├── Milestones/
│   └── Projects/
├── Enums/                    # Backed enums per domain (Status, Action, Role, etc.)
├── Events/                   # Domain events (ProjectEvent, MilestoneEvent, …)
├── Http/
│   ├── Controllers/          # Thin controllers (ProjectHubController, settings)
│   └── Middleware/            # ensureAdmin, EnsureProjectAccess
├── Listeners/                # Event listeners (ActivityLog, Notifications, cache busting)
├── Livewire/
│   ├── Actions/              # Reusable Livewire action classes
│   ├── Agency/               # Admin workspace components (projects, clients, hub)
│   └── Settings/             # User/account settings components
├── Models/                   # Eloquent models (Project, Milestone, Deliverable, …)
├── Policies/                 # Authorization policies
├── Providers/                # Service providers (App, Fortify, etc.)
└── Services/                 # Business logic layer (ProjectService, ClientService, …)

config/
├── navigation.php            # Sidebar navigation — add items here, not in Blade
├── fortify.php               # Fortify auth configuration
└── …

resources/
├── css/app.css               # Tailwind + brand tokens (@theme / :root)
├── views/
│   ├── layouts/              # App shell (app.blade.php, auth.blade.php)
│   ├── components/           # Blade components (x-ui.*, x-agency.*, x-sidebar.*)
│   ├── pages/auth/           # Fortify pages (login, register, etc.)
│   └── livewire/             # Volt single-file components (settings)
└── js/                       # Minimal JS — most interactivity is Livewire/Alpine

tests/
├── Feature/                  # Pest feature tests
└── Unit/                     # Pest unit tests

routes/
├── web.php                   # Public + admin routes
└── settings.php              # User settings routes
```

## Terminology

| Term                   | In code                                                 | Meaning                                                                                                      |
| ---------------------- | ------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------ |
| **Admin**              | `AccountRole::ADMIN`, `ensureAdmin`, `$user->isAdmin()` | The agency operator — owns clients, projects, and hub data.                                                  |
| **Agency (routes/UI)** | `agency.*` routes, `App\Livewire\Agency`, `x-agency.*`  | Same admin workspace in product naming — URLs and Blade namespaces use “agency”; auth and roles use “admin”. |
| **Client**             | `AccountRole::CLIENT`, portal routes (future)           | End customer; updates their own profile via settings, not admin modals.                                      |

When docs say **admin workspace**, that is the same surface as `/agency/…` in the app.

## Layers

```
Route / Page Blade
    └── Livewire (list, view flyout, Save* modal)
            └── Service (domain logic, persistence)
                    └── Model + Domain Event
                            └── Listeners (cache, activity, notifications)
```

| Layer          | Responsibility                                                        | Examples                                                     |
| -------------- | --------------------------------------------------------------------- | ------------------------------------------------------------ |
| **HTTP**       | Auth middleware, load authorized models, return Blade                 | `ensureAdmin`, `EnsureProjectAccess`, `ProjectHubController` |
| **Livewire**   | UI state, validation, authorization on write, dispatch refresh events | `ProjectsList`, `SaveProject`, `MilestonesList`              |
| **Data (DTO)** | Typed input/output at service boundaries                              | `SaveProjectData`, `ProjectOverviewData`                     |
| **Service**    | Business rules, queries, mutations, domain events                     | `ProjectService`, `ClientService`, `MilestoneService`        |
| **Events**     | Side effects decoupled from the caller                                | `ProjectEvent`, `ForgetProjectOverviewCache`                 |

## Mutation pattern (admin workspace)

Every create/update flow follows the same pipeline (except **client invite** — create-only; clients edit their own profile):

1. **List or hub page** mounts a `Save{Domain}` modal and dispatches `open-save-{domain}` (optionally with `uniqueId` for edit).
2. **`Save{Domain}::open()`** resets form state, loads existing record when editing, `$this->authorize()`, shows the Flux modal.
3. **`Save{Domain}::save()`** validates, builds `Save{Domain}Data::fromArray()`, calls the service, notifies, closes modal, dispatches `{domain}-created` or `{domain}-updated`.
4. **Service** accepts **only DTOs** — never raw arrays.
5. **Domain event** fires; listeners handle cache busting, audit trails, etc.

### Save\* components

| Domain      | Livewire          | DTO                   | Service methods                          |
| ----------- | ----------------- | --------------------- | ---------------------------------------- |
| Project     | `SaveProject`     | `SaveProjectData`     | `createProject`, `updateProject`         |
| Client      | `SaveClient`      | `SaveClientData`      | `createClient` (invite only)             |
| Milestone   | `SaveMilestone`   | `SaveMilestoneData`   | `createMilestone`, `updateMilestone`     |
| Deliverable | `SaveDeliverable` | `SaveDeliverableData` | `createDeliverable`, `updateDeliverable` |
| Credential  | `SaveCredential`  | `SaveCredentialData`  | `createCredential`, `updateCredential`   |
| Meeting     | `SaveMeeting`     | `SaveMeetingData`     | `scheduleMeeting`, `updateMeeting`       |

**Exception:** `ViewCredential` stays separate — reveal/copy UX is read-focused, not a save form.

### DTO conventions

- Location: `app/Data/{Domain}/Save{Thing}Data.php`
- `readonly` class with constructor property promotion
- `fromArray(array $validated)` — maps Livewire-validated keys to typed properties
- `toAttributes()` or `toCreateAttributes()` / `toUpdateAttributes()` — maps back to Eloquent fill arrays
- **Read-only page DTOs** (e.g. `ProjectOverviewData`, `ProjectOverviewStats`) have no `fromArray`; services construct them directly

## Admin surfaces

### Index pages (projects, clients)

- `x-ui.page-header` + searchable `*List` Livewire component
- Empty state uses `x-ui.empty-state` with a button that dispatches `open-save-*`
- Page Blade includes sibling modals: `SaveProject`, `SaveClient`, `ViewProject`, `ViewClient`
- Lists listen for `{domain}-created` / `{domain}-updated` to reset pagination

### Project hub

- **Routes:** `/agency/projects/{projectUniqueId}/…` — overview, milestones, deliverables, credentials, meetings
- **Middleware:** `EnsureProjectAccess` loads the project, enforces `ProjectPolicy::view`, attaches it to the request
- **Shell:** `x-agency.project-hub.shell` — tabs, breadcrumbs, open content (no clip-path card)
- **Sections:** `x-agency.project-hub.section` wraps list panels; overview uses the same header styles
- **Hub Livewire** receives `projectUniqueId` only — never `mount(Project $project)` — keeps components portable and testable
- **Overview cache:** scalar stats cached 5 minutes; busted by `ForgetProjectOverviewCache` on milestone/deliverable/credential/meeting events

## Authorization

- **Route level:** `ensureAdmin` on all `/agency/*` routes; hub adds project access middleware
- **Livewire writes:** `$this->authorize()` in `Save*::open()` and `Save*::save()` (and inline actions like `approve()`, `revealPassword()`)
- **Policies:** admin abilities often short-circuit in `before()`; explicit Livewire checks remain for defense in depth
- List components that only dispatch `open-save-*` rely on the modal to re-check — no duplicate authorize trait

## UI components

| Purpose               | Component                                                            |
| --------------------- | -------------------------------------------------------------------- |
| Branded form controls | `x-ui.*` (button, input, checkbox, modal-footer)                     |
| App chrome            | `flux:*` sidebar, header, modal, toast                               |
| Hub sections          | `x-agency.project-hub.section`, `x-agency.project-hub.shell`         |
| Data tables           | `x-ui.data-table` with `x-ui.data-table.view-button` for row actions |

Modals use `flux:modal` + `x-ui.modal-footer` + `x-ui.button`. Primary actions call `wire:click="save"`.

## Events and list refresh

| Event                                 | Dispatched by    | Listened by     |
| ------------------------------------- | ---------------- | --------------- |
| `open-save-project`                   | `ProjectsList`   | `SaveProject`   |
| `project-created` / `project-updated` | `SaveProject`    | `ProjectsList`  |
| `open-save-client`                    | `ClientsList`    | `SaveClient`    |
| `client-created`                      | `SaveClient`     | `ClientsList`   |
| `open-save-milestone`                 | `MilestonesList` | `SaveMilestone` |
| …                                     | …                | …               |

Hub list components follow the same `{domain}-created` / `{domain}-updated` pattern.

## What we avoid

- **Create* vs Save* split** — one modal per domain, edit-ready from day one
- **Arrays in service method signatures** — always DTOs at the boundary
- **“Until migrated” exceptions** — index and hub use the same pipeline
- **Models in Livewire mount** on hub components — use `projectUniqueId` string + service lookup
- **Clip-path panels in the hub shell** — reserved for auth, landing, settings cards

## Adding a new admin domain

1. Add `Save{Thing}Data` under `app/Data/{Domain}/`
2. Add `create*` / `update*` on `{Thing}Service` accepting the DTO
3. Dispatch `{Thing}Event` on mutation
4. Add `Save{Thing}` Livewire with `open-save-{thing}` + `save()`
5. Wire list empty states and edit actions to dispatch the open event
6. Add Pest feature tests for Livewire save flows and service DTO tests
7. Register cache/event listeners if the domain affects project overview stats

---

## UI & View Conventions

### Layouts

- **Authenticated app:** `<x-layouts::app>` or Livewire `#[Layout('layouts.app')]`. Admin vs client chrome is resolved in [`resources/views/layouts/app.blade.php`](resources/views/layouts/app.blade.php).
- **Sidebar nav:** add items in [`config/navigation.php`](config/navigation.php) — do not edit sidebar Blade for new routes.
- **Guest/auth:** `<x-layouts::auth>` for Fortify pages under `resources/views/pages/auth/`.
- **Clip-path surfaces:** reserve `handoff-clip-*` for auth, landing hero, and settings card — not scattered in the app shell.

### Component Catalog

#### Form & Layout Primitives (`x-ui.*`)

Branded HandOff controls — use these instead of raw HTML:

| Component           | Purpose                                         | Key Props                                 |
| ------------------- | ----------------------------------------------- | ----------------------------------------- |
| `x-ui.button`       | Primary/secondary/danger buttons                | `variant`, `wire:click`, `disabled`       |
| `x-ui.input`        | Text inputs, textareas, selects                 | `label`, `wire:model`, `type`, `error`    |
| `x-ui.checkbox`     | Single checkbox with label                      | `wire:model`, `label`                     |
| `x-ui.divider`      | Horizontal rule with optional label             | `label`                                   |
| `x-ui.logo-mark`    | HandOff brand mark                              | —                                         |
| `x-ui.page-header`  | Page title + subtitle + actions bar             | `heading`, `subheading`, `actions` slot   |
| `x-ui.modal-footer` | Modal action buttons (use instead of Flux slot) | `align` (start/center/end, default: end)  |
| `x-ui.empty-state`  | Empty collection placeholder                    | `icon`, `heading`, `text`, `actions` slot |
| `x-ui.data-table`   | Panel-wrapped `flux:table` with pagination      | `:paginate="$this->items"`                |

**Modal pattern:** `flux:modal` + `x-ui.modal-footer` + `x-ui.button`. Do NOT use `x-slot name="footer"` — it doesn't render in Flux free edition.

#### App Chrome

- **Shell/nav:** raw `flux:*` (sidebar, header, toast, modal) — don't rebuild what Flux ships.
- **Sidebar pieces:** `x-sidebar.*` for sidebar footer/header customizations.
- **Feature UI:** group under `components/{dashboard,marketing,settings}/` or `livewire/{domain}/`.

#### Data Tables (`x-ui.data-table`)

Admin index tables (clients, projects) live under `livewire/agency/` and `/agency/*` routes.

- **`x-ui.data-table.primary-cell`** — props: `title`, `meta` (mobile-only subline). Slots: `action` (view button, mobile-only, inline with title), `mobile` (badge under meta on mobile).
- **`x-ui.data-table.action-cell`** — view/action column; visible from `sm` up only.
- **`x-ui.data-table.view-button`** — standard agency row action (`icon="eye"`). Props: `wireClick`, `name`.
- **`x-ui.data-table.empty`** — em dash for empty **cell** values inside a table row.

**Responsive behavior:**

- **Mobile:** one column — title with inline action, meta line (`line-clamp-2`), status badge. No horizontal scroll.
- **Desktop:** extra columns at `sm` (status, action), `md` (email/client, budget), `lg` (date).

**Empty collection:** `x-ui.page-header` + `x-ui.empty-state` on admin index pages (`/agency/clients`, `/agency/projects`). Hub section tabs use [`x-agency.project-hub.section`](resources/views/components/agency/project-hub/section.blade.php) with in-panel `project-overview__empty` instead.

### Mutation Data (DTOs)

Typed input objects live under `app/Data/{Domain}/`.

- **Read payloads** (e.g. `ProjectOverviewData`): constructor-only.
- **Write payloads**: `readonly` class with `fromArray()` and `toAttributes()` (or explicit mapping in the service).

**Naming:** admin mutations use **`Save{Thing}Data` + `Save{Thing}`** — one modal per domain, handles both create and edit. **`SaveClient` is invite-only** (clients update their own profile in settings).

**Pipeline:** Livewire `Save{Domain}` → validate → `Save{Domain}Data::fromArray()` → `{Domain}Service::{create|update}*` → `{Domain}Event` → listeners (cache, activity, notifications).

### Authorization (Admin)

- **Page access:** `ensureAdmin` on `/agency/*` routes; hub adds `EnsureProjectAccess` (`ProjectPolicy::view` on the project).
- **Livewire writes:** `$this->authorize()` on the action that mutates (`Save*::save()`, `approve()`, `ViewCredential::revealPassword()`). List components that only dispatch `open-save-*` rely on the modal to re-check — no separate authorize trait needed (`AuthorizesRequests` is on Livewire `Component`).
- **Policies:** admin abilities often pass via `before()`; explicit Livewire checks remain for defense-in-depth and future client/portal access on the same models.

### Admin Project Hub (Full Detail)

Project detail uses **controller-guarded pages** under `/agency/projects/{projectUniqueId}` (admin workspace; `ensureAdmin` on entry). Nested work has its own route; the tab bar reflects the active section. No models in route definitions.

- **HTTP stack:** `ensureAdmin` → [`EnsureProjectAccess`](app/Http/Middleware/EnsureProjectAccess.php) → [`ProjectHubController`](app/Http/Controllers/Agency/Projects/ProjectHubController.php).
    - Middleware loads the project via `ProjectService`, enforces `ProjectPolicy::view`, attaches `Project` to the request (`EnsureProjectAccess::PROJECT_ATTRIBUTE`).
    - Controller reads the authorized project and returns Blade only.
- **Routes:** `agency.projects.show`, `.milestones`, `.deliverables`, `.credentials`, `.meetings` — param is `{projectUniqueId}` (UUID string).
- **Shell:** [`x-agency.project-hub.shell`](resources/views/components/agency/project-hub/shell.blade.php) — breadcrumbs, title, status badge inline with client meta, tab links (`wire:navigate`), open content slot (no clip-path card). Chrome only — no modals. Section pages use [`x-agency.project-hub.section`](resources/views/components/agency/project-hub/section.blade.php) (`handoff-panel` + `project-hub__section-header`; overview blocks reuse the same header styles via `project-overview__section-header`).
- **Overview:** [`ProjectService::getProjectOverview()`](app/Services/ProjectService.php) — scalar stats cached 5 minutes (`ProjectOverviewStats`); milestone pipeline, recent deliverables, and next meeting load fresh. Cache bust via [`ForgetProjectOverviewCache`](app/Listeners/Projects/ForgetProjectOverviewCache.php) on deliverable/credential/meeting/milestone domain events. Progress is computed from completed milestones (`calculateProgress()`), not the `projects.progress_percentage` column.
- **Modals:** unified `Save*` Livewire components per domain (`SaveProject`, `SaveMilestone`, …) opened via `open-save-*` events. `SaveClient` invites only. Credentials keep separate `ViewCredential` for reveal UX.
- **Navigation:** `ProjectsList` → flyout glance (`ViewProject`) → Open project → hub overview page.
- **Livewire** list/modal components receive `projectUniqueId` only — no `mount(Project)`. Mutations authorize on the action (`approve`, `create`, etc.).
- Milestone → deliverables: link to `agency.projects.deliverables?milestone={id}`.

### Livewire Component Style

- **Admin Livewire** — namespaces under `app/Livewire/Agency/` (agency = admin workspace in URLs/components):
    - Root: `ProjectsList`, `SaveProject`, `ViewProject` (index + flyout)
    - `Milestones/`, `Deliverables/`, `Credentials/`, `Meetings/` — list + `Save*` modal components per domain
    - Blade tags: `agency.projects.milestones.milestones-list`, `agency.projects.credentials.save-credential`, etc.
- **Admin clients** — `ClientsList`, `SaveClient` (invite), `ViewClient` under `app/Livewire/Agency/Clients/`.
- **Volt** (`livewire/settings/profile.blade.php`): simple CRUD pages with little state.
- **Class-based** (`app/Livewire/Settings/`): modals, `#[Locked]`, Fortify actions, security flows.
- Avoid anonymous `new class extends Component` in Blade unless the UI is truly throwaway.

### CSS Conventions

- Brand tokens live in `@theme` / `:root` in `resources/css/app.css`.
- Clip corners: `--handoff-clip-path-md` and `--handoff-clip-path-sm` — change once, applies everywhere.
- `settings-layout__*` uses a separate BEM namespace for settings tabs/panel.
