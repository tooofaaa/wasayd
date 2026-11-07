<?php

namespace App\Http\Requests\Location;

use App\Http\Requests\Concerns\AuthorizesRequests;
use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    use AuthorizesRequests;

    public function authorize(): bool
    {
        return $this->authorizePermission('locations.create');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:companies,code',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'tax_number' => 'nullable|string|max:50',
            'license_number' => 'nullable|string|max:50',
            'country' => 'required|string|size:2',
            'currency' => 'required|string|size:3',
            'timezone' => 'required|string|max:50',
            'locale' => 'required|in:ar,en',
        ];
    }
}
