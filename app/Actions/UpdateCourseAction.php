<?php

namespace App\Actions;

use App\DTO\CourseDTO;
use App\Interfaces\CourseRepositoryInterface;
use App\Models\Course;

class UpdateCourseAction
{
    protected CourseRepositoryInterface $repository;

    public function __construct(CourseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id, CourseDTO $dto): Course
    {
        return $this->repository->update($id, $dto->toArray());
    }
}