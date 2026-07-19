<?php

namespace App\Actions\Auth;

use App\DTO\LoginDTO;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginAction
{
    protected UserRepositoryInterface $repository;

    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(LoginDTO $dto): array
    {
        $user = $this->repository->findByEmail($dto->email);

        if (!$user || !Hash::check($dto->password, $user->password)) {
            return [
                'success' => false,
                'message' => 'Invalid credentials',
            ];
        }

        if ($user->status !== 1) {
            return [
                'success' => false,
                'message' => 'Account is deactivated',
            ];
        }

        // Update last login timestamp
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ]);

        return [
            'success' => true,
            'user' => $user,
        ];
    }
}