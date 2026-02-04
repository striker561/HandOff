<?php

namespace App\Services;

use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

abstract class BaseCRUDService
{
    abstract protected function getModel(): string;

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->getModel()::query();
        $query = $this->applyFilters($query, $filters);
        return $this->paginateQuery($query, $filters);
    }

    public function create(array $data): Model
    {
        return $this->getModel()::create($data);
    }

    public function update(Model $model, array $data): Model
    {
        $model->update($data);
        return $model->fresh();
    }

    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        // Apply search
        if (!empty($filters['search']) && !empty($this->searchableColumns())) {
            $query->where(function ($q) use ($filters) {
                foreach ($this->searchableColumns() as $column) {
                    $q->orWhere($column, 'LIKE', "%{$filters['search']}%");
                }
            });
        }

        // Apply exact matches (e.g., user_unique_id = 'xyz')
        foreach ($this->filterableColumns() as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                $query->where($column, $filters[$column]);
            }
        }

        // Apply sorting
        $sort = $filters['sort'] ?? $this->defaultSort();
        $direction = $this->sanitizeDirection($filters['direction'] ?? 'desc');

        if (in_array($sort, $this->sortableColumns())) {
            $query->orderBy($sort, $direction);
        }

        return $query;
    }

    protected function sanitizeDirection(?string $direction): string
    {
        return in_array(strtolower($direction), ['asc', 'desc'])
            ? strtolower($direction)
            : 'desc';
    }

    protected function getPerPage(array $filters): int
    {
        $perPage = (int) ($filters['per_page'] ?? 15);
        return min(max($perPage, 1), 100); // Between 1 and 100
    }

    protected function paginateQuery(Builder $query, array $filters = [])
    {
        return $query->paginate($this->getPerPage($filters));
    }

    // Override these in child services
    protected function searchableColumns(): array
    {
        return [];
    }

    protected function sortableColumns(): array
    {
        return ['created_at', 'updated_at'];
    }

    protected function filterableColumns(): array
    {
        return [];
    }

    protected function defaultSort(): string
    {
        return 'created_at';
    }
}