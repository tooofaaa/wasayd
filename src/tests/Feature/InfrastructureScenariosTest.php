<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Infrastructure & API Scenarios
 *
 * Plan reference: WASAYD-SPECKIT-IMPLEMENTATION-PLAN.md → Feature 1.1
 * Covers: health endpoint, API structure, locale files
 */
class InfrastructureScenariosTest extends TestCase
{
    use RefreshDatabase;

    /** Scenario INF1: Health check returns JSON with status */
    public function test_INF1_health_check_returns_json_status(): void
    {
        $response = $this->getJson('/api/health');
        $this->assertContains($response->status(), [200, 503]);
        $response->assertJsonStructure(['status', 'service', 'services']);
    }

    /** Scenario INF2: Health check service field is 'wasayd-api' */
    public function test_INF2_health_check_service_name(): void
    {
        $response = $this->getJson('/api/health');
        $response->assertJsonPath('service', 'wasayd-api');
    }

    /** Scenario INF3: Health check services section lists database and redis */
    public function test_INF3_health_check_has_database_and_redis(): void
    {
        $response = $this->getJson('/api/health');
        $response->assertJsonStructure(['services' => ['database', 'redis']]);
    }

    /** Scenario INF4: Root URL redirects to login for guest */
    public function test_INF4_root_redirects_guest_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    /** Scenario INF5: Root URL redirects to dashboard for authenticated user */
    public function test_INF5_root_redirects_authenticated_to_dashboard(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $company = \App\Models\Company::factory()->create();
        $user    = \App\Models\User::factory()->create(['company_id' => $company->id]);
        $user->assignRole('admin');

        $this->actingAs($user);
        session(['active_company_id' => $company->id, 'two_factor_verified' => true]);

        $this->get('/')->assertRedirect(route('dashboard'));
    }

    /** Scenario INF6: English locale files exist and are valid PHP arrays */
    public function test_INF6_english_locale_files_are_valid(): void
    {
        $files = ['inventory', 'rfid', 'approvals', 'auth'];
        foreach ($files as $file) {
            $path = base_path("lang/en/$file.php");
            $this->assertFileExists($path, "lang/en/$file.php should exist");

            $data = require $path;
            $this->assertIsArray($data, "lang/en/$file.php should return an array");
            $this->assertNotEmpty($data, "lang/en/$file.php should not be empty");
        }
    }

    /** Scenario INF7: Arabic locale files exist and have matching keys */
    public function test_INF7_arabic_locale_files_match_english(): void
    {
        $files = ['inventory', 'rfid', 'approvals', 'auth'];
        foreach ($files as $file) {
            $enPath = base_path("lang/en/$file.php");
            $arPath = base_path("lang/ar/$file.php");

            $this->assertFileExists($arPath, "lang/ar/$file.php should exist");

            $en = require $enPath;
            $ar = require $arPath;

            // All English keys should have Arabic translations
            foreach (array_keys($en) as $key) {
                $this->assertArrayHasKey($key, $ar,
                    "Arabic translation missing for key '$key' in $file.php");
            }
        }
    }

    /** Scenario INF8: API v1 routes require auth */
    public function test_INF8_api_v1_routes_require_auth(): void
    {
        $this->getJson('/api/v1/items')->assertStatus(401);
        $this->getJson('/api/v1/stock')->assertStatus(401);
        $this->getJson('/api/v1/transactions')->assertStatus(401);
    }

    /** Scenario INF9: rfid-bridge server.js has the health endpoint defined */
    public function test_INF9_rfid_bridge_server_has_health_endpoint(): void
    {
        $serverFile = base_path('../rfid-bridge/src/server.js');
        $this->assertFileExists($serverFile, 'rfid-bridge/src/server.js should exist');

        $content = file_get_contents($serverFile);
        $this->assertStringContainsString('/health', $content);
        $this->assertStringContainsString('rfid-bridge', $content);
        $this->assertStringContainsString('Redis', $content);
    }

    /** Scenario INF10: mobile/package.json exists with Expo dependency */
    public function test_INF10_mobile_package_json_has_expo(): void
    {
        $packageFile = base_path('../mobile/package.json');
        $this->assertFileExists($packageFile, 'mobile/package.json should exist');

        $package = json_decode(file_get_contents($packageFile), true);
        $this->assertArrayHasKey('expo', $package['dependencies'] ?? []);
        $this->assertStringContainsString('wasayd', $package['name'] ?? '');
    }

    /** Scenario INF11: docker-compose.yml has all required services */
    public function test_INF11_docker_compose_has_required_services(): void
    {
        $composePath = base_path('../docker-compose.yml');
        $this->assertFileExists($composePath);

        $content = file_get_contents($composePath);
        $requiredServices = ['postgres', 'redis', 'rfid-bridge', 'soketi', 'minio', 'gotenberg'];
        foreach ($requiredServices as $service) {
            $this->assertStringContainsString("$service:", $content,
                "docker-compose.yml should have '$service' service");
        }
    }

    /** Scenario INF12: Spec Kit constitution exists with 10 principles */
    public function test_INF12_spec_kit_constitution_exists(): void
    {
        $constitutionPath = base_path('../.specify/constitution.md');
        $this->assertFileExists($constitutionPath, '.specify/constitution.md should exist');

        $content = file_get_contents($constitutionPath);
        $this->assertStringContainsString('IMMUTABILITY-FIRST', $content);
        $this->assertStringContainsString('DUAL-TRUST', $content);
        $this->assertStringContainsString('TENANT-ISOLATION', $content);
        $this->assertStringContainsString('GS1-COMPLIANCE', $content);
        $this->assertStringContainsString('FEFO-DEFAULT', $content);
        $this->assertStringContainsString('ARABIC-FIRST', $content);
        $this->assertStringContainsString('FAIL-LOUD', $content);
        $this->assertStringContainsString('ZERO-DRIFT', $content);
        $this->assertStringContainsString('HEALTHCARE-GRADE', $content);
    }
}
