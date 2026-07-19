<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ChangePasswordAction
{
    public function execute(User $user, string $currentPassword, string $newPassword): array
    {
        if (!Hash::check($currentPassword, $user->password)) {
            return [
                'success' => false,
                'message' => 'Current password is incorrect',
            ];
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // Revoke all tokens after password change (optional)
        // $user->tokens()->delete();

        return [
            'success' => true,
            'message' => 'Password changed successfully',
        ];
    }
}