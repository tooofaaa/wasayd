<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesRequests;
use Illuminate\Foundation\Http\FormRequest;

class DispenseControlledRequest extends FormRequest
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
            'quantity' => ['required', 'integer', 'min:1'],
            'dispensed_by' => ['required', 'integer', 'exists:users,id'],
            'witnessed_by' => ['required', 'integer', 'exists:users,id', 'different:dispensed_by'],
            'patient_id' => ['nullable', 'string', 'max:100'],
            'prescription_number' => ['nullable', 'string', 'max:100'],
            'physician_name' => ['nullable', 'string', 'max:200'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'witnessed_by.different' => 'The witness must be a different user than the dispenser (dual authorization required).',
        ];
    }
}
