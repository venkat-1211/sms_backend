<?php

namespace App\Actions;

use App\Interfaces\StudentRepositoryInterface;

class DeleteStudentAction
{
    protected StudentRepositoryInterface $repository;

    public function __construct(StudentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id): bool
    {
        return $this->repository->delete($id);
    }
}