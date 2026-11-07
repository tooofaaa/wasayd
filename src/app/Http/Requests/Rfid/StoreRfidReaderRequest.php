<?php

namespace App\Http\Requests\Rfid;
use App\Http\Requests\Concerns\AuthorizesRequests;

use App\Enums\RfidReaderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRfidReaderRequest extends FormRequest
{
    use AuthorizesRequests;
    public function authorize(): bool
    {
        return $this->authorizePermission('rfid.view');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'reader_id' => ['required', 'string', 'max:255', 'unique:rfid_readers,reader_id'],
            'model' => ['nullable', 'string', 'max:255'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'ip_address' => ['required', 'ip'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'zone_id' => ['nullable', 'exists:zones,id'],
            'status' => ['required', Rule::enum(RfidReaderStatus::class)],
            'firmware_version' => ['nullable', 'string', 'max:255'],
            'configuration' => ['nullable', 'array'],
        ];
    }
}
