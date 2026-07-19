<?php

namespace App\Actions;

use App\Interfaces\CourseRepositoryInterface;

class DeleteCourseAction
{
    protected CourseRepositoryInterface $repository;

    public function __construct(CourseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id): bool
    {
        return $this->repository->delete($id);
    }
}