<?php

namespace App\Interfaces;

interface StudentRepositoryInterface
{
    public function all(array $filters = [], int $perPage = 15);
    public function find(int $id);
    public function findByUuid(string $uuid);
    public function findByEmail(string $email);
    public function findByMobile(string $mobile);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function search(string $search, array $filters = [], int $perPage = 15);
    public function getStats(): array;
    public function getRecentStudents(int $limit = 5);
    public function getStudentsByCourse(int $courseId);
    public function getGenderDistribution(): array;
}