<?php

namespace App\Http\Requests\Items;
use App\Http\Requests\Concerns\AuthorizesRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemSubcategoryRequest extends FormRequest
{
    use AuthorizesRequests;
    public function authorize(): bool
    {
        return $this->authorizePermission('items.view');
    }

    public function rules(): array
    {
        $subcategoryId = $this->route('subcategory')->id;

        return [
            'category_id' => ['required', 'exists:item_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('item_subcategories', 'code')->ignore($subcategoryId)],
            'is_active' => ['boolean'],
        ];
    }
}
