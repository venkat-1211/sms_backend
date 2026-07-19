<?php

namespace App\DTO;

class CourseDTO
{
    public readonly string $courseName;
    public readonly int $duration;
    public readonly float $totalFee;
    public readonly ?int $status;

    public function __construct(
        string $courseName,
        int $duration,
        float $totalFee,
        ?int $status = 1
    ) {
        $this->courseName = $courseName;
        $this->duration = $duration;
        $this->totalFee = $totalFee;
        $this->status = $status;
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            courseName: $data['course_name'],
            duration: $data['duration'],
            totalFee: $data['total_fee'],
            status: $data['status'] ?? 1
        );
    }

    public function toArray(): array
    {
        return [
            'course_name' => $this->courseName,
            'duration' => $this->duration,
            'total_fee' => $this->totalFee,
            'status' => $this->status,
        ];
    }
}