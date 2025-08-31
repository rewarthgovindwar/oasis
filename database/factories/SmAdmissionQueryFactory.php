<?php

namespace Database\Factories;

use App\Models\Model;
use App\SmAdmissionQuery;
use Illuminate\Database\Eloquent\Factories\Factory;

class SmAdmissionQueryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SmAdmissionQuery::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->email,
            'address' => $this->faker->address,
            'description' => $this->faker->sentence($nbWords = 3, $variableNbWords = true),
            'date' => $this->faker->dateTime()->format('Y-m-d'),
            'follow_up_date' => $this->faker->dateTime()->format('Y-m-d'),
            'next_follow_up_date' => $this->faker->dateTime()->format('Y-m-d'),
            'assigned' => $this->faker->name,
            'reference' => random_int(1, 5),
            'source' => random_int(1, 5),
            'no_of_child' => random_int(1, 4),
        ];
    }
}
