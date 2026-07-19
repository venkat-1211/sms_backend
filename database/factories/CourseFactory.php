<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        $courseTypes = [
            'Bachelor of Science in ',
            'Master of ',
            'Diploma in ',
            'Certificate in ',
            'Professional ',
            'Advanced ',
        ];
        
        $subjects = [
            'Computer Science', 'Business Administration', 'Engineering', 
            'Medicine', 'Law', 'Education', 'Arts', 'Science', 
            'Technology', 'Management', 'Finance', 'Marketing',
            'Psychology', 'Sociology', 'History', 'Mathematics',
            'Physics', 'Chemistry', 'Biology', 'Economics',
        ];
        
        return [
            'course_name' => $this->faker->randomElement($courseTypes) . $this->faker->randomElement($subjects),
            'duration' => $this->faker->numberBetween(2, 48),
            'total_fee' => $this->faker->numberBetween(2000, 50000),
            'status' => $this->faker->numberBetween(0, 1),
        ];
    }
}