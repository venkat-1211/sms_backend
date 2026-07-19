<?php

namespace App\Actions\Auth;

use App\DTO\ProfileDTO;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateProfileAction
{
    public function execute(User $user, ProfileDTO $dto): User
    {
        return DB::transaction(function () use ($user, $dto) {
            $user->update([
                'name' => $dto->name,
                'email' => $dto->email,
                'avatar' => $dto->avatar ?? $user->avatar,
                'bio' => $dto->bio ?? $user->bio,
            ]);

            return $user->fresh();
        });
    }
}