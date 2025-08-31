<?php

namespace Database\Factories;

use App\SmRoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

class SmRoomTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $data = ['Single', 'Double', 'Triple', 'Quad', 'Queen', 'King'];

    public $i = 0;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SmRoomType::class;

    public function definition()
    {

        return [
            'type' => $this->data[$this->i++] ?? $this->faker->unique()->colorName,
            'description' => $this->faker->colorName,
        ];
    }
}
