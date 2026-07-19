<?php

namespace App\DTO;

class StudentDTO
{
    public readonly string $fullName;
    public readonly string $email;
    public readonly string $mobile;
    public readonly string $dateOfBirth;
    public readonly string $gender;
    public readonly string $address;
    public readonly ?int $status;

    public function __construct(
        string $fullName,
        string $email,
        string $mobile,
        string $dateOfBirth,
        string $gender,
        string $address,
        ?int $status = 1
    ) {
        $this->fullName = $fullName;
        $this->email = $email;
        $this->mobile = $mobile;
        $this->dateOfBirth = $dateOfBirth;
        $this->gender = $gender;
        $this->address = $address;
        $this->status = $status;
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            fullName: $data['full_name'],
            email: $data['email'],
            mobile: $data['mobile'],
            dateOfBirth: $data['date_of_birth'],
            gender: $data['gender'],
            address: $data['address'],
            status: $data['status'] ?? 1
        );
    }

    public function toArray(): array
    {
        return [
            'full_name' => $this->fullName,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'date_of_birth' => $this->dateOfBirth,
            'gender' => $this->gender,
            'address' => $this->address,
            'status' => $this->status,
        ];
    }
}