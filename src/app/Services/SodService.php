<?php

namespace App\Services;

/**
 * Segregation of Duties (SoD) Service
 * Ensures that the same user cannot both request and approve
 */
class SodService
{
    /**
     * Validate that requester and approver are different users
     */
    public function validateSod(int $requesterId, int $approverId): void
    {
        if ($requesterId === $approverId) {
            throw new \RuntimeException('Segregation of Duties violation: Requester cannot approve their own request');
        }
    }
}
