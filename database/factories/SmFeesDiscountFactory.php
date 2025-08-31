<?php

namespace Database\Factories;

use App\SmFeesDiscount;
use Illuminate\Database\Eloquent\Factories\Factory;

class SmFeesDiscountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $types = ['once', 'year'];

    public $i = 0;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SmFeesDiscount::class;

    public function definition()
    {
        $i = $this->i++;

        return [
            'name' => $this->faker->unique()->word ?? $this->faker->colorName.$i,
            'code' => 'SB-0'.$i,
            'type' => $this->faker->randomElement($this->types),
            'amount' => random_int(100, 200),
            'description' => $this->faker->word,
        ];
    }
}
