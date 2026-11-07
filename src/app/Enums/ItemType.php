<?php

namespace App\Enums;

enum ItemType: string
{
    case MedicalConsumable = 'medical_consumable';
    case Medicine = 'medicine';
    case MedicalDevice = 'medical_device';
    case MedicalEquipment = 'medical_equipment';
    case NarcoticControlled = 'narcotic_controlled';

    public function label(): string
    {
        return match ($this) {
            self::MedicalConsumable => 'Medical Consumable',
            self::Medicine => 'Medicine',
            self::MedicalDevice => 'Medical Device',
            self::MedicalEquipment => 'Medical Equipment',
            self::NarcoticControlled => 'Narcotic / Controlled',
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::MedicalConsumable => 'مستهلكات طبية',
            self::Medicine => 'دواء',
            self::MedicalDevice => 'جهاز طبي',
            self::MedicalEquipment => 'معدات طبية',
            self::NarcoticControlled => 'مواد مخدرة / مراقبة',
        };
    }

    public function requiresBatchControl(): bool
    {
        return match ($this) {
            self::Medicine, self::NarcoticControlled => true,
            default => false,
        };
    }

    public function requiresExpiryControl(): bool
    {
        return match ($this) {
            self::Medicine, self::NarcoticControlled => true,
            default => false,
        };
    }

    public function requiresSerialControl(): bool
    {
        return match ($this) {
            self::MedicalDevice, self::MedicalEquipment, self::NarcoticControlled => true,
            default => false,
        };
    }

    public function requiresDualAuth(): bool
    {
        return $this === self::NarcoticControlled;
    }

    public function requiresFefo(): bool
    {
        return match ($this) {
            self::Medicine, self::NarcoticControlled => true,
            default => false,
        };
    }
}
