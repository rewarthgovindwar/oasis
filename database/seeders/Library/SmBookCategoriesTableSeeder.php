<?php

namespace Database\Seeders\Library;

use App\SmBook;
use App\SmBookCategory;
use Illuminate\Database\Seeder;

class SmBookCategoriesTableSeeder extends Seeder
{
    public function run($school_id = 1, int $count = 16): void
    {

        SmBookCategory::factory()->times($count)->create([
            'school_id' => $school_id,
        ])->each(function ($book_category) use ($school_id): void {
            SmBook::factory()->times(11)->create([
                'school_id' => $school_id,
                'quantity' => 100,
            ]);
        });
    }
}
