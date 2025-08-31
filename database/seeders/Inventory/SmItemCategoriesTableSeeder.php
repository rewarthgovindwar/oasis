<?php

namespace Database\Seeders\Inventory;

use App\SmItem;
use App\SmItemCategory;
use Illuminate\Database\Seeder;

class SmItemCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id, int $count = 5): void
    {
        //
        $school_academic = [
            'school_id' => $school_id,
            'academic_id' => $academic_id,
        ];
        SmItemCategory::factory()->times($count)->create($school_academic)->each(function ($itemCategory) use ($school_academic, $count): void {
            SmItem::factory()->times($count)->create(array_merge([
                'item_category_id' => $itemCategory->id,
            ], $school_academic));
        });
    }
}
