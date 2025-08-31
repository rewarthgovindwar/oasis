<?php

namespace Database\Factories;

use App\Models\Model;
use App\SmStudentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class SmStudentCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $category = [
        'Normal',
        'Obsessive Compulsive Disorder',
        'Attention Deficit Hyperactivity Disorder (ADHD)',
        'Oppositional Defiant Disorder (ODD)',
        'Anxiety Disorder',
        'Conduct Disorders',
    ];

    public $i = 0;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SmStudentCategory::class;

    public function definition()
    {
        return [
            'category_name' => $this->category[$this->i++] ?? $this->faker->word,
        ];
    }
}
