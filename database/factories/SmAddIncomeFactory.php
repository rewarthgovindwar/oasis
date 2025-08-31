<?php

namespace Database\Factories;

use App\Models\Model;
use App\SmAddIncome;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class SmAddIncomeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SmAddIncome::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'payment_method_id' => 1,
            'date' => Carbon::now()->format('Y-m-d'),
            'amount' => 1300 + random_int(0, mt_getrandmax()) % 10000,
        ];
    }
}
