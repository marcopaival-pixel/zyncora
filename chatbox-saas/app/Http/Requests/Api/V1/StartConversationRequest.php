<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StartConversationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'visitor_token' => ['nullable', 'string', 'max:64'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'client_phone' => ['nullable', 'string', 'max:64'],
            'client_email' => ['nullable', 'email', 'max:255'],
        ];
    }
}
