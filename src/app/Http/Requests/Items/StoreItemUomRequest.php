<?php

namespace App\Http\Requests\Items;
use App\Http\Requests\Concerns\AuthorizesRequests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemUomRequest extends FormRequest
{
    use AuthorizesRequests;
    public function authorize(): bool
    {
        return $this->authorizePermission('items.view');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'name_ar' => ['nullable', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', 'unique:item_uoms,code'],
            'symbol' => ['nullable', 'string', 'max:10'],
            'is_base' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }
}
