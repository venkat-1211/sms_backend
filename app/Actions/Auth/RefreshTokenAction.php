<?php

namespace App\Actions\Auth;

use App\Services\Auth\TokenService;

class RefreshTokenAction
{
    protected TokenService $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function execute(string $refreshToken): array
    {
        $user = $this->tokenService->validateRefreshToken($refreshToken);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid or expired refresh token',
            ];
        }

        // Revoke old refresh token
        $this->tokenService->revokeRefreshToken($refreshToken);

        // Generate new tokens
        $tokens = $this->tokenService->generateTokens($user);

        return array_merge(['success' => true], $tokens);
    }
}