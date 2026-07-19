<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');
        
        return [
            'course_name' => ['required', 'string', 'max:255', Rule::unique('courses')->ignore($id)],
            'duration' => ['required', 'integer', 'min:1', 'max:60'],
            'total_fee' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'status' => ['sometimes', 'integer', Rule::in([0, 1])],
        ];
    }

    public function messages(): array
    {
        return [
            'course_name.required' => 'Course name is required',
            'course_name.unique' => 'This course name already exists',
            'duration.required' => 'Duration is required',
            'duration.min' => 'Duration must be at least 1 month',
            'duration.max' => 'Duration cannot exceed 60 months',
            'total_fee.required' => 'Total fee is required',
            'total_fee.numeric' => 'Total fee must be a valid number',
            'total_fee.min' => 'Total fee cannot be negative',
        ];
    }
}