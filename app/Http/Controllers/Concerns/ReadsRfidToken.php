<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * For the reader server's endpoints: which Sanctum token made the request (stored with what it sent, for tracing).
 */
trait ReadsRfidToken
{
    private function tokenId(Request $request): ?int
    {
        $token = $request->user()?->currentAccessToken();

        return $token instanceof PersonalAccessToken && is_numeric($token->getKey()) ? (int) $token->getKey() : null;
    }
}
