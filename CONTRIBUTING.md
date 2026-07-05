# Contributing to HandOff

## Architecture Overview

HandOff uses a service-based architecture where **all business logic lives in services**. The UI is **Livewire-first**: components validate input, authorize actions, and call service methods.

### Core Principles

1. **KISS (Keep It Simple, Stupid)** - One pattern over dozens of methods
2. **Actor-Required Events** - All user actions require an authenticated actor
3. **Consolidated Listeners** - One listener per domain, not per action

### Event System (Domain Events + Activity Logs + Notifications)

Every user-triggered action dispatches a domain event that automatically:

- Logs the action in the activity log
- Sends notifications to relevant users

**Key Rule:** Service methods that dispatch events **must require** `User $performedBy` (never nullable).

```php
// Correct - actor is required
public function createClient(array $data, User $performedBy): User
{
    $client = $this->create($data);

    ClientEvent::dispatch($client, ClientAction::CREATED, $performedBy, []);

    return $client;
}

// Wrong - nullable actor allows silent audit failures
public function createClient(array $data, ?User $performedBy = null): User
{
    $client = $this->create($data);

    if ($performedBy) {  // This conditional is NOT allowed
        ClientEvent::dispatch($client, ClientAction::CREATED, $performedBy, []);
    }

    return $client;
}
```

**Why:** If actor is optional, events/logs/notifications can be silently skipped. Authenticated Livewire actions always have `Auth::user()` available.

**Livewire components must pass the authenticated user:**

```php
public function create(): void
{
    $this->authorize('create', User::class);

    $validated = $this->validate([
        'name' => ['required', 'string', 'min:2', 'max:120'],
        'email' => ['required', 'email', 'max:190', 'unique:users,email'],
    ]);

    $this->clientService->createClient($validated, Auth::user());

    $this->notifySuccess(__('Client invited.'));
    $this->dispatch('client-created');
}
```

**Business rule failures in services** use `ValidationException::withMessages()` so Livewire renders field errors automatically:

```php
// ClientService
private function fieldError(string $field, string $message): never
{
    throw ValidationException::withMessages([
        $field => $message,
    ]);
}
```

No separate API response layer — services throw, Livewire displays.

### Project hub pages (detail routes)

Index pages (`clients`, `projects`) use `Route::view`. Project **detail sections** use a thin controller behind middleware:

```text
agency project hub routes
  → ensureAdmin (role)
  → ensureProjectAccess (ProjectService + ProjectPolicy::view → Project on request)
  → ProjectHubController (view only)
```

- [`EnsureProjectAccess`](app/Http/Middleware/EnsureProjectAccess.php) resolves `{projectUniqueId}`, 404 if missing, `Gate::authorize('view', $project)`, sets request attribute `project`.
- [`ProjectHubController`](app/Http/Controllers/Agency/Projects/ProjectHubController.php) reads the authorized project from the request and returns Blade.
- Shared shell: `x-agency.project-hub.shell` (chrome only). Section pages mount their own modals via `<x-slot:modals>`. Livewire components receive `projectUniqueId` only; policy on mutations stays in Livewire actions.

## Service Pattern Overview

All services extend `BaseCRUDService` which provides:

- Standard CRUD operations (create, update, delete, find)
- Filtering, searching, and sorting via `applyFilters()`
- Consistent pagination with `paginateQuery()`
- Automatic per-page validation (1-100 items)

### The Golden Rule

**Reference `ClientService` when building new services.** It's our gold standard implementation.

## Building a Service

### 1. Extend BaseCRUDService

```php
namespace App\Services;

use App\Models\YourModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class YourModelService extends BaseCRUDService
{
    protected string $modelClass = YourModel::class;
}
```

### 2. Define Searchable/Filterable/Sortable Columns

```php
protected function searchableColumns(): array
{
    return ['name', 'description', 'email'];
}

protected function filterableColumns(): array
{
    return ['status', 'type', 'is_active', 'created_at'];
}

protected function sortableColumns(): array
{
    return ['name', 'created_at', 'updated_at', 'status'];
}
```

