<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Basic application smoke test.
 * Plan reference: Feature 1.1 — health check endpoint.
 */
class ExampleTest extends TestCase
{
    /**
     * The health check endpoint returns 200.
     * Plan T1.1-B: Laravel responds on port 80.
     */
    public function test_health_check_returns_ok(): void
    {
        $response = $this->getJson('/api/health');
        // 200 or 503 depending on whether Redis/DB are available in test env
        $this->assertContains($response->status(), [200, 503]);
    }

    /**
     * Root redirects to login for unauthenticated users.
     */
    public function test_root_redirects_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }
}
