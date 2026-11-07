<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemConfig extends Model
{
    protected $fillable = [
        'company_id', 'config_group', 'config_key', 'config_value', 'description',
    ];

    protected $casts = [
        'config_value' => 'array',
    ];

    public static function getValue(string $group, string $key, mixed $default = null, ?int $companyId = null): mixed
    {
        $query = static::where('config_group', $group)->where('config_key', $key);

        if ($companyId) {
            $config = (clone $query)->where('company_id', $companyId)->first();
            if ($config) {
                return $config->config_value;
            }
        }

        $config = $query->whereNull('company_id')->first();

        return $config ? $config->config_value : $default;
    }

    public static function setValue(string $group, string $key, mixed $value, ?int $companyId = null): static
    {
        return static::updateOrCreate(
            ['config_group' => $group, 'config_key' => $key, 'company_id' => $companyId],
            ['config_value' => $value],
        );
    }
}
