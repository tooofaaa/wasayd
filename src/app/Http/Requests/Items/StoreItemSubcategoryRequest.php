<?php

namespace App\Http\Requests\Items;
use App\Http\Requests\Concerns\AuthorizesRequests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemSubcategoryRequest extends FormRequest
{
    use AuthorizesRequests;
    public function authorize(): bool
    {
        return $this->authorizePermission('items.view');
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:item_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:item_subcategories,code'],
            'is_active' => ['boolean'],
        ];
    }
}
