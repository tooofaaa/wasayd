<?php

namespace App\Http\Requests\Assets;
use App\Http\Requests\Concerns\AuthorizesRequests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssetRequest extends FormRequest
{
    use AuthorizesRequests;
    public function authorize(): bool
    {
        return $this->authorizePermission('assets.view');
    }

    public function rules(): array
    {
        return [
            'item_id' => ['required', 'exists:items,id'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'epc_id' => ['nullable', 'exists:epcs,id'],
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'current_warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'current_department' => ['nullable', 'string', 'max:255'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'warranty_start' => ['nullable', 'date'],
            'warranty_end' => ['nullable', 'date', 'after_or_equal:warranty_start'],
            'expected_life_years' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
