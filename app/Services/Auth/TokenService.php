<?php

namespace App\Services\Auth;

use App\Models\User;
use Carbon\Carbon;
use Laravel\Passport\PersonalAccessTokenResult;
use Illuminate\Support\Facades\Cache;

class TokenService
{
    /**
     * Generate access and refresh tokens for user
     *
     * @param User $user
     * @return array
     */
    public function generateTokens(User $user): array
    {
        $tokenResult = $user->createToken('auth_token');
        $accessToken = $tokenResult->accessToken;
        
        // Create a refresh token
        $refreshToken = $this->createRefreshToken($user);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => config('passport.tokens_expire_in')
                            ? now()->diffInSeconds(config('passport.tokens_expire_in'))
                            : null,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Create refresh token
     *
     * @param User $user
     * @return string
     */
    protected function createRefreshToken(User $user): string
    {
        $refreshToken = bin2hex(random_bytes(32));
        
        // Store refresh token in cache
        Cache::put(
            "refresh_token:{$refreshToken}",
            [
                'user_id' => $user->id,
                'expires_at' => now()->addDays(30),
            ],
            60 * 24 * 30 // 30 days
        );

        return $refreshToken;
    }

    /**
     * Validate refresh token
     *
     * @param string $refreshToken
     * @return User|null
     */
    public function validateRefreshToken(string $refreshToken): ?User
    {
        $tokenData = Cache::get("refresh_token:{$refreshToken}");

        if (!$tokenData) {
            return null;
        }

        if (now()->greaterThan($tokenData['expires_at'])) {
            Cache::forget("refresh_token:{$refreshToken}");
            return null;
        }

        return User::find($tokenData['user_id']);
    }

    /**
     * Revoke refresh token
     *
     * @param string $refreshToken
     * @return bool
     */
    public function revokeRefreshToken(string $refreshToken): bool
    {
        return Cache::forget("refresh_token:{$refreshToken}");
    }

    /**
     * Revoke all tokens for user
     *
     * @param User $user
     * @return bool
     */
    public function revokeAllTokens(User $user): bool
    {
        // Revoke all access tokens
        $user->tokens()->delete();
        
        // Revoke all refresh tokens (clear cache keys)
        $keys = Cache::get('refresh_tokens:' . $user->id, []);
        foreach ($keys as $key) {
            Cache::forget("refresh_token:{$key}");
        }
        Cache::forget('refresh_tokens:' . $user->id);

        return true;
    }

    /**
     * Check if token is valid
     *
     * @param string $token
     * @return bool
     */
    public function isValidToken(string $token): bool
    {
        // Check if token exists in cache or database
        return Cache::has("access_token:{$token}") ||
               \DB::table('oauth_access_tokens')
                   ->where('id', $token)
                   ->where('revoked', false)
                   ->where('expires_at', '>', now())
                   ->exists();
    }
}