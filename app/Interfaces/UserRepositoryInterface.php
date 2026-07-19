<?php

namespace App\Interfaces;

use App\Models\User;

interface UserRepositoryInterface
{
    public function all(array $filters = [], int $perPage = 15);
    public function find(int $id);
    public function findByUuid(string $uuid);
    public function findByEmail(string $email);
    public function findByGoogleId(string $googleId);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}