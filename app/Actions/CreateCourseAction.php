<?php

namespace App\Actions;

use App\DTO\CourseDTO;
use App\Interfaces\CourseRepositoryInterface;
use App\Models\Course;

class CreateCourseAction
{
    protected CourseRepositoryInterface $repository;

    public function __construct(CourseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(CourseDTO $dto): Course
    {
        return $this->repository->create($dto->toArray());
    }
}