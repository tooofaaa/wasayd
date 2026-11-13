<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Make an Inertia request with the correct version header.
     * Sends the manifest hash so Inertia doesn't return 409 version mismatch.
     * Plan reference: Feature 1.6 — Dashboard tests use assertInertia().
     */
    protected function getInertia(string $url, array $headers = [])
    {
        $version = $this->resolveInertiaVersion();

        return $this->withHeaders(array_merge([
            'X-Inertia'         => 'true',
            'X-Inertia-Version' => $version,
        ], $headers))->get($url);
    }

    private function resolveInertiaVersion(): string
    {
        $manifest = public_path('build/manifest.json');
        if (file_exists($manifest)) {
            return hash_file('xxh128', $manifest);
        }
        return '';
    }
}
