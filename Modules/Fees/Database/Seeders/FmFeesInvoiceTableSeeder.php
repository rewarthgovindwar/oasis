<?php

namespace Modules\Fees\Database\Seeders;

use App\Models\StudentRecord;
use Illuminate\Database\Seeder;
use Modules\Fees\Entities\FmFeesGroup;
use Modules\Fees\Entities\FmFeesInvoice;
use Modules\Fees\Entities\FmFeesInvoiceChield;
use Modules\Fees\Entities\FmFeesType;

class FmFeesInvoiceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id, int $count = 5): void
    {
        $school_academic = ['school_id' => $school_id, 'academic_id' => $academic_id];
        $smStudentIds = StudentRecord::select('id', 'class_id', 'student_id')->get();

        FmFeesGroup::factory()->times($count)->create($school_academic)->each(function ($feesGroup) use ($school_academic, $smStudentIds): void {
            FmFeesType::factory()->times(5)->create(array_merge([
                'fees_group_id' => $feesGroup->id,
            ], $school_academic))->each(function ($feesTypes) use ($school_academic, $smStudentIds): void {
                foreach ($smStudentIds as $smStudentId) {
                    FmFeesInvoice::factory()->times(1)->create(array_merge([
                        'invoice_id' => generateRandomString(15),
                        'student_id' => $smStudentId->id,
                        'class_id' => $smStudentId->class_id,
                    ], $school_academic))->each(function ($feesInvoices) use ($school_academic, $feesTypes): void {
                        FmFeesInvoiceChield::factory()->times(1)->create(array_merge([
                            'fees_invoice_id' => $feesInvoices->id,
                            'fees_type' => $feesTypes->id,
                        ], $school_academic));
                    });
                }
            });
        });
    }
}
