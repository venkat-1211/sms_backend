<?php

namespace App\Actions;

use App\Interfaces\StudentRepositoryInterface;
use Illuminate\Support\Facades\DB;

class BulkDeleteStudentsAction
{
    protected StudentRepositoryInterface $repository;

    public function __construct(StudentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $ids): array
    {
        $deleted = 0;
        $failed = 0;

        foreach ($ids as $id) {
            try {
                $this->repository->delete($id);
                $deleted++;
            } catch (\Exception $e) {
                $failed++;
            }
        }

        return [
            'deleted' => $deleted,
            'failed' => $failed,
        ];
    }
}