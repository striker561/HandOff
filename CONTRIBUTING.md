# Contributing to HandOff

## Service Layer Architecture

HandOff uses a service-based architecture where **all business logic lives in services**, not controllers. Controllers are thin wrappers that validate input and call service methods.

### Core Principle: KISS (Keep It Simple, Stupid)

We favor **one well-designed pattern** over dozens of specialized methods. If `applyFilters()` can handle it, don't write a custom method.

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
- `?sort_by=created_at&sort_direction=desc` - sorts results

### 3. Use Scoped Query Methods (Not Bloated Filters)

**Bad - Manual filters everywhere:**

```php
public function getAll(?array $filters = []): LengthAwarePaginator
{
    $query = $this->model->query();
    
    if (isset($filters['project_id'])) {
        $query->where('project_id', $filters['project_id']);
    }
    
    if (isset($filters['status'])) {
        $query->where('status', $filters['status']);
    }
    
    if (isset($filters['search'])) {
        $query->where('name', 'like', "%{$filters['search']}%");
    }
    
    return $query->paginate($filters['per_page'] ?? 15);
}
```

**Good - Scoped base query + applyFilters():**

```php
public function getMilestonesForProject(string $projectId, ?array $filters = []): LengthAwarePaginator
{
    $query = $this->model->where('project_id', $projectId);
    $query = $this->applyFilters($query, $filters);
    return $this->paginateQuery($query, $filters);
}
```

**Why this is better:**

- DRY - filtering logic lives in one place (`applyFilters`)
- Consistent - all services behave the same
- Maintainable - change filter behavior once, affects all services
- Scoped - base query sets the context, filters refine it

### 4. The applyFilters() Pattern

`applyFilters()` in `BaseCRUDService` handles:

- **Search**: `?search=keyword` - ILIKE across all searchableColumns
- **Filters**: `?status=active&type=client` - exact matches on filterableColumns
- **Sorting**: `?sort_by=name&sort_direction=asc`
- **Date ranges**: `?created_from=2026-01-01&created_to=2026-01-31`

You don't write this logic - just define the columns.

### 5. Pagination Strategy

**Always use `paginateQuery()` for data retrieval:**

```php
public function getSomething(?array $filters = []): LengthAwarePaginator
{
    $query = $this->model->query();
    $query = $this->applyFilters($query, $filters);
    return $this->paginateQuery($query, $filters);
}
```

**Never do this:**

```php
// Returns Collection - doesn't scale, no pagination metadata
return $query->get();

// Manual pagination - duplicates logic
return $query->paginate($filters['per_page'] ?? 15);
```

`paginateQuery()` enforces 1-100 items per page and provides consistent pagination metadata.

## Real-World Examples

### Example 1: MilestoneService (Clean Scoped Methods)

```php
public function getMilestonesForProject(string $projectId, ?array $filters = []): LengthAwarePaginator
{
    $query = $this->model->where('project_id', $projectId);
    $query = $this->applyFilters($query, $filters);
    return $this->paginateQuery($query, $filters);
}
```

**Usage:**

```php
// Get all milestones for a project
$milestones = $milestoneService->getMilestonesForProject($projectId);

// With status filter
$milestones = $milestoneService->getMilestonesForProject($projectId, [
    'status' => MilestoneStatus::COMPLETED->value
]);

// With search and sorting
$milestones = $milestoneService->getMilestonesForProject($projectId, [
    'search' => 'design',
    'sort_by' => 'due_date',
    'sort_direction' => 'asc',
    'per_page' => 25
]);
```

### Example 2: DeliverableService (Nested Scopes)

```php
public function getDeliverablesForMilestone(string $milestoneId, ?array $filters = []): LengthAwarePaginator
{
    $query = $this->model->where('milestone_id', $milestoneId);
    $query = $this->applyFilters($query, $filters);
    return $this->paginateQuery($query, $filters);
}
```

Supports: Project → Milestone → Deliverable hierarchy

### Example 3: NotificationService (User Context)

```php
public function getUserNotifications(string $userId, ?array $filters = []): LengthAwarePaginator
{
    $query = $this->model->where('user_id', $userId);
    $query = $this->applyFilters($query, $filters);
    return $this->paginateQuery($query, $filters);
}
```

**Usage:**

```php
// Unread notifications only
$notifications = $notificationService->getUserNotifications($userId, [
    'read_at' => null  // filterableColumns includes read_at
]);
```

### Example 4: CommentService (Custom Logic)

Sometimes you need custom behavior. That's fine - just don't duplicate what `applyFilters()` already does:

