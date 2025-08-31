<?php

namespace Database\Factories;

use App\Models\Model;
use App\SmVehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class SmVehicleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SmVehicle::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'vehicle_no' => 'INFIX-'.random_int(100, 1000),
            'vehicle_model' => 'INFIX-M'.random_int(100, 1000),
            'made_year' => date('Y'),
            'note' => $this->faker->sentence($nbWords = 6, $variableNbWords = true),
            'driver_id' => 5,
        ];
    }
}
