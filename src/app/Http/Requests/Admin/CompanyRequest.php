<?php

namespace App\Http\Requests\Admin;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole('super-admin');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $companyId = $this->route('company');

        return [
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('companies', 'code')->ignore($companyId),
            ],
            'email' => [
                'nullable',
                'email',
                Rule::unique('companies', 'email')->ignore($companyId),
            ],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'tax_number' => 'nullable|string|max:50',
            'license_number' => 'nullable|string|max:50',
            'country' => 'required|string|size:2',
            'currency' => 'required|string|size:3',
            'timezone' => 'required|string|max:50',
            'locale' => 'required|in:ar,en',
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Company name is required.',
            'code.required' => 'Company code is required.',
            'code.unique' => 'Company code already exists.',
            'email.unique' => 'Email already exists.',
            'country.required' => 'Country code is required.',
            'currency.required' => 'Currency code is required.',
            'timezone.required' => 'Timezone is required.',
            'locale.required' => 'Locale is required.',
        ];
    }
}
