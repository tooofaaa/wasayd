<?php

namespace App\Http\Requests\Locations;
use App\Http\Requests\Concerns\AuthorizesRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRackRequest extends FormRequest
{
    use AuthorizesRequests;
    public function authorize(): bool
    {
        return $this->authorizePermission('locations.view');
    }

    public function rules(): array
    {
        return [
            'aisle_id' => ['required', 'exists:aisles,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('racks')->ignore($this->rack)],
            'levels' => ['nullable', 'integer', 'min:1'],
            'max_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
