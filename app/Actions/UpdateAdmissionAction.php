<?php

namespace App\Actions;

use App\DTO\AdmissionDTO;
use App\Interfaces\AdmissionRepositoryInterface;
use App\Models\Admission;

class UpdateAdmissionAction
{
    protected AdmissionRepositoryInterface $repository;

    public function __construct(AdmissionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id, AdmissionDTO $dto): Admission
    {
        return $this->repository->update($id, $dto->toArray());
    }
}