<?php

namespace App\Http\Requests\Locations;
use App\Http\Requests\Concerns\AuthorizesRequests;

use App\Enums\WarehouseType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWarehouseRequest extends FormRequest
{
    use AuthorizesRequests;
    public function authorize(): bool
    {
        return $this->authorizePermission('locations.view');
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:warehouses,code'],
            'type' => ['required', Rule::enum(WarehouseType::class)],
            'address' => ['nullable', 'string', 'max:500'],
            'total_capacity' => ['nullable', 'numeric', 'min:0'],
            'temperature_min' => ['nullable', 'numeric'],
            'temperature_max' => ['nullable', 'numeric', 'gte:temperature_min'],
            'humidity_min' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'humidity_max' => ['nullable', 'numeric', 'min:0', 'max:100', 'gte:humidity_min'],
            'is_active' => ['boolean'],
        ];
    }
}
