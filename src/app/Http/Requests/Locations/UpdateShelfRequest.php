<?php

namespace App\Http\Requests\Locations;
use App\Http\Requests\Concerns\AuthorizesRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShelfRequest extends FormRequest
{
    use AuthorizesRequests;
    public function authorize(): bool
    {
        return $this->authorizePermission('locations.view');
    }

    public function rules(): array
    {
        return [
            'rack_id' => ['required', 'exists:racks,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('shelves')->ignore($this->shelf)],
            'level' => ['nullable', 'integer', 'min:1'],
            'max_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
