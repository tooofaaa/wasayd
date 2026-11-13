<?php

namespace App\Models;

use App\Models\Concerns\HasAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles, HasAuditTrail;

    protected $fillable = [
        'company_id', 'branch_id', 'name', 'name_ar', 'email', 'password',
        'phone', 'avatar', 'locale', 'theme', 'is_active',
        'last_login_at', 'last_login_ip',
        // Plan Feature 1.5 — TOTP 2FA
        'two_factor_enabled', 'two_factor_secret',
        'two_factor_backup_codes', 'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret', // never expose the TOTP secret
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'        => 'datetime',
            'password'                 => 'hashed',
            'is_active'                => 'boolean',
            'last_login_at'            => 'datetime',
            'two_factor_enabled'       => 'boolean',
            'two_factor_backup_codes'  => 'json',
            'two_factor_confirmed_at'  => 'datetime',
        ];
    }

    /**
     * Whether the user has completed 2FA setup (secret confirmed).
     * Plan Feature 1.5: T1.5-A — 2FA required after enabling.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_enabled && $this->two_factor_confirmed_at !== null;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
