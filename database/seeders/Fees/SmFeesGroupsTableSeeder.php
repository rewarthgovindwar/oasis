<?php

namespace Database\Seeders\Fees;

use App\SmFeesGroup;
use App\SmFeesMaster;
use App\SmFeesType;
use Illuminate\Database\Seeder;

class SmFeesGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id, int $count = 5): void
    {

        $school_academic = ['school_id' => $school_id, 'academic_id' => $academic_id];

        SmFeesGroup::factory()->times($count)->create($school_academic)->each(function ($feesGroup) use ($school_academic): void {
            SmFeesType::factory()->times(5)->create(array_merge([
                'fees_group_id' => $feesGroup->id,
            ], $school_academic))->each(function ($feesTypes) use ($school_academic): void {
                SmFeesMaster::factory()->times(1)->create(array_merge([
                    'fees_group_id' => $feesTypes->fees_group_id,
                    'fees_type_id' => $feesTypes->id,
                ], $school_academic));
            });
        });
    }
}
