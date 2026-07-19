<?php

namespace App\Actions;

use App\DTO\StudentDTO;
use App\Interfaces\StudentRepositoryInterface;
use App\Models\Student;

class UpdateStudentAction
{
    protected StudentRepositoryInterface $repository;

    public function __construct(StudentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id, StudentDTO $dto): Student
    {
        return $this->repository->update($id, $dto->toArray());
    }
}