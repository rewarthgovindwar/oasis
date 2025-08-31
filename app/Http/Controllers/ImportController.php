<?php

namespace App\Http\Controllers;

use App\User;
use Throwable;
use App\SmStaff;
use App\SmBaseSetup;
use App\SmDesignation;
use App\SmHumanDepartment;
use App\Imports\StaffsImport;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\StaffImportBulkTemporary;
use App\Http\Requests\StaffImportRequestForm;
use Modules\RolePermission\Entities\InfixRole;

class ImportController extends Controller
{
    public function index()
    {
        $data['genders'] = SmBaseSetup::where('base_group_id', '=', '1')->get(['id', 'base_setup_name']);
        $data['roles'] = InfixRole::where('is_saas', 0)
            ->where('active_status', 1)
            ->where(function ($q): void {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })
            ->whereNotIn('id', [1, 2, 3])
            ->orderBy('name', 'asc')
            ->get();

        $data['departments'] = SmHumanDepartment::where('is_saas', 0)
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        $data['designations'] = SmDesignation::where('is_saas', 0)
            ->orderBy('title', 'asc')
            ->get(['id', 'title']);

        return view('backEnd.humanResource.import_staff', $data);
    }

    public function staffStore(StaffImportRequestForm $staffImportRequestForm)
    {
        try {
            DB::beginTransaction();
            Excel::import(new StaffsImport, $staffImportRequestForm->file('file'), 's3', \Maatwebsite\Excel\Excel::XLSX);
            $bulk_staffs = StaffImportBulkTemporary::where('user_id', Auth::user()->id)->get();

            $emailCounts = $bulk_staffs->groupBy('email')->map(function ($rows) {
                return $rows->count();
            });

            $duplicateEmails = $emailCounts->filter(function ($count): bool {
                return $count > 1;
            });

            if ($duplicateEmails->isNotEmpty()) {
                foreach ($duplicateEmails as $email => $count) {
                    toastr()->error('Duplicate email found: '.$email);
                }

                StaffImportBulkTemporary::where('user_id', Auth::user()->id)->delete();

                return redirect()->back();
            }

            if (! empty($bulk_staffs)) {
                foreach ($bulk_staffs as $bulk_staff) {

                    if (isSubscriptionEnabled()) {

                        $active_staff = SmStaff::where('school_id', Auth::user()->school_id)->where('active_status', 1)->count();

                        if (\Modules\Saas\Entities\SmPackagePlan::staff_limit() <= $active_staff && saasDomain() != 'school') {
                            DB::commit();
                            StaffImportBulkTemporary::where('user_id', Auth::user()->id)->delete();
                            Toastr::error('Your Staff limit has been crossed.', 'Failed');

                            return redirect()->route('staff_directory');
                        }
                    }

                    $role_id = InfixRole::where('is_saas', 0)->where('name', $bulk_staff->role)
                        ->where(function ($q): void {
                            $q->where('school_id', auth()->user()->school_id)
                                ->orWhere('type', 'System');
                        })
                        ->value('id') ?? null;
                    $department_id = SmHumanDepartment::where('name', $bulk_staff->department)->value('id') ?? null;
                    $designation_id = SmDesignation::where('title', $bulk_staff->designation)->value('id') ?? null;
                    if ($this->checkExitUser($bulk_staff->mobile, $bulk_staff->email)) {
                        continue;
                    }

                    if (! $role_id) {
                        continue;
                    }

                    $user = new User();
                    $user->role_id = $role_id == 1 ? 5 : $role_id;
                    $user->username = $bulk_staff->mobile ?: $bulk_staff->email;
                    $user->email = $bulk_staff->email;
                    $user->phone_number = $bulk_staff->mobile;
                    $user->full_name = $bulk_staff->first_name.' '.$bulk_staff->last_name;
                    $user->password = Hash::make(123456);
                    $user->school_id = Auth::user()->school_id;
                    $user->save();

                    if ($role_id == 5) {
                        $this->assignChatGroup($user);
                    }

                    if ($user) {
                        $basic_salary = $bulk_staff->basic_salary ?? 0;

                        $staff = new SmStaff();
                        $staff->staff_no = $bulk_staff->staff_no;
                        $staff->role_id = $role_id == 1 ? 5 : $role_id;
                        $staff->department_id = $department_id;
                        $staff->designation_id = $designation_id;
                        $staff->first_name = $bulk_staff->first_name;
                        $staff->last_name = $bulk_staff->last_name;
                        $staff->full_name = $bulk_staff->first_name.' '.$bulk_staff->last_name;
                        $staff->fathers_name = $bulk_staff->fathers_name;
                        $staff->mothers_name = $bulk_staff->mothers_name;
                        $staff->email = $bulk_staff->email;
                        $staff->school_id = Auth::user()->school_id;
                        $staff->gender_id = $bulk_staff->gender_id;
                        $staff->marital_status = $bulk_staff->marital_status;
                        $staff->date_of_birth = date('Y-m-d', strtotime($bulk_staff->date_of_birth));
                        $staff->date_of_joining = date('Y-m-d', strtotime($bulk_staff->date_of_joining));
                        $staff->mobile = $bulk_staff->mobile ?? null;
                        $staff->emergency_mobile = $bulk_staff->emergency_mobile;
                        $staff->current_address = $bulk_staff->current_address;
                        $staff->permanent_address = $bulk_staff->permanent_address;
                        $staff->qualification = $bulk_staff->qualification;
                        $staff->experience = $bulk_staff->experience;
                        $staff->epf_no = $bulk_staff->epf_no;
                        $staff->basic_salary = $basic_salary;
                        $staff->contract_type = $bulk_staff->contract_type;
                        $staff->location = $bulk_staff->location;
                        $staff->bank_account_name = $bulk_staff->bank_account_name;
                        $staff->bank_account_no = $bulk_staff->bank_account_no;
                        $staff->bank_name = $bulk_staff->bank_name;
                        $staff->bank_brach = $bulk_staff->bank_brach;
                        $staff->facebook_url = $bulk_staff->facebook_url;
                        $staff->twiteer_url = $bulk_staff->twitter_url;
                        $staff->linkedin_url = $bulk_staff->linkedin_url;
                        $staff->instragram_url = $bulk_staff->instagram_url;
                        $staff->user_id = $user->id;
                        $staff->driving_license = $bulk_staff->driving_license;
                        $staff->save();
                    }
                }

                StaffImportBulkTemporary::where('user_id', Auth::user()->id)->delete();
                DB::commit();
                Toastr::success('Operation successful', 'Success');

                return redirect()->route('staff_directory');
            }
        } catch (Throwable $throwable) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->route('staff_directory');
        }

        return null;
    }

    private function checkExitUser($phone_number = null, $email = null): bool
    {
        $user = User::when($phone_number && ! $email, function ($q) use ($phone_number): void {
            $q->where('phone_number', $phone_number)->orWhere('username', $phone_number);
        })
            ->when($email && ! $phone_number, function ($q) use ($email): void {
                $q->where('email', $email)->orWhere('username', $email);
            })
            ->when($email && $phone_number, function ($q) use ($phone_number): void {
                $q->where('phone_number', $phone_number);
            })
            ->first();

        return (bool) $user;
    }

    private function assignChatGroup(User $user): void
    {
        $groups = \Modules\Chat\Entities\Group::where('school_id', auth()->user()->school_id)->get();
        foreach ($groups as $group) {
            createGroupUser($group, $user->id);
        }
    }
}
