<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'course_name' => $this->course_name,
            'duration' => $this->duration,
            'duration_label' => $this->duration . ' Months',
            'total_fee' => $this->total_fee,
            'total_fee_formatted' => '₹ ' . $this->total_fee,
            'status' => $this->status,
            'status_label' => $this->status == 1 ? 'Active': 'Inactive',
            'created_at' => $this->created_at,
            'created_at_formatted' => $this->created_at->format('M d, Y H:i'),
            'updated_at' => $this->updated_at,
            'students_count' => $this->whenCounted('students'),
            'admissions_count' => $this->whenCounted('admissions'),
            'students' => StudentResource::collection($this->whenLoaded('students')),
            'admissions' => AdmissionResource::collection($this->whenLoaded('admissions')),
        ];
    }
}