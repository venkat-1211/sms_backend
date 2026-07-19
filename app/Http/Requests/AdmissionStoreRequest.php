<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdmissionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'admission_date' => ['required', 'date', 'after_or_equal:today'],
            'total_fee' => ['required', 'numeric', 'min:0'],
            'amount_paid' => ['required', 'numeric', 'min:0', 'lte:total_fee'],
            'status' => ['sometimes', 'integer', Rule::in([0, 1])],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'Student is required',
            'student_id.exists' => 'Selected student does not exist',
            'course_id.required' => 'Course is required',
            'course_id.exists' => 'Selected course does not exist',
            'admission_date.required' => 'Admission date is required',
            'admission_date.after_or_equal' => 'Admission date cannot be in the past',
            'total_fee.required' => 'Total fee is required',
            'total_fee.numeric' => 'Total fee must be a valid number',
            'amount_paid.required' => 'Amount paid is required',
            'amount_paid.numeric' => 'Amount paid must be a valid number',
            'amount_paid.lte' => 'Amount paid cannot exceed total fee',
        ];
    }
}