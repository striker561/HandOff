# HandOff Architecture

This document describes how the **admin workspace** is structured. It complements [`AGENTS.md`](AGENTS.md) (agent conventions) and is the source of truth for layering and naming.

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
