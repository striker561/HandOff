<?php

namespace App\Http\Requests\Client;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{

    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc,dns', 'max:190', 'unique:users,email'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'A user with this email already exists.',
        ];
    }
}