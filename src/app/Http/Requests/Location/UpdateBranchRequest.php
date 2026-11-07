<?php

namespace App\Http\Requests\Location;

use App\Http\Requests\Concerns\AuthorizesRequests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBranchRequest extends FormRequest
{
    use AuthorizesRequests;

    public function authorize(): bool
    {
        return $this->authorizePermission('locations.update');
    }

    public function rules(): array
    {
        $companyId = $this->input('company_id', auth()->user()?->company_id);

        return [
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'code' => [
                'required', 'string', 'max:20',
                Rule::unique('branches', 'code')->where('company_id', $companyId)->ignore($this->route('branch')),
            ],
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'manager_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
