<?php

namespace Modules\Fees\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Fees\Entities\FmFeesInvoice;

class FmFeesInvoiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FmFeesInvoice::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'create_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+10 days')),
            'payment_status' => 'not',
        ];
    }
}
