<?php

namespace App\Http\Requests\Rfid;
use App\Http\Requests\Concerns\AuthorizesRequests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateEpcBatchRequest extends FormRequest
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
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'tag_type' => ['required', 'string', 'in:SGTIN-96,SGTIN-198'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.max' => 'Maximum batch size is 1000 EPCs',
        ];
    }
}
