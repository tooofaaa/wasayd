<?php

namespace Tests\Feature\Auth;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

/**
 * Feature 1.5 — MFA + 2FA (TOTP)
 *
 * Plan reference: WASAYD-SPECKIT-IMPLEMENTATION-PLAN.md → Feature 1.5
 * Lean Loop Test Gate 1.5: Tests T1.5-A through T1.5-C
 */
class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $user;
    private Google2FA $google2fa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->user    = User::factory()->create(['company_id' => $this->company->id]);
        $this->google2fa = app(Google2FA::class);
        session(['active_company_id' => $this->company->id]);
    }

    /**
     * T1.5-A: 2FA required after enabling.
     * Plan: User with hasTwoFactorEnabled() = true and no session flag
     * gets redirected to two-factor challenge.
     */
    public function test_T1_5_A_2fa_required_after_enabling(): void
    {
        $secret = $this->google2fa->generateSecretKey();

        $this->user->update([
            'two_factor_enabled'      => true,
            'two_factor_secret'       => Crypt::encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->actingAs($this->user);

        // Session does NOT have two_factor_verified — should redirect to challenge
        $response = $this->get('/dashboard');
        $response->assertRedirect(route('two-factor.challenge'));
    }

    /**
     * T1.5-B: Valid TOTP code grants access.
     * Plan T1.5-B: Valid code sets two_factor_verified in session.
     */
    public function test_T1_5_B_valid_totp_code_grants_access(): void
    {
        $secret = $this->google2fa->generateSecretKey();

        $this->user->update([
            'two_factor_enabled'      => true,
            'two_factor_secret'       => Crypt::encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->actingAs($this->user);

        $validCode = $this->google2fa->getCurrentOtp($secret);

        $response = $this->post(route('two-factor.verify'), ['code' => $validCode]);

        $response->assertRedirect(route('dashboard'));
        $this->assertTrue(session('two_factor_verified'));
    }

    /**
     * T1.5-C: Invalid TOTP code is rejected.
     * Plan T1.5-C: Wrong code → error, session flag not set.
     */
    public function test_T1_5_C_invalid_totp_code_rejected(): void
    {
        $secret = $this->google2fa->generateSecretKey();

        $this->user->update([
            'two_factor_enabled'      => true,
            'two_factor_secret'       => Crypt::encrypt($secret),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('two-factor.verify'), ['code' => '000000']);

        $response->assertSessionHasErrors('code');
        $this->assertFalse((bool) session('two_factor_verified'));
    }

    /**
     * T1.5-D: User without 2FA enabled passes through without challenge.
     */
    public function test_T1_5_D_user_without_2fa_passes_through(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->user->assignRole('viewer');
        $this->actingAs($this->user);

        // No 2FA enabled — should reach dashboard directly
        $response = $this->get('/dashboard');
        $response->assertOk(); // or assertInertia — no redirect to 2FA
    }

    /**
     * T1.5-E: hasTwoFactorEnabled() returns false before confirmation.
     */
    public function test_T1_5_E_has_two_factor_enabled_requires_confirmation(): void
    {
        $secret = $this->google2fa->generateSecretKey();

        // Set secret but NOT confirmed_at
        $this->user->update([
            'two_factor_enabled' => true,
            'two_factor_secret'  => Crypt::encrypt($secret),
            // two_factor_confirmed_at is null
        ]);

        $this->assertFalse($this->user->hasTwoFactorEnabled());

        // Confirm it
        $this->user->update(['two_factor_confirmed_at' => now()]);
        $this->assertTrue($this->user->fresh()->hasTwoFactorEnabled());
    }
}
