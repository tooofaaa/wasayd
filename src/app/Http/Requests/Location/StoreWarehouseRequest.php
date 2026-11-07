<?php

namespace App\Http\Requests\Location;

use App\Http\Requests\Concerns\AuthorizesRequests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWarehouseRequest extends FormRequest
{
    use AuthorizesRequests;

    public function authorize(): bool
    {
        return $this->authorizePermission('locations.create');
    }

    public function rules(): array
    {
        $companyId = $this->input('company_id', auth()->user()?->company_id);

        return [
            'company_id' => 'required|exists:companies,id',
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'code' => [
                'required', 'string', 'max:20',
                Rule::unique('warehouses', 'code')->where('company_id', $companyId),
            ],
            'type' => 'required|in:standard,cold_storage,quarantine,hazmat,controlled',
            'total_capacity' => 'nullable|numeric|min:0',
            'temperature_min' => 'nullable|numeric',
            'temperature_max' => 'nullable|numeric',
            'humidity_min' => 'nullable|numeric|min:0|max:100',
            'humidity_max' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ];
    }
}
