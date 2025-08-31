<?php

namespace Database\Factories;

use App\SmSchool;
use Illuminate\Database\Eloquent\Factories\Factory;

class SmSchoolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $i = 1;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SmSchool::class;

    public function definition()
    {
        $i = $this->i++;

        return [

            'school_name' => $this->faker->colorName.$i,
            'email' => 'school_'.$i.'@infixedu.com',
            'domain' => 'school'.$i,
            'created_at' => date('Y-m-d h:i:s'),
            'starting_date' => date('Y-m-d'),
            'is_email_verified' => 1,
        ];
    }
}
