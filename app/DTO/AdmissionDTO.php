<?php

namespace App\DTO;

class AdmissionDTO
{
    public readonly int $studentId;
    public readonly int $courseId;
    public readonly string $admissionDate;
    public readonly float $totalFee;
    public readonly float $amountPaid;
    public readonly ?int $status;

    public function __construct(
        int $studentId,
        int $courseId,
        string $admissionDate,
        float $totalFee,
        float $amountPaid,
        ?int $status = 1
    ) {
        $this->studentId = $studentId;
        $this->courseId = $courseId;
        $this->admissionDate = $admissionDate;
        $this->totalFee = $totalFee;
        $this->amountPaid = $amountPaid;
        $this->status = $status;
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            studentId: $data['student_id'],
            courseId: $data['course_id'],
            admissionDate: $data['admission_date'],
            totalFee: $data['total_fee'],
            amountPaid: $data['amount_paid'] ?? 0,
            status: $data['status'] ?? 1
        );
    }

    public function toArray(): array
    {
        return [
            'student_id' => $this->studentId,
            'course_id' => $this->courseId,
            'admission_date' => $this->admissionDate,
            'total_fee' => $this->totalFee,
            'amount_paid' => $this->amountPaid,
            'balance_fee' => $this->totalFee - $this->amountPaid,
            'status' => $this->status,
        ];
    }
}