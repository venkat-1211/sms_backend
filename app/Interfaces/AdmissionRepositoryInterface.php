<?php

namespace App\Interfaces;

interface AdmissionRepositoryInterface
{
    public function all(array $filters = [], int $perPage = 15);
    public function find(int $id);
    public function findByUuid(string $uuid);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function search(string $search, array $filters = [], int $perPage = 15);
    public function getStats(): array;
    public function getRevenueStats(): array;
    public function getRecentAdmissions(int $limit = 5);
    public function getAdmissionsByStudent(int $studentId);
    public function getAdmissionsByCourse(int $courseId);
    public function getPaymentStatusDistribution(): array;
    public function getMonthlyRevenue(int $months = 12);
}