<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdmissionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');
        
        return [
            'student_id' => ['sometimes', 'integer', 'exists:students,id'],
            'course_id' => ['sometimes', 'integer', 'exists:courses,id'],
            'admission_date' => ['sometimes', 'date', 'after_or_equal:today'],
            'total_fee' => ['sometimes', 'numeric', 'min:0'],
            'amount_paid' => ['sometimes', 'numeric', 'min:0'],
            'status' => ['sometimes', 'integer', Rule::in([0, 1])],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.exists' => 'Selected student does not exist',
            'course_id.exists' => 'Selected course does not exist',
            'admission_date.after_or_equal' => 'Admission date cannot be in the past',
            'total_fee.numeric' => 'Total fee must be a valid number',
            'amount_paid.numeric' => 'Amount paid must be a valid number',
        ];
    }
}