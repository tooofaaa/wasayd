<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Concerns\AuthorizesRequests;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    use AuthorizesRequests;

    public function authorize(): bool
    {
        return $this->authorizePermission('auth.login');
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required'],
            'remember' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('auth.email_required'),
            'email.email' => __('auth.email_invalid'),
            'password.required' => __('auth.password_required'),
        ];
    }
}
