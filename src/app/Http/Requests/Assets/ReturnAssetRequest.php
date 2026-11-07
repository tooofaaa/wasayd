<?php

namespace App\Http\Requests\Assets;
use App\Http\Requests\Concerns\AuthorizesRequests;

use App\Enums\AssetCondition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReturnAssetRequest extends FormRequest
{
    use AuthorizesRequests;
    public function authorize(): bool
    {
        return $this->authorizePermission('assets.view');
    }

    public function rules(): array
    {
        return [
            'condition_at_return' => ['required', Rule::enum(AssetCondition::class)],
            'returned_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
