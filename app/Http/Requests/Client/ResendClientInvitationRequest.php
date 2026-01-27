<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class ResendClientInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $client = $this->route('client');
        return $this->user()->can('resendInvitation', $client) ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