**What this enables:**

- `?search=keyword` - searches across all searchableColumns
- `?status=active&type=client` - filters by exact matches
- `?sort=created_at&direction=desc` - sorts results

### 3. Use Scoped Query Methods

**Bad - Manual filters everywhere:**

```php
public function getAll(array $filters = []): LengthAwarePaginator
{
    $query = $this->model->query();

    if (isset($filters['project_id'])) {
        $query->where('project_id', $filters['project_id']);
    }

    if (isset($filters['status'])) {
        $query->where('status', $filters['status']);
    }

    return $query->paginate($filters['per_page'] ?? 15);
}
```

**Good - Scoped base query + applyFilters():**

```php
public function getMilestonesForProject(string $projectId, array $filters = []): LengthAwarePaginator
{
    $query = $this->model->where('project_id', $projectId);
    $query = $this->applyFilters($query, $filters);
    return $this->paginateQuery($query, $filters);
}
```

**Why this is better:**

- DRY - filtering logic lives in one place
- Consistent - all services behave the same
- Maintainable - change once, affects everywhere

### 4. The applyFilters() Pattern

`applyFilters()` in `BaseCRUDService` handles:

- **Search**: `?search=keyword` - ILIKE across all searchableColumns
- **Filters**: `?status=active&type=client` - exact matches on filterableColumns
- **Sorting**: `?sort=name&direction=asc`
- **Date ranges**: `?created_from=2026-01-01&created_to=2026-01-31`

You don't write this logic - just define the columns.

### 5. Pagination Strategy

**Always use `paginateQuery()`:**

```php
public function getSomething(array $filters = []): LengthAwarePaginator
{
    $query = $this->model->query();
    $query = $this->applyFilters($query, $filters);
    return $this->paginateQuery($query, $filters);
}
```

**Don't do this:**

```php
return $query->get();  // No pagination
return $query->paginate(15);  // Duplicates logic
```

## Real-World Examples

### ClientService (Standard CRUD with Events)

```php
public function createClient(array $data, User $performedBy): User
{
    $client = $this->create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make(Str::random(12)),
        'role' => AccountRole::CLIENT,
    ]);

    ClientEvent::dispatch($client, ClientAction::CREATED, $performedBy, []);

    return $client;
}

public function getClients(array $filters = []): LengthAwarePaginator
{
    $query = User::query()->clients();
    $query = $this->applyFilters($query, $filters);
    return $this->paginateQuery($query);
}

public function findClient(string $uniqueId): ?User
{
    return User::query()->clients()->where('unique_id', $uniqueId)->first();
}
```

Reference Livewire UI: `app/Livewire/Agency/Clients/` (`ClientsList`, `SaveClient`, `ViewClient`).

### MilestoneService (Scoped Queries)

```php
public function getMilestonesForProject(string $projectId, array $filters = []): LengthAwarePaginator
{
    $query = Milestone::where('project_unique_id', $projectId);
    $query = $this->applyFilters($query, $filters);
    return $this->paginateQuery($query, $filters);
}

public function updateStatus(Milestone $milestone, MilestoneStatus $status, User $performedBy): Milestone
{
    $milestone->update(['status' => $status]);

    $action = $status === MilestoneStatus::COMPLETED
        ? MilestoneAction::COMPLETED
        : MilestoneAction::STATUS_CHANGED;

    MilestoneEvent::dispatch($milestone, $action, $performedBy, []);

    return $milestone->fresh();
}
```

### DeliverableService (File Operations + Events)

```php
public function uploadFile(Deliverable $deliverable, UploadedFile $file, User $uploadedBy): DeliverableFile
{
    return DB::transaction(function () use ($deliverable, $file, $uploadedBy) {
        $path = $this->storage->putFileAs("deliverables/{$deliverable->project_unique_id}", $file, $filename);

        $deliverableFile = DeliverableFile::create([...]);

        DeliverableEvent::dispatch(
            $deliverable,
            DeliverableAction::FILE_UPLOADED,
            $uploadedBy,
            ['file_unique_id' => $deliverableFile->unique_id]
        );

        return $deliverableFile;
    });
}
```

## Storage Abstraction

Use `StorageService` instead of `Storage::disk()` directly:

```php
use App\Services\Storage\StorageService;

class DeliverableService extends BaseCRUDService
{
    private StorageService $storage;

    public function __construct()
    {
        $this->storage = new StorageService('filesystems.deliverables_disk');
    }

    public function uploadFile(Deliverable $deliverable, UploadedFile $file, User $uploadedBy): DeliverableFile
    {
        $path = $this->storage->putFileAs("deliverables/{$deliverable->project_unique_id}", $file, $filename);
        // Store path in database...
    }
}
```

**Why:** Swap storage backends via `.env` without code changes (local/S3/DigitalOcean Spaces).

## Type Safety & Enums

Always use type hints and enums:

```php
use App\Enums\Milestone\MilestoneStatus;

public function updateStatus(Milestone $milestone, MilestoneStatus $status, User $performedBy): Milestone
{
    $milestone->update(['status' => $status]);
    return $milestone;
}
```

**Available Enums:** Check `/app/Enums/` for complete list (ProjectStatus, DeliverableStatus, MilestoneStatus, etc.).

## Database Transactions

Wrap multi-step operations:

```php
public function uploadFile(Deliverable $deliverable, UploadedFile $file, User $uploadedBy): DeliverableFile
{
    return DB::transaction(function () use ($deliverable, $file, $uploadedBy) {
        $path = $this->storage->putFileAs(...);
        $deliverableFile = DeliverableFile::create([...]);
        $deliverable->update(['version' => $nextVersion]);

        return $deliverableFile;
    });
}
```

## Common Pitfalls

### Don't Create Redundant Methods

```php
// Bad
public function getActiveProjects(): Collection
{
    return $this->model->where('status', 'active')->get();
}
```

```php
// Good
public function getProjects(array $filters = []): LengthAwarePaginator
{
    $query = $this->model->query();
    $query = $this->applyFilters($query, $filters);
    return $this->paginateQuery($query, $filters);
}

// Usage: $service->getProjects(['status' => 'active']);
```

### Don't Use Nullable Actor Parameters

```php
// Bad - allows silent event/log failures
public function createProject(array $data, ?User $performedBy = null): Project
{
    $project = $this->create($data);

    if ($performedBy) {
        ProjectEvent::dispatch($project, ProjectAction::CREATED, $performedBy, []);
    }

    return $project;
}
```

```php
// Good - actor is always required
public function createProject(array $data, User $performedBy): Project
{
    $project = $this->create($data);

    ProjectEvent::dispatch($project, ProjectAction::CREATED, $performedBy, []);

    return $project;
}
```

### Don't Duplicate Filter Logic

```php
// Bad
if (isset($filters['status'])) {
    $query->where('status', $filters['status']);
}
```

```php
// Good - define once
protected function filterableColumns(): array
{
    return ['status', 'type'];
}
```

### Don't Mix Concerns

```php
// Bad - validation in service
public function createProject(array $data): Project
{
    if (empty($data['name'])) {
        throw new Exception('Name required');
    }
    return $this->create($data);
}
```

```php
// Good - input validation in Livewire, business rules in service
public function create(): void
{
    $validated = $this->validate([
        'name' => ['required', 'string', 'max:255'],
    ]);

    $this->projectService->createProject($validated, Auth::user());
}

public function createProject(array $data, User $performedBy): Project
{
    return $this->create($data);
}
```

## Reference Files

When in doubt, read the code:

- **`ClientService`** - cleanest implementation with events and `findClient()`
- **`app/Livewire/Agency/Clients/`** - Livewire list, create modal, view flyout pattern
- **`MilestoneService`** - scoped queries and status updates
- **`DeliverableService`** - file operations + transactions
- **`BaseCRUDService`** - filtering and pagination patterns
- **`NotifyOnDomainEvent`** - consolidated listener routing
