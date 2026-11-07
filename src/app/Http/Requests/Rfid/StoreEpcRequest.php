<?php

namespace App\Http\Requests\Rfid;
use App\Http\Requests\Concerns\AuthorizesRequests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEpcRequest extends FormRequest
{
    use AuthorizesRequests;
    public function authorize(): bool
    {
        return $this->authorizePermission('rfid.view');
    }

    public function rules(): array
    {
        return [
            'item_id' => ['required', 'exists:items,id'],
            'batch_id' => ['nullable', 'exists:batches,id'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'epc' => ['required', 'string', 'max:255', 'unique:epcs,epc'],
            'tid' => ['nullable', 'string', 'max:255'],
            'sgtin' => ['nullable', 'string', 'max:255'],
        ];
    }
}
