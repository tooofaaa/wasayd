<?php

namespace App\Http\Requests\Locations;
use App\Http\Requests\Concerns\AuthorizesRequests;

use Illuminate\Foundation\Http\FormRequest;

class StoreZoneRequest extends FormRequest
{
    use AuthorizesRequests;
    public function authorize(): bool
    {
        return $this->authorizePermission('locations.view');
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:zones,code'],
            'type' => ['nullable', 'string', 'max:50'],
            'temperature_controlled' => ['boolean'],
            'temperature_min' => ['nullable', 'numeric'],
            'temperature_max' => ['nullable', 'numeric', 'gte:temperature_min'],
            'is_active' => ['boolean'],
        ];
    }
}
