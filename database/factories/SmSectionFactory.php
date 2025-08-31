<?php

namespace Database\Factories;

use App\SmSection;
use Illuminate\Database\Eloquent\Factories\Factory;

class SmSectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $school_id;

    public $academic_id;

    public $section = ['A', 'B', 'C', 'D', 'E'];

    public $i = 0;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SmSection::class;

    public function definition()
    {

        return [
            'section_name' => $this->section[$this->i++] ?? $this->faker->word,
            'school_id' => 1,
            'created_at' => date('Y-m-d h:i:s'),
        ];
    }
}
