<?php

namespace Database\Seeders\Admin;

use App\SmStudentCertificate;
use Illuminate\Database\Seeder;

class SmStudentCertificateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id, int $count = 1): void
    {
        $school_academic = [
            'school_id' => $school_id,
            'academic_id' => $academic_id,
        ];
        SmStudentCertificate::factory()->times($count)->create($school_academic);

    }
}
