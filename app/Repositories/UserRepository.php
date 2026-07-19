<?php

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserRepository implements UserRepositoryInterface
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = [], int $perPage = 15)
    {
        $cacheKey = 'users:' . md5(serialize($filters) . $perPage . request('page', 1));

        return Cache::remember($cacheKey, 3600, function () use ($filters, $perPage) {
            return DB::table('users')
                ->select('id', 'uuid', 'name', 'email', 'avatar', 'status', 'created_at')
                ->when(isset($filters['search']), function ($query) use ($filters) {
                    return $query->where('name', 'LIKE', "%{$filters['search']}%")
                                ->orWhere('email', 'LIKE', "%{$filters['search']}%");
                })
                ->when(isset($filters['status']), function ($query) use ($filters) {
                    return $query->where('status', $filters['status']);
                })
                ->when(isset($filters['role']), function ($query) use ($filters) {
                    return $query->where('role', $filters['role']);
                })
                ->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_direction'] ?? 'desc')
                ->paginate($perPage);
        });
    }

    public function find(int $id)
    {
        return Cache::remember("user:{$id}", 3600, function () use ($id) {
            return $this->model->findOrFail($id);
        });
    }

    public function findByUuid(string $uuid)
    {
        return $this->model->where('uuid', $uuid)->firstOrFail();
    }

    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByGoogleId(string $googleId)
    {
        return $this->model->where('google_id', $googleId)->first();
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = $this->model->create($data);
            Cache::forget('users:*');
            return $user;
        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $user = $this->find($id);
            $user->update($data);
            Cache::forget("user:{$id}");
            Cache::forget('users:*');
            return $user;
        });
    }

    public function delete(int $id)
    {
        return DB::transaction(function () use ($id) {
            $user = $this->find($id);
            $user->delete();
            Cache::forget("user:{$id}");
            Cache::forget('users:*');
            return true;
        });
    }
}