<?php

namespace App\Actions;

use App\DTO\StudentDTO;
use App\Interfaces\StudentRepositoryInterface;
use App\Models\Student;

class CreateStudentAction
{
    protected StudentRepositoryInterface $repository;

    public function __construct(StudentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(StudentDTO $dto): Student
    {
        return $this->repository->create($dto->toArray());
    }
}