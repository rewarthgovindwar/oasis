<?php

namespace Database\Factories;

use App\SmBaseSetup;
use Illuminate\Database\Eloquent\Factories\Factory;

class SmBaseSetupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $names = ['Male', 'Female', 'Others', 'Islam', 'Hinduism', 'Sikhism', 'Buddhism', 'Protestantism', 'A+', 'O+', 'B+', 'AB+', 'A-', 'O-', 'B-', 'AB-'];

    public $ids = [1, 1, 1, 2, 2, 2, 2, 2, 3, 3, 3, 3, 3, 3, 3, 3];

    public $i = 0;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SmBaseSetup::class;

    public function definition()
    {
        return [
            'base_group_id' => random_int(1, 3),
            'base_setup_name' => $this->names[$this->i++],

        ];
    }
}
