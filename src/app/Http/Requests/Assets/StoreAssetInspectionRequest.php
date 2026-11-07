<?php

namespace App\Http\Requests\Assets;
use App\Http\Requests\Concerns\AuthorizesRequests;

use App\Enums\InspectionResult;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetInspectionRequest extends FormRequest
{
    use AuthorizesRequests;
    public function authorize(): bool
    {
        return $this->authorizePermission('assets.view');
    }

    public function rules(): array
    {
        return [
            'inspection_date' => ['nullable', 'date'],
            'inspector' => ['required', 'exists:users,id'],
            'condition_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'result' => ['required', Rule::enum(InspectionResult::class)],
            'findings' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],
            'next_inspection_date' => ['nullable', 'date', 'after:inspection_date'],
        ];
    }
}