```php
public function getCommentsForEntity(string $entityType, string $entityId, bool $includeInternal = false): Collection
{
    $query = $this->model
        ->where('commentable_type', $entityType)
        ->where('commentable_id', $entityId)
        ->whereNull('parent_id');  // Top-level only
    
    if (!$includeInternal) {
        $query->where('is_internal', false);
    }
    
    return $query->with('user', 'replies.user')->get();
}
```

**When to use Collection vs LengthAwarePaginator:**

- **Collection**: Small datasets (comments on one entity, user's roles)
- **LengthAwarePaginator**: Tables, lists, potentially large datasets

## Storage Abstraction Pattern

Never use `Storage::disk('s3')` directly. Use `StorageService`:

```php
use App\Services\Storage\StorageService;

class DeliverableService extends BaseCRUDService
{
    public function __construct(protected StorageService $storage)
    {
        parent::__construct();
    }
    
    public function uploadFile(string $deliverableId, UploadedFile $file): DeliverableFile
    {
        $path = $this->storage->putFileAs(
            "deliverables/{$deliverableId}",
            $file,
            $file->getClientOriginalName()
        );
        
        // Store path in database
    }
}
```

**Why:**

- Swap storage backends via `.env` (local/S3/DigitalOcean Spaces)
- Consistent interface across all file operations
- Graceful fallbacks (e.g., `temporaryUrl()` returns null for local)

## Type Safety

Always use type hints. We're on PHP 8.2+:

```php
public function createMilestone(array $data): Milestone
{
    // ...
}

public function getMilestonesForProject(string $projectId, ?array $filters = []): LengthAwarePaginator
{
    // ...
}

protected function searchableColumns(): array
{
    return ['title', 'description'];
}
```

## Enums Over Strings

Use enums for status/type fields:

```php
use App\Enums\Milestone\MilestoneStatus;

public function completeMilestone(string $milestoneId): Milestone
{
    return $this->update($milestoneId, [
        'status' => MilestoneStatus::COMPLETED,
        'completed_at' => now()
    ]);
}
```

**Available Enums:**

- `ProjectStatus`, `DeliverableStatus`, `MilestoneStatus`
- `NotificationType`, `CredentialType`, `MeetingStatus`
- Check `/app/Enums/` for complete list

## Database Transactions

Wrap multi-step operations in transactions:

```php
public function uploadFile(string $deliverableId, UploadedFile $file): DeliverableFile
{
    return DB::transaction(function () use ($deliverableId, $file) {
        // 1. Store file
        $path = $this->storage->putFileAs(...);
        
        // 2. Create database record
        $deliverableFile = DeliverableFile::create([...]);
        
        // 3. Update deliverable
        $deliverable->update([...]);
        
        return $deliverableFile;
    });
}
```

If step 2 or 3 fails, step 1 is rolled back (file is deleted).

## Queue Pattern

We use database queues for fast background processing:

```php
use Illuminate\Support\Facades\Queue;

public function notifyDeliverableApproved(string $deliverableId): void
{
    Queue::push(function () use ($deliverableId) {
        // Send email, create notification, log activity
    });
}
```

**Config:** `QUEUE_CONNECTION=database` in `.env`

## Common Pitfalls

### Don't Create Redundant Methods

```php
// Bad - this is what applyFilters() does
public function getActiveProjects(): Collection
{
    return $this->model->where('status', 'active')->get();
}

public function searchProjects(string $keyword): Collection
{
    return $this->model->where('name', 'like', "%$keyword%")->get();
}
```

```php
// Good - one method, flexible filters
public function getProjects(?array $filters = []): LengthAwarePaginator
{
    $query = $this->model->query();
    $query = $this->applyFilters($query, $filters);
    return $this->paginateQuery($query, $filters);
}

// Usage:
$active = $service->getProjects(['status' => 'active']);
$searched = $service->getProjects(['search' => 'keyword']);
```

### Don't Duplicate Filter Logic

```php
// Bad - manual filtering
if (isset($filters['status'])) {
    $query->where('status', $filters['status']);
}
```

```php
// Good - define once, use everywhere
protected function filterableColumns(): array
{
    return ['status', 'type', 'is_active'];
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
    // ...
}
```

```php
// Good - validation in Form Request
class CreateProjectRequest extends FormRequest
{
    public function rules(): array
    {
        return ['name' => 'required|string|max:255'];
    }
}

// Service stays clean
public function createProject(array $data): Project
{
    return $this->create($data);
}
```

## Questions?

Read the code. Seriously.

- **Best reference:** `ClientService` - our cleanest implementation
- **Pagination examples:** `MilestoneService`, `DeliverableService`
- **Custom logic examples:** `CommentService`, `ActivityLogService`
- **Storage abstraction:** `DeliverableService` + `StorageService`

If the pattern isn't clear from reading existing services, open an issue.
