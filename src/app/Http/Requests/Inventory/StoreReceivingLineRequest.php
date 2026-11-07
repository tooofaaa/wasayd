<?php

namespace App\Http\Requests\Inventory;
use App\Http\Requests\Concerns\AuthorizesRequests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReceivingLineRequest extends FormRequest
{
    use AuthorizesRequests;
    public function authorize(): bool
    {
        return $this->authorizePermission('inventory.view');
    }

    public function rules(): array
    {
        return [
            'item_id' => ['required', 'exists:items,id'],
            'expected_quantity' => ['required', 'numeric', 'min:0.001'],
            'uom_id' => ['nullable', 'exists:item_uoms,id'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'item_id.required' => 'Please select an item.',
            'item_id.exists' => 'The selected item is invalid.',
            'expected_quantity.required' => 'Expected quantity is required.',
            'expected_quantity.min' => 'Expected quantity must be greater than 0.',
        ];
    }
}
