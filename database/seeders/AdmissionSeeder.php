<?php

namespace Database\Seeders;

use App\Models\Admission;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AdmissionSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::pluck('id')->toArray();
        $courses = Course::pluck('id')->toArray();

        $paymentStatuses = ['pending', 'partial', 'paid'];
        $admissionDates = [
            Carbon::now()->subMonths(6),
            Carbon::now()->subMonths(5),
            Carbon::now()->subMonths(4),
            Carbon::now()->subMonths(3),
            Carbon::now()->subMonths(2),
            Carbon::now()->subMonths(1),
            Carbon::now(),
        ];

        // Create 100 admissions
        for ($i = 0; $i < 100; $i++) {
            $studentId = $students[array_rand($students)];
            $courseId = $courses[array_rand($courses)];
            $course = Course::find($courseId);
            $totalFee = $course ? $course->total_fee : rand(3000, 20000);
            
            $status = $paymentStatuses[array_rand($paymentStatuses)];
            $amountPaid = 0;
            
            switch ($status) {
                case 'paid':
                    $amountPaid = $totalFee;
                    break;
                case 'partial':
                    $amountPaid = round($totalFee * (rand(20, 80) / 100), 2);
                    break;
                case 'pending':
                default:
                    $amountPaid = 0;
                    break;
            }

            Admission::create([
                'student_id' => $studentId,
                'course_id' => $courseId,
                'admission_date' => $admissionDates[array_rand($admissionDates)],
                'total_fee' => $totalFee,
                'amount_paid' => $amountPaid,
                'balance_fee' => $totalFee - $amountPaid,
                'payment_status' => $status,
                'status' => rand(0, 1),
            ]);
        }
    }
}