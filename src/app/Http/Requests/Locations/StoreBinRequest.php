<?php

namespace App\Http\Requests\Locations;
use App\Http\Requests\Concerns\AuthorizesRequests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBinRequest extends FormRequest
{
    use AuthorizesRequests;
    public function authorize(): bool
    {
        return $this->authorizePermission('locations.view');
    }

    public function rules(): array
    {
        return [
            'shelf_id' => ['required', 'exists:shelves,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:bins,code'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:bins,barcode'],
            'max_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'max_volume_m3' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
