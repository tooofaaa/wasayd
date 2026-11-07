<?php

namespace App\Http\Requests;

use App\Enums\RecallClass;
use App\Http\Requests\Concerns\AuthorizesRequests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRecallRequest extends FormRequest
{
    use AuthorizesRequests;

    public function authorize(): bool
    {
        return $this->authorizePermission('operations.create');
    }

    public function rules(): array
    {
        return [
            'item_id' => ['required', 'integer', 'exists:items,id'],
            'recall_class' => ['required', Rule::enum(RecallClass::class)],
            'manufacturer_recall_id' => ['nullable', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:1000'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'affected_batches' => ['nullable', 'array'],
            'affected_batches.*' => ['integer', 'exists:batches,id'],
        ];
    }
}
