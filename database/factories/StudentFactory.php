<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        $genders = ['male', 'female', 'other'];
        $gender = $this->faker->randomElement($genders);
        
        return [
            'full_name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'mobile' => $this->faker->unique()->phoneNumber(),
            'date_of_birth' => $this->faker->dateTimeBetween('-30 years', '-18 years'),
            'gender' => $gender,
            'address' => $this->faker->address(),
            'status' => $this->faker->numberBetween(0, 1),
        ];
    }
}