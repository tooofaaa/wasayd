<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesRequests;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuarantineRequest extends FormRequest
{
    use AuthorizesRequests;

    public function authorize(): bool
    {
        return $this->authorizePermission('operations.create');
    }

    public function rules(): array
    {
        return [
            'item_id' => ['required', 'integer', 'exists:items,id'],
            'batch_id' => ['nullable', 'integer', 'exists:batches,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'bin_id' => ['nullable', 'integer', 'exists:bins,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason_code' => ['required', 'string', 'max:50'],
            'reason_description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
