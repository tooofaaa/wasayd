<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesRequests;
use Illuminate\Foundation\Http\FormRequest;

class ReconcileControlledRequest extends FormRequest
{
    use AuthorizesRequests;

    public function authorize(): bool
    {
        return $this->authorizePermission('operations.update');
    }

    public function rules(): array
    {
        return [
            'item_id' => ['required', 'integer', 'exists:items,id'],
            'batch_id' => ['nullable', 'integer', 'exists:batches,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'expected_balance' => ['required', 'integer', 'min:0'],
            'actual_balance' => ['required', 'integer', 'min:0'],
            'dispensed_by' => ['required', 'integer', 'exists:users,id'],
            'witnessed_by' => ['required', 'integer', 'exists:users,id', 'different:dispensed_by'],
            'reason' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'witnessed_by.different' => 'The witness must be a different user than the person performing the reconciliation (dual authorization required).',
        ];
    }
}
