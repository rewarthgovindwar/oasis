<?php

namespace Database\Factories;

use App\SmFeesMaster;
use Illuminate\Database\Eloquent\Factories\Factory;

class SmFeesMasterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SmFeesMaster::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'amount' => 500 + random_int(0, mt_getrandmax()) % 500,
        ];
    }
}
