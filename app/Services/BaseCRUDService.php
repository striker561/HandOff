<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

abstract class BaseCRUDService
{
    abstract protected function getModel(): string;

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
}