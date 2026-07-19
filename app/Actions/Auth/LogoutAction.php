<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Services\Auth\TokenService;

class LogoutAction
{
    protected TokenService $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function execute(User $user): void
    {
        // Revoke access tokens
        $user->tokens()->delete();
        
        // Revoke refresh tokens
        $this->tokenService->revokeAllTokens($user);
    }
}