<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'stats' => $this->resource['stats'],
            'recent_students' => StudentResource::collection($this->resource['recent_students']),
            'recent_admissions' => AdmissionResource::collection($this->resource['recent_admissions']),
            'popular_courses' => CourseResource::collection($this->resource['popular_courses']),
            'payment_distribution' => $this->resource['payment_distribution'],
            'monthly_revenue' => $this->resource['monthly_revenue'],
        ];
    }
}