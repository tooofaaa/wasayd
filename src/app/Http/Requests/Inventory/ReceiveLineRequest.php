<?php

namespace App\Http\Requests\Inventory;
use App\Http\Requests\Concerns\AuthorizesRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReceiveLineRequest extends FormRequest
{
    use AuthorizesRequests;
    public function authorize(): bool
    {
        return $this->authorizePermission('inventory.view');
    }

    public function rules(): array
    {
        $receivingLine = $this->route('line');
        $item = $receivingLine->item;

        return [
            'received_quantity' => ['required', 'numeric', 'min:0.001'],
            'batch_number' => [
                Rule::requiredIf($item->requires_batch),
                'nullable',
                'string',
                'max:255',
            ],
            'expiry_date' => [
                Rule::requiredIf($item->requires_expiry),
                'nullable',
                'date',
                'after:today',
            ],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'rejection_reason' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'received_quantity.required' => 'Received quantity is required.',
            'received_quantity.min' => 'Received quantity must be greater than 0.',
            'batch_number.required' => 'Batch number is required for batch-controlled items.',
            'expiry_date.required' => 'Expiry date is required for expiry-controlled items.',
            'expiry_date.after' => 'Expiry date must be in the future.',
        ];
    }
}
