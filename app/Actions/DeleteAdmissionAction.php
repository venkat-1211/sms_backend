<?php

namespace App\Actions;

use App\Interfaces\AdmissionRepositoryInterface;

class DeleteAdmissionAction
{
    protected AdmissionRepositoryInterface $repository;

    public function __construct(AdmissionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id): bool
    {
        return $this->repository->delete($id);
    }
}