<?php

namespace Database\Seeders\HumanResources;

use App\SmHrPayrollGenerate;
use App\SmStaff;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SmHrPayrollGeneratesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id): void
    {
        $increment = 100;

        $staffs = SmStaff::where('active_status', 1)->whereIn('role_id', [4, 5, 6, 7, 8, 9])->where('school_id', 1)->get(['id', 'user_id']);
        foreach ($staffs as $staff) {
            $store = new SmHrPayrollGenerate();
            $store->staff_id = $staff->id;
            $store->basic_salary = 3000 + $increment;
            $store->total_earning = 5000 + $increment;
            $store->total_deduction = 300 + $increment;
            $store->gross_salary = 4000 + $increment;
            $store->tax = $increment++;
            $store->net_salary = $store->basic_salary + $store->gross_salary - $store->total_deduction + $store->total_earning + $store->tax;
            $store->payroll_month = Carbon::now()->format('F');
            $store->payroll_year = Carbon::now()->year;
            $store->payroll_status = 'G';
            $store->payment_mode = 1;
            $store->created_at = date('Y-m-d h:i:s');
            $store->note = fake()->realText($maxNbChars = 100, $indexSize = 1);
            $store->school_id = $school_id;
            $store->academic_id = $academic_id;
            $store->save();
        }
    }
}
