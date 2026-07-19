<?php

namespace App\Actions;

use App\Interfaces\StudentRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class ExportStudentsAction
{
    protected StudentRepositoryInterface $repository;

    public function __construct(StudentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $filters = []): array
    {
        $cacheKey = 'students:export:' . md5(serialize($filters));

        return Cache::remember($cacheKey, 3600, function () use ($filters) {
            return $this->repository->all($filters, 1000)->items();
        });
    }
}