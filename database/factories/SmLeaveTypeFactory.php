<?php

namespace Database\Factories;

use App\Models\Model;
use App\SmLeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class SmLeaveTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public $leaveType = ['Casual Leave', 'Sick Leave', 'Annual/Vacation Leave', 'Earned Leave', 'Public holidays', 'Maternity/Paternity', 'Administrative leave'];

    public $i = 0;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SmLeaveType::class;

    public function definition()
    {
        return [
            'type' => $this->leaveType[$this->i++],
            'total_days' => random_int(5, 10),
        ];
    }
}
