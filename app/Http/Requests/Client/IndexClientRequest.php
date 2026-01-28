<?php

namespace App\Http\Requests\Client;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class IndexClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('view', User::class);
    }

    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'string', 'max:255'],
            'sort' => ['sometimes', 'nullable', 'in:name,email,created_at,updated_at'],
            'direction' => ['sometimes', 'string', 'in:asc,desc'],
            'per_page' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'nullable', 'integer', 'min:1'],
        ];
    }

    public function filters(): array
    {
        return $this->only(['search', 'sort', 'direction', 'per_page', 'page']);
    }
}
