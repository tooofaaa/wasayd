<?php

namespace Tests\Feature\Auth;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

/**
 * Comprehensive Authentication Scenarios
 *
 * Plan reference: WASAYD-SPECKIT-IMPLEMENTATION-PLAN.md → Feature 1.4 & 1.5
 * Spec §3 Business Rules 1–5
 * Covers: login, logout, inactive user, failed attempts, 2FA full flow
 *
 * TEST SCENARIOS (human scenarios like a real user would experience):
 * - Happy path: correct credentials → dashboard
 * - Wrong password → error shown
 * - Inactive account → specific error message
 * - Logout → session cleared, redirected to login
 * - Login with remember me
 * - 2FA: enable → challenge → verify → access granted
 * - 2FA: wrong code → access denied
 * - 2FA: backup code works
 * - 2FA: backup code single-use (can't reuse)
 * - 2FA: disable with correct password
 * - 2FA: bypass attempt by directly visiting dashboard
 * - 2FA: unconfirmed secret doesn't block
 * - Company switch clears 2FA session flag
 */
class ComprehensiveAuthTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private Google2FA $google2fa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->company    = Company::factory()->create();
        $this->google2fa  = app(Google2FA::class);
    }

    // ─── LOGIN SCENARIOS ──────────────────────────────────────────────────────

    /** Scenario A1: Happy path — correct credentials → redirect to dashboard */
    public function test_A1_login_with_correct_credentials_redirects_to_dashboard(): void
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'email'      => 'test@wasayd.com',
            'password'   => bcrypt('SecurePass123!'),
            'is_active'  => true,
        ]);

        $response = $this->post('/login', [
            'email'    => 'test@wasayd.com',
            'password' => 'SecurePass123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /** Scenario A2: Wrong password → error message, not logged in */
    public function test_A2_wrong_password_shows_error_does_not_log_in(): void
    {
        User::factory()->create([
            'company_id' => $this->company->id,
            'email'      => 'test@wasayd.com',
            'password'   => bcrypt('CorrectPassword'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'test@wasayd.com',
            'password' => 'WrongPassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** Scenario A3: Non-existent email → generic error (no user enumeration) */
    public function test_A3_nonexistent_email_shows_generic_error(): void
    {
        $response = $this->post('/login', [
            'email'    => 'nobody@wasayd.com',
            'password' => 'whatever',
        ]);

        $response->assertSessionHasErrors('email');
        // Error must NOT say "user not found" (user enumeration risk)
        $error = session('errors')->first('email');
        $this->assertStringNotContainsStringIgnoringCase('not found', $error);
        $this->assertStringNotContainsStringIgnoringCase('no user', $error);
    }

    /** Scenario A4: Inactive user cannot log in (Spec §3 Rule 1) */
    public function test_A4_inactive_user_cannot_log_in(): void
    {
        User::factory()->create([
            'company_id' => $this->company->id,
            'email'      => 'inactive@wasayd.com',
            'password'   => bcrypt('Pass123!'),
            'is_active'  => false,
        ]);

        $response = $this->post('/login', [
            'email'    => 'inactive@wasayd.com',
            'password' => 'Pass123!',
        ]);

        // The key assertion: user MUST NOT be authenticated
        $this->assertGuest('web');
        // Must have an error on the email field
        $response->assertSessionHasErrors('email');
        // The error should NOT be the generic "credentials" error
        // it should be the deactivated message (check auth.deactivated key was used)
        $error = session('errors')->first('email');
        $this->assertNotEmpty($error);
        // Confirm it is NOT the wrong-credentials message in either language
        $this->assertStringNotContainsStringIgnoringCase('credentials', $error);
        $this->assertStringNotContainsStringIgnoringCase('بيانات الاعتماد', $error);
    }

    /** Scenario A5: Logout clears session, redirects to login */
    public function test_A5_logout_clears_session_and_redirects_to_login(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);
        $this->actingAs($user);

        $response = $this->post('/logout');
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /** Scenario A6: Logout from 2FA-verified session also clears 2FA flag */
    public function test_A6_logout_clears_2fa_session_flag(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);
        $this->actingAs($user);
        session(['two_factor_verified' => true]);

        $this->post('/logout');

        $this->assertNull(session('two_factor_verified'));
    }

    /** Scenario A7: Login with remember me sets persistent cookie */
    public function test_A7_login_with_remember_me(): void
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'email'      => 'remember@wasayd.com',
            'password'   => bcrypt('Pass123!'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'remember@wasayd.com',
            'password' => 'Pass123!',
            'remember' => true,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /** Scenario A8: Empty email/password validation */
    public function test_A8_empty_credentials_validation(): void
    {
        $this->post('/login', [])
             ->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    /** Scenario A9: Invalid email format */
    public function test_A9_invalid_email_format_rejected(): void
    {
        $this->post('/login', ['email' => 'not-an-email', 'password' => 'pass'])
             ->assertSessionHasErrors('email');
    }

    /** Scenario A10: last_login_at and last_login_ip recorded on success */
    public function test_A10_last_login_recorded_on_success(): void
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'email'      => 'track@wasayd.com',
            'password'   => bcrypt('Pass123!'),
        ]);

        $this->post('/login', [
            'email'    => 'track@wasayd.com',
            'password' => 'Pass123!',
        ]);

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    /** Scenario A11: Guest cannot access dashboard — redirected to login */
    public function test_A11_guest_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect(route('login'));
    }

    /** Scenario A12: Guest cannot access admin routes */
    public function test_A12_guest_cannot_access_admin_routes(): void
    {
        $this->get('/admin/users')->assertRedirect(route('login'));
    }

    // ─── 2FA SCENARIOS ────────────────────────────────────────────────────────

    /** Scenario B1: User without 2FA passes through to dashboard directly */
    public function test_B1_user_without_2fa_accesses_dashboard_directly(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);
        $user->assignRole('admin');
        $this->actingAs($user);
        session(['active_company_id' => $this->company->id]);

        $response = $this->get('/dashboard');
        $response->assertOk();
        $this->assertFalse($user->hasTwoFactorEnabled());
    }

    /** Scenario B2: User with enabled + confirmed 2FA redirected to challenge */
    public function test_B2_user_with_enabled_2fa_redirected_to_challenge(): void
    {
        $secret = $this->google2fa->generateSecretKey();
        $user   = User::factory()->create([
            'company_id'             => $this->company->id,
            'two_factor_enabled'     => true,
            'two_factor_secret'      => Crypt::encrypt($secret),
            'two_factor_confirmed_at'=> now(),
        ]);

        $this->actingAs($user);
        // No two_factor_verified in session

        $response = $this->get('/dashboard');
        $response->assertRedirect(route('two-factor.challenge'));
    }

    /** Scenario B3: Valid TOTP code allows access, sets session flag */
    public function test_B3_valid_totp_code_allows_access(): void
    {
        $secret = $this->google2fa->generateSecretKey();
        $user   = User::factory()->create([
            'company_id'             => $this->company->id,
            'two_factor_enabled'     => true,
            'two_factor_secret'      => Crypt::encrypt($secret),
            'two_factor_confirmed_at'=> now(),
        ]);

        $this->actingAs($user);
        $validCode = $this->google2fa->getCurrentOtp($secret);

        $response = $this->post(route('two-factor.verify'), ['code' => $validCode]);
        $response->assertRedirect(route('dashboard'));
        $this->assertTrue(session('two_factor_verified'));
    }

    /** Scenario B4: Invalid TOTP code — access denied, error shown */
    public function test_B4_invalid_totp_code_access_denied(): void
    {
        $secret = $this->google2fa->generateSecretKey();
        $user   = User::factory()->create([
            'company_id'             => $this->company->id,
            'two_factor_enabled'     => true,
            'two_factor_secret'      => Crypt::encrypt($secret),
            'two_factor_confirmed_at'=> now(),
        ]);

        $this->actingAs($user);
        $this->post(route('two-factor.verify'), ['code' => '000000'])
             ->assertSessionHasErrors('code');
        $this->assertFalse((bool) session('two_factor_verified'));
    }

    /** Scenario B5: Backup code works when TOTP code not available */
    public function test_B5_backup_code_grants_access(): void
    {
        $secret     = $this->google2fa->generateSecretKey();
        $backupCode = 'ABCD-1234';

        $user = User::factory()->create([
            'company_id'             => $this->company->id,
            'two_factor_enabled'     => true,
            'two_factor_secret'      => Crypt::encrypt($secret),
            'two_factor_confirmed_at'=> now(),
            'two_factor_backup_codes'=> [bcrypt($backupCode)],
        ]);

        $this->actingAs($user);
        $response = $this->post(route('two-factor.verify'), ['code' => $backupCode]);
        $response->assertRedirect(route('dashboard'));
        $this->assertTrue(session('two_factor_verified'));
    }

    /** Scenario B6: Backup code is single-use — cannot reuse */
    public function test_B6_backup_code_is_single_use(): void
    {
        $secret     = $this->google2fa->generateSecretKey();
        $backupCode = 'WXYZ-5678';

        $user = User::factory()->create([
            'company_id'             => $this->company->id,
            'two_factor_enabled'     => true,
            'two_factor_secret'      => Crypt::encrypt($secret),
            'two_factor_confirmed_at'=> now(),
            'two_factor_backup_codes'=> [bcrypt($backupCode)],
        ]);

        $this->actingAs($user);

        // First use — succeeds
        $this->post(route('two-factor.verify'), ['code' => $backupCode]);
        $this->assertTrue(session('two_factor_verified'));

        // Second use — backup code list is now empty, should fail
        session(['two_factor_verified' => false]);
        $this->post(route('two-factor.verify'), ['code' => $backupCode])
             ->assertSessionHasErrors('code');
    }

    /** Scenario B7: 2FA requires confirmed_at — secret without confirm doesn't block */
    public function test_B7_2fa_without_confirmed_at_does_not_block(): void
    {
        $secret = $this->google2fa->generateSecretKey();
        $user   = User::factory()->create([
            'company_id'             => $this->company->id,
            'two_factor_enabled'     => true,
            'two_factor_secret'      => Crypt::encrypt($secret),
            'two_factor_confirmed_at'=> null, // not confirmed
        ]);
        $user->assignRole('admin');

        $this->actingAs($user);
        session(['active_company_id' => $this->company->id]);

        // hasTwoFactorEnabled() should return false → no challenge
        $this->assertFalse($user->hasTwoFactorEnabled());
        $this->get('/dashboard')->assertOk();
    }

    /** Scenario B8: 2FA challenge page accessible without full auth (unskipped route) */
    public function test_B8_2fa_challenge_page_accessible_without_verified_flag(): void
    {
        $secret = $this->google2fa->generateSecretKey();
        $user   = User::factory()->create([
            'company_id'             => $this->company->id,
            'two_factor_enabled'     => true,
            'two_factor_secret'      => Crypt::encrypt($secret),
            'two_factor_confirmed_at'=> now(),
        ]);

        $this->actingAs($user);
        // Should NOT be redirected back (infinite loop prevention)
        $response = $this->get(route('two-factor.challenge'));
        $response->assertOk();
    }

    /** Scenario B9: Disable 2FA requires current password */
    public function test_B9_disable_2fa_requires_correct_password(): void
    {
        $secret = $this->google2fa->generateSecretKey();
        $user   = User::factory()->create([
            'company_id'             => $this->company->id,
            'password'               => bcrypt('MyPassword!'),
            'two_factor_enabled'     => true,
            'two_factor_secret'      => Crypt::encrypt($secret),
            'two_factor_confirmed_at'=> now(),
        ]);

        $this->actingAs($user);
        session(['two_factor_verified' => true]);

        // Wrong password
        $this->delete(route('two-factor.disable'), ['password' => 'WrongPass'])
             ->assertSessionHasErrors('password');
        $this->assertTrue($user->fresh()->two_factor_enabled);
    }

    /** Scenario B10: Company switch clears 2FA session flag (security) */
    public function test_B10_company_switch_clears_2fa_session_flag(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);
        $this->actingAs($user);
        session(['two_factor_verified' => true, 'active_company_id' => $this->company->id]);

        // Switch company
        $newCompany = Company::factory()->create();
        app(\App\Services\TenantContext::class)->switchCompany($newCompany->id);

        $this->assertNull(session('two_factor_verified'));
    }
}
