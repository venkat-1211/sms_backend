<?php

namespace App\Actions;

use App\DTO\AdmissionDTO;
use App\Interfaces\AdmissionRepositoryInterface;
use App\Models\Admission;

class CreateAdmissionAction
{
    protected AdmissionRepositoryInterface $repository;

    public function __construct(AdmissionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(AdmissionDTO $dto): Admission
    {
        return $this->repository->create($dto->toArray());
    }
}