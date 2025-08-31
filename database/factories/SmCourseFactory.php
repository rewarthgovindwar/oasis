<?php

namespace Database\Factories;

use App\Models\Model;
use App\SmCourse;
use Illuminate\Database\Eloquent\Factories\Factory;

class SmCourseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SmCourse::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->word(),
            'image' => 'public/uploads/testimonial/testimonial_1.jpg',
        ];
    }
}
