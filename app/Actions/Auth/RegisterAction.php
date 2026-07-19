<?php

namespace App\Actions\Auth;

use App\DTO\RegisterDTO;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterAction
{
    protected UserRepositoryInterface $repository;

    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(RegisterDTO $dto): User
    {
        return DB::transaction(function () use ($dto) {
            $user = $this->repository->create([
                'name' => $dto->name,
                'email' => $dto->email,
                'password' => Hash::make($dto->password),
                'avatar' => $dto->avatar,
                'status' => 1,
            ]);

            // Send email verification notification
            $user->sendEmailVerificationNotification();

            return $user;
        });
    }
}