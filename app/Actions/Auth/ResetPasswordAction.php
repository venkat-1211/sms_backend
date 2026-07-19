<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Password;

class ResetPasswordAction
{
    public function execute(array $data): array
    {
        $status = Password::reset(
            $data,
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return [
                'success' => false,
                'message' => 'Unable to reset password. Invalid token or email.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Password reset successfully',
        ];
    }
}