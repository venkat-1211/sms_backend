<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            [
                'full_name' => 'Alice Johnson',
                'email' => 'alice@example.com',
                'mobile' => '+1234567890',
                'date_of_birth' => '2000-05-15',
                'gender' => 'female',
                'address' => '123 Main St, New York, NY 10001',
                'status' => 1,
            ],
            [
                'full_name' => 'Bob Wilson',
                'email' => 'bob@example.com',
                'mobile' => '+1234567891',
                'date_of_birth' => '1999-08-22',
                'gender' => 'male',
                'address' => '456 Oak Ave, Los Angeles, CA 90001',
                'status' => 1,
            ],
            [
                'full_name' => 'Carol Brown',
                'email' => 'carol@example.com',
                'mobile' => '+1234567892',
                'date_of_birth' => '2001-12-10',
                'gender' => 'female',
                'address' => '789 Pine St, Chicago, IL 60601',
                'status' => 1,
            ],
            [
                'full_name' => 'David Lee',
                'email' => 'david@example.com',
                'mobile' => '+1234567893',
                'date_of_birth' => '2000-03-25',
                'gender' => 'male',
                'address' => '321 Elm St, Houston, TX 77001',
                'status' => 0,
            ],
            [
                'full_name' => 'Emma Davis',
                'email' => 'emma@example.com',
                'mobile' => '+1234567894',
                'date_of_birth' => '2002-07-18',
                'gender' => 'female',
                'address' => '654 Cedar Ln, Phoenix, AZ 85001',
                'status' => 1,
            ],
        ];

        foreach ($students as $student) {
            Student::create($student);
        }

        // Create 50 more students
        Student::factory(50)->create();
    }
}