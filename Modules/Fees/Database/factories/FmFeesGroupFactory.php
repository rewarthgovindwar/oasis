<?php

namespace Modules\Fees\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Fees\Entities\FmFeesGroup;

class FmFeesGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $fees_groups = ['Library Fee', 'Processing Fee', 'Tuition Fee', 'Development Fee'];

    public $i = 0;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FmFeesGroup::class;

    public function definition()
    {
        return [
            'name' => $this->fees_groups[$this->i++] ?? $this->faker->unique()->word,
        ];
    }
}
