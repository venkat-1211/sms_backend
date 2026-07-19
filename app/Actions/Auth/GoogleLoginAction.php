<?php

namespace App\Actions\Auth;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleLoginAction
{
    protected UserRepositoryInterface $repository;

    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(string $token): array
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->userFromToken($token);

            if (!$googleUser) {
                return [
                    'success' => false,
                    'message' => 'Invalid Google token',
                ];
            }

            $user = $this->findOrCreateGoogleUser($googleUser);

            return [
                'success' => true,
                'user' => $user,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function findOrCreateGoogleUser($googleUser): User
    {
        $email = $googleUser->getEmail();
        $googleId = $googleUser->getId();

        // First, try to find user by google_id
        $user = $this->repository->findByGoogleId($googleId);

        if (!$user) {
            // Then try by email
            $user = $this->repository->findByEmail($email);
        }

        if (!$user) {
            // Create new user
            $user = DB::transaction(function () use ($googleUser) {
                $userData = [
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'provider' => 'google',
                    'provider_id' => $googleUser->getId(),
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(32)),
                    'status' => 1,
                ];

                return $this->repository->create($userData);
            });
        } else {
            // Update existing user with google data if not already linked
            if (!$user->google_id) {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar() ?? $user->avatar,
                    'provider' => 'google',
                    'provider_id' => $googleUser->getId(),
                ]);
            }
        }

        return $user;
    }
}