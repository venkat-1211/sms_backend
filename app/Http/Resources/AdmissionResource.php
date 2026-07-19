<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\PaymentStatusEnum;

class AdmissionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'student_id' => $this->student_id,
            'course_id' => $this->course_id,
            'admission_date' => $this->admission_date,
            'admission_date_formatted' => $this->admission_date->format('M d, Y'),
            'total_fee' => $this->total_fee,
            'total_fee_formatted' => '₹ ' . $this->total_fee,
            'amount_paid' => $this->amount_paid,
            'amount_paid_formatted' => '₹ ' . $this->amount_paid,
            'balance_fee' => $this->balance_fee,
            'balance_fee_formatted' => '₹ ' . $this->balance_fee,
            'payment_status' => $this->payment_status,
            'payment_status_label' => $this->payment_status
                            ? PaymentStatusEnum::from($this->payment_status)->label()
                            : null,
            'payment_status_badge' => $this->payment_status
                            ? PaymentStatusEnum::from($this->payment_status)->label()
                            : null,
            'payment_percentage' => $this->payment_percentage,
            'status' => $this->status,
            'created_at' => $this->created_at->toISOString(),
            'created_at_formatted' => $this->created_at->format('M d, Y H:i'),
            'updated_at' => $this->updated_at->toISOString(),
            'student' => new StudentResource($this->whenLoaded('student')),
            'course' => new CourseResource($this->whenLoaded('course')),
        ];
    }
}