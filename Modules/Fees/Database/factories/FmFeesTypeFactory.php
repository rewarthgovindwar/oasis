<?php

namespace Modules\Fees\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Fees\Entities\FmFeesType;

class FmFeesTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $fees_types = ['Transportation', 'Dormitory', 'Library', 'Sports', 'Environment', 'E-learning', 'Fine', 'Extra-curricular activities', 'Software', 'Uniforms', 'Lunch', 'School Products'];

    public $i = 0;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FmFeesType::class;

    public function definition()
    {
        return [
            'name' => $this->fees_types[$this->i++] ?? $this->faker->unique()->word,
        ];
    }
}
