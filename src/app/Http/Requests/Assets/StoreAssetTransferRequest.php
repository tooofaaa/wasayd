<?php

namespace App\Http\Requests\Assets;
use App\Http\Requests\Concerns\AuthorizesRequests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssetTransferRequest extends FormRequest
{
    use AuthorizesRequests;
    public function authorize(): bool
    {
        return $this->authorizePermission('assets.view');
    }

    public function rules(): array
    {
        return [
            'asset_id' => ['required', 'exists:assets,id'],
            'from_warehouse_id' => ['required', 'exists:warehouses,id'],
            'to_warehouse_id' => ['required', 'exists:warehouses,id', 'different:from_warehouse_id'],
            'from_department' => ['nullable', 'string', 'max:255'],
            'to_department' => ['nullable', 'string', 'max:255'],
            'transfer_date' => ['nullable', 'date'],
            'reason' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
