<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\GenderEnum;

class StudentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'date_of_birth' => $this->date_of_birth,
            'age' => $this->date_of_birth ? now()->diffInYears($this->date_of_birth) : null,
            'gender' => $this->gender,
            'gender_label' => $this->gender
                            ? GenderEnum::from($this->gender)->label()
                            : null,
            'address' => $this->address,
            'status' => $this->status,
            'status_label' => $this->status == 1 ? 'Active': 'Inactive',
            'status_badge' => $this->status == 1 ? 'Active': 'Inactive',
            'created_at' => $this->created_at,
            'created_at_formatted' => $this->created_at->format('M d, Y H:i'),
            'updated_at' => $this->updated_at,
            'admissions_count' => $this->whenCounted('admissions'),
            'courses_count' => $this->whenCounted('courses'),
            'admissions' => AdmissionResource::collection($this->whenLoaded('admissions')),
            'courses' => CourseResource::collection($this->whenLoaded('courses')),
        ];
    }
}