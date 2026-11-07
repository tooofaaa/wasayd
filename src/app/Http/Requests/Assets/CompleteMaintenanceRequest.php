<?php

namespace App\Http\Requests\Assets;
use App\Http\Requests\Concerns\AuthorizesRequests;

use Illuminate\Foundation\Http\FormRequest;

class CompleteMaintenanceRequest extends FormRequest
{
    use AuthorizesRequests;
    public function authorize(): bool
    {
        return $this->authorizePermission('assets.view');
    }

    public function rules(): array
    {
        return [
            'completed_date' => ['nullable', 'date'],
            'findings' => ['nullable', 'string'],
            'actions_taken' => ['nullable', 'string'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'next_due_date' => ['nullable', 'date', 'after:completed_date'],
        ];
    }
}
