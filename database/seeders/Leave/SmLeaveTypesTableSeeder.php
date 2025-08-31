<?php

namespace Database\Seeders\Leave;

use App\Http\Requests\Admin\Leave\SmLeaveRequest;
use App\SmLeaveDefine;
use App\SmLeaveType;
use App\SmStaff;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Modules\RolePermission\Entities\InfixRole;

class SmLeaveTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($school_id, $academic_id, int $count = 4): void
    {
        $school_academic = [
            'school_id' => $school_id,
            'academic_id' => $academic_id,
        ];
        $roles = InfixRole::get();
        $staffs = SmStaff::where('school_id', $school_id)->get(['id', 'full_name']);
        SmLeaveType::factory()->times($count)->create($school_academic)->each(function ($leaveTypes) use ($roles, $school_id, $academic_id, $staffs): void {
            foreach ($roles as $role) {
                $users = User::where('role_id', $role->id)->get();
                foreach ($users as $user) {
                    $store = new SmLeaveDefine();
                    $store->role_id = $role->id;
                    $store->user_id = $user->id;
                    $store->type_id = $leaveTypes->id;
                    $store->days = $leaveTypes->total_days;
                    $store->school_id = $school_id;
                    $store->academic_id = $academic_id;
                    $store->save();
                }
            }

            foreach ($staffs as $staff) {

                $storeRequest = new SmLeaveRequest();
                $storeRequest->type_id = $leaveTypes->id;
                $storeRequest->leave_define_id = 1;
                $storeRequest->staff_id = $staff->id;
                $storeRequest->role_id = 4;
                $storeRequest->apply_date = Carbon::now()->format('Y-m-d');
                $storeRequest->leave_from = Carbon::now()->format('Y-m-d');
                $storeRequest->leave_to = Carbon::now()->addDays(2)->format('Y-m-d');
                $storeRequest->reason = 'Seeder Leave';
                $storeRequest->note = 'Seeder Leave';
                $storeRequest->file = 'public/uploads/leave_request/sample.pdf';
                $storeRequest->approve_status = 'P';
                $storeRequest->school_id = $school_id;
                $storeRequest->academic_id = $academic_id;
                // $storeRequest->save();
            }

        });
    }
}
