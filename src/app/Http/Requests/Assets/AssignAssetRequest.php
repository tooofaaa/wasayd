<?php

namespace App\Http\Requests\Assets;
use App\Http\Requests\Concerns\AuthorizesRequests;

use App\Enums\AssignmentType;
use App\Enums\AssetCondition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignAssetRequest extends FormRequest
{
    use AuthorizesRequests;
    public function authorize(): bool
    {
        return $this->authorizePermission('assets.view');
    }

    public function rules(): array
    {
        return [
            'assignment_type' => ['required', Rule::enum(AssignmentType::class)],
            'assigned_to_user_id' => ['required_if:assignment_type,user', 'nullable', 'exists:users,id'],
            'assigned_to_department' => ['required_if:assignment_type,department', 'nullable', 'string', 'max:255'],
            'condition_at_assignment' => ['required', Rule::enum(AssetCondition::class)],
            'expected_return_at' => ['nullable', 'date', 'after:now'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
