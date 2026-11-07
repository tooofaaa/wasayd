<?php

namespace App\Http\Requests\Items;

use App\Enums\ItemType;
use App\Http\Requests\Concerns\AuthorizesRequests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemRequest extends FormRequest
{
    use AuthorizesRequests;

    public function authorize(): bool
    {
        return $this->authorizePermission('items.update');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'reorder_point' => $this->input('reorder_point') ?? 0,
            'reorder_qty'   => $this->input('reorder_qty') ?? 0,
            'min_stock'     => $this->input('min_stock') ?? 0,
            'max_stock'     => $this->input('max_stock') ?? 0,
        ]);
    }

    public function rules(): array
    {
        $itemId = $this->route('item')->id;

        $rules = [
            'sku' => ['required', 'string', 'max:50', Rule::unique('items', 'sku')->where('company_id', auth()->user()->company_id)->ignore($itemId)],
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'item_type' => ['required', Rule::enum(ItemType::class)],
            'category_id' => ['required', 'exists:item_categories,id'],
            'subcategory_id' => ['nullable', 'exists:item_subcategories,id'],
            'base_uom_id' => ['required', 'exists:item_uoms,id'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model_number' => ['nullable', 'string', 'max:255'],
            'requires_batch' => ['boolean'],
            'requires_expiry' => ['boolean'],
            'requires_serial' => ['boolean'],
            'requires_rfid' => ['boolean'],
            'is_controlled' => ['boolean'],
            'is_hazardous' => ['boolean'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'max_stock' => ['nullable', 'numeric', 'min:0', 'gte:min_stock'],
            'reorder_point' => ['nullable', 'numeric', 'min:0'],
            'reorder_qty' => ['nullable', 'numeric', 'min:0'],
            'lead_time_days' => ['nullable', 'integer', 'min:0'],
            'storage_conditions' => ['nullable', 'string', 'max:500'],
            'weight_kg' => ['nullable', 'numeric', 'min:0'],
            'length_cm' => ['nullable', 'numeric', 'min:0'],
            'width_cm' => ['nullable', 'numeric', 'min:0'],
            'height_cm' => ['nullable', 'numeric', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'image_url' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ];

        // Add type-specific validation rules
        $itemType = $this->input('item_type');
        if ($itemType) {
            $rules = array_merge($rules, $this->getTypeSpecificRules($itemType));
        }

        return $rules;
    }

    protected function getTypeSpecificRules(string $itemType): array
    {
        $type = ItemType::tryFrom($itemType);
        if (!$type) {
            return [];
        }

        return match ($type) {
            ItemType::Medicine => [
                'manufacturer' => ['required', 'string', 'max:255'],
                'requires_batch' => ['required', 'accepted'],
                'requires_expiry' => ['required', 'accepted'],
            ],
            ItemType::MedicalDevice => [
                'manufacturer' => ['required', 'string', 'max:255'],
                'model_number' => ['required', 'string', 'max:255'],
                'requires_serial' => ['required', 'accepted'],
            ],
            ItemType::MedicalEquipment => [
                'manufacturer' => ['required', 'string', 'max:255'],
                'model_number' => ['required', 'string', 'max:255'],
                'requires_serial' => ['required', 'accepted'],
            ],
            ItemType::NarcoticControlled => [
                'manufacturer' => ['required', 'string', 'max:255'],
                'requires_batch' => ['required', 'accepted'],
                'requires_expiry' => ['required', 'accepted'],
                'requires_serial' => ['required', 'accepted'],
                'requires_rfid' => ['required', 'accepted'],
                'is_controlled' => ['required', 'accepted'],
            ],
            ItemType::MedicalConsumable => [],
        };
    }

    public function messages(): array
    {
        return [
            'requires_batch.accepted' => 'Batch control is required for this item type.',
            'requires_expiry.accepted' => 'Expiry control is required for this item type.',
            'requires_serial.accepted' => 'Serial control is required for this item type.',
            'requires_rfid.accepted' => 'RFID tracking is required for narcotic/controlled items.',
            'is_controlled.accepted' => 'Controlled status is required for narcotic/controlled items.',
            'manufacturer.required' => 'Manufacturer is required for this item type.',
            'model_number.required' => 'Model number is required for medical devices and equipment.',
        ];
    }
}
