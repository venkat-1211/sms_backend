<?php

namespace App\Actions\Auth;

use App\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Password;

class ForgotPasswordAction
{
    protected UserRepositoryInterface $repository;

    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(string $email): array
    {
        $user = $this->repository->findByEmail($email);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'No user found with this email address',
            ];
        }

        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            return [
                'success' => false,
                'message' => 'Unable to send reset link. Please try again.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Password reset link sent to your email',
        ];
    }
}