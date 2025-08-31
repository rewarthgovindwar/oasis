<?php

namespace Database\Factories;

use App\Models\Model;
use App\SmItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class SmItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $i = 0;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SmItem::class;

    public function definition()
    {
        return [
            'item_name' => $this->faker->colorName.$this->i++,
        ];
    }
}
