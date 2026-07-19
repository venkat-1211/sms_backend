<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            [
                'course_name' => 'Computer Science (BSc)',
                'duration' => 36,
                'total_fee' => 15000.00,
                'status' => 1,
            ],
            [
                'course_name' => 'Business Administration (MBA)',
                'duration' => 24,
                'total_fee' => 18000.00,
                'status' => 1,
            ],
            [
                'course_name' => 'Data Science Bootcamp',
                'duration' => 6,
                'total_fee' => 8000.00,
                'status' => 1,
            ],
            [
                'course_name' => 'Web Development Bootcamp',
                'duration' => 4,
                'total_fee' => 6000.00,
                'status' => 1,
            ],
            [
                'course_name' => 'Digital Marketing Certification',
                'duration' => 3,
                'total_fee' => 4000.00,
                'status' => 1,
            ],
            [
                'course_name' => 'Graphic Design Diploma',
                'duration' => 12,
                'total_fee' => 9000.00,
                'status' => 0,
            ],
            [
                'course_name' => 'Cybersecurity Professional',
                'duration' => 18,
                'total_fee' => 12000.00,
                'status' => 1,
            ],
            [
                'course_name' => 'Project Management (PMP)',
                'duration' => 2,
                'total_fee' => 3000.00,
                'status' => 1,
            ],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }

        // Create 20 more courses
        Course::factory(20)->create();
    }
}