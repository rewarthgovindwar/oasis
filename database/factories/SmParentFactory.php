<?php

namespace Database\Factories;

use App\Models\Model;
use App\SmParent;
use Illuminate\Database\Eloquent\Factories\Factory;

class SmParentFactory extends Factory
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
    protected $model = SmParent::class;

    public function definition()
    {
        return [
            'fathers_name' => $this->faker->firstNameMale,
            'fathers_mobile' => random_int(1000, 9999).random_int(1000, 9999),
            'fathers_occupation' => 'Teacher',
            'mothers_name' => $this->faker->firstNameFemale,
            'mothers_mobile' => random_int(1000, 9999).random_int(1000, 9999),
            'mothers_occupation' => 'Housewife',
            'guardians_name' => $this->faker->firstNameMale,
            'guardians_mobile' => random_int(1000, 9999).random_int(1000, 9999),
            'guardians_email' => 'guardian_'.$this->i++.'@infixedu.com',
            'guardians_occupation' => 'Businessman',
            'guardians_relation' => 'Father',
            'relation' => 'Son',
            'guardians_address' => 'Dhaka-1219, Bangladesh',
            'is_guardian' => 1,
        ];
    }
}
