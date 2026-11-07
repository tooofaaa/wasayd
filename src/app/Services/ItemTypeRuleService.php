<?php

namespace App\Services;

use App\Enums\ItemType;

class ItemTypeRuleService
{
    /**
     * Evaluate and apply type-specific rules to item data
     */
    public function evaluateRules(string $itemType, array $data): array
    {
        $typeEnum = ItemType::from($itemType);
        $defaults = $this->getDefaultRulesForType($itemType);

        // Apply forced values based on item type
        foreach ($defaults as $key => $value) {
            if ($value !== null) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * Get default/forced rules for a specific item type
     */
    public function getDefaultRulesForType(string $itemType): array
    {
        $type = ItemType::from($itemType);

        return match ($type) {
            ItemType::Medicine => [
                'requires_batch' => true,
                'requires_expiry' => true,
                'requires_serial' => false,
                'requires_rfid' => null, // Optional
            ],
            ItemType::MedicalDevice => [
                'requires_batch' => null, // Optional
                'requires_expiry' => null, // Optional
                'requires_serial' => true,
                'requires_rfid' => null, // Optional
            ],
            ItemType::MedicalEquipment => [
                'requires_batch' => null, // Optional
                'requires_expiry' => null, // Optional
                'requires_serial' => true,
                'requires_rfid' => null, // Optional
            ],
            ItemType::NarcoticControlled => [
                'requires_batch' => true,
                'requires_expiry' => true,
                'requires_serial' => true,
                'requires_rfid' => true,
                'is_controlled' => true,
            ],
            ItemType::MedicalConsumable => [
                'requires_batch' => null, // Optional
                'requires_expiry' => null, // Optional
                'requires_serial' => null, // Optional
                'requires_rfid' => null, // Optional
            ],
        };
    }

    /**
     * Get required fields for a specific item type
     */
    public function getRequiredFieldsForType(string $itemType): array
    {
        $type = ItemType::from($itemType);

        return match ($type) {
            ItemType::Medicine => [
                'manufacturer',
            ],
            ItemType::NarcoticControlled => [
                'manufacturer',
            ],
            ItemType::MedicalDevice, ItemType::MedicalEquipment => [
                'manufacturer',
                'model_number',
            ],
            default => [],
        };
    }

    /**
     * Check if field is required for given item type
     */
    public function isFieldRequired(string $itemType, string $field): bool
    {
        return in_array($field, $this->getRequiredFieldsForType($itemType));
    }
}
