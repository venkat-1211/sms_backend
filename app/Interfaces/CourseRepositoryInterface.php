<?php

namespace App\Interfaces;

interface CourseRepositoryInterface
{
    public function all(array $filters = [], int $perPage = 15);
    public function find(int $id);
    public function findByUuid(string $uuid);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function search(string $search, array $filters = [], int $perPage = 15);
    public function getStats(): array;
    public function getPopularCourses(int $limit = 5);
    public function getActiveCourses();
}