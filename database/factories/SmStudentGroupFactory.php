<?php

namespace Database\Factories;

use App\Models\Model;
use App\SmStudentGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class SmStudentGroupFactory extends Factory
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
    protected $model = SmStudentGroup::class;

    public function definition()
    {
        return [
            'group' => $this->faker->word.$this->i++,
        ];
    }
}
