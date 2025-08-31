<?php

namespace App\Http\Controllers\Admin\Hr;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Hr\staffAttendanceBulkStoreRequest;
use App\Http\Requests\Admin\Hr\staffAttendanceReportSearchRequest;
use App\Http\Requests\Admin\Hr\staffAttendanceSearchRequest;
use App\Imports\StaffAttendanceBulk;
use App\SmStaff;
use App\SmStaffAttendanceImport;
use App\SmStaffAttendence;
use App\Traits\NotificationSend;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use Modules\RolePermission\Entities\InfixRole;

class SmStaffAttendanceController extends Controller
{
    use NotificationSend;

    public function staffAttendance(Request $request)
    {

        /*
        try {
        */
        $roles = InfixRole::where('is_saas', 0)->where('active_status', '=', '1')->whereNotIn('id', [1, 2, 3])->where(function ($q): void {
            $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
        })
            ->orderBy('name', 'asc')
            ->get();

        return view('backEnd.humanResource.staff_attendance', ['roles' => $roles]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function staffAttendanceSearch(staffAttendanceSearchRequest $staffAttendanceSearchRequest)
    {
        /*
        try {
        */
        $date = $staffAttendanceSearchRequest->attendance_date;
        $user = Auth::user();
        $roles = InfixRole::where('is_saas', 0)->where('active_status', '=', '1')
            ->whereNotIn('id', [1, 2, 3])
            ->whereOr(['school_id', $user->school_id], ['school_id', 1])
            ->select(['type', 'name', 'id'])
            ->get();
        $role_id = $staffAttendanceSearchRequest->role;
        $staffs = SmStaff::with('DateWiseStaffAttendance', 'roles')
            ->where(function ($q) use ($staffAttendanceSearchRequest): void {
                $q->where('role_id', $staffAttendanceSearchRequest->role)->orWhere('previous_role_id', $staffAttendanceSearchRequest->role);
            })->status()
            ->select(['last_name', 'first_name', 'id', 'staff_no'])
            ->get();

        if ($staffs->isEmpty()) {
            Toastr::error('No result found', 'Failed');

            return redirect('staff-attendance');
        }

        $attendance_type = $staffs[0]->DateWiseStaffAttendance !== null ? $staffs[0]->DateWiseStaffAttendance->attendence_type : '';

        return view('backEnd.humanResource.staff_attendance', ['role_id' => $role_id, 'date' => $date, 'roles' => $roles, 'staffs' => $staffs, 'attendance_type' => $attendance_type]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function staffAttendanceStore(Request $request)
    {
        /*
                try {
                */
        foreach ($request->id as $staff) {
            $attendance = SmStaffAttendence::where('staff_id', $staff)
                ->where('attendence_date', date('Y-m-d', strtotime($request->date)))
                ->where('school_id', Auth::user()->school_id)
                ->first();

            if ($attendance) {
                $attendance->delete();
            }

            $attendance = new SmStaffAttendence();
            $attendance->staff_id = $staff;
            $attendance->school_id = Auth::user()->school_id;
            $attendance->attendence_type = $request->attendance[$staff];
            $attendance->notes = $request->note[$staff];
            $attendance->attendence_date = date('Y-m-d', strtotime($request->date));
            $attendance->academic_id = getAcademicId();
            $attendance->save();

            $data['teacher_name'] = $attendance->StaffInfo->full_name;
            $this->sent_notifications('Staff_Attendance', (array) $attendance->StaffInfo->user_id, $data, ['Teacher']);

            $staffInfo = SmStaff::find($staff);
            $compact['slug'] = 'staff';
            $compact['user_email'] = $staffInfo->email;
            $compact['staff_name'] = $staffInfo->full_name;
            $compact['attendance_date'] = date('Y-m-d', strtotime($request->date));
            if ($request->attendance[$staff] == 'P') {
                @send_sms($staffInfo->mobile, 'staff_attendance', $compact);
            } elseif ($request->attendance[$staff] == 'A') {
                @send_sms($staffInfo->mobile, 'staff_absent', $compact);
            } elseif ($request->attendance[$staff] == 'L') {
                @send_sms($staffInfo->mobile, 'staff_late', $compact);
            }
        }

        Toastr::success('Operation successful', 'Success');

        return redirect('staff-attendance');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect('staff-attendance');
        }
        */
    }

    public function staffHolidayStore(Request $request)
    {
        $staffs = SmStaff::where('role_id', $request->role_id)
            ->where('active_status', 1)
            ->where('school_id', Auth::user()->school_id)
            ->get();
        if ($staffs->isEmpty()) {
            Toastr::error('No Result Found', 'Failed');

            return redirect('staff-attendance');
        }

        foreach ($staffs as $staff) {
            $attendance = SmStaffAttendence::where('staff_id', $staff->id)
                ->where('attendence_date', date('Y-m-d', strtotime($request->date)))
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->first();

            if (! empty($attendance)) {
                $attendance->delete();
            }

            if ($request->purpose == 'mark') {
                $attendance = new SmStaffAttendence();
                $attendance->attendence_type = 'H';
                $attendance->notes = 'Holiday';
                $attendance->attendence_date = date('Y-m-d', strtotime($request->date));
                $attendance->staff_id = $staff->id;
                $attendance->academic_id = getAcademicId();
                $attendance->school_id = Auth::user()->school_id;
                $attendance->save();

                $compact['holiday_date'] = date('Y-m-d', strtotime($request->date));
                @send_sms($staff->mobile, 'holiday', $compact);
            }
        }

        Toastr::success('Operation successful', 'Success');

        return redirect('staff-attendance');
    }

    public function staffAttendanceReport(Request $request)
    {
        /*
        try {
        */
        $roles = InfixRole::where('is_saas', 0)->where('active_status', '=', '1')
            ->whereNotIn('id', [1, 2, 3])
            ->whereOr(['school_id', Auth::user()->school_id], ['school_id', 1])
            ->orderBy('name', 'asc')
            ->select(['name', 'id'])
            ->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendResponse($roles, null);
        }

        return view('backEnd.humanResource.staff_attendance_report', ['roles' => $roles]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function staffAttendanceReportSearch(staffAttendanceReportSearchRequest $staffAttendanceReportSearchRequest)
    {
        /*
        try {
        */
        $year = $staffAttendanceReportSearchRequest->year;
        $month = $staffAttendanceReportSearchRequest->month;
        $role_id = $staffAttendanceReportSearchRequest->role;
        $current_day = date('d');
        $user = Auth::user();
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        // Fetch roles with filtering
        $roles = InfixRole::where('is_saas', 0)
            ->whereNotIn('id', [1, 2, 3])
            ->where(function ($q) use ($user): void {
                $q->where('school_id', $user->school_id)
                    ->orWhere('type', 'System');
            })
            ->select(['name', 'id'])
            ->get();

        // Fetch staff based on role
        $staffs = SmStaff::where('role_id', $role_id)
            ->where('school_id', $user->school_id)
            ->select(['id', 'staff_no', 'full_name'])
            ->get();

        $staffIds = $staffs->pluck('id');

        // Fetch attendance in a single query
        $attendances = SmStaffAttendence::whereIn('staff_id', $staffIds)
            ->whereYear('attendence_date', $year)
            ->whereMonth('attendence_date', $month)
            ->where('school_id', $user->school_id)
            ->with('staffInfo:id,full_name,staff_no')
            ->select(['id', 'attendence_type', 'staff_id', 'attendence_date'])
            ->get()
            ->groupBy('staff_id'); // Grouping by staff ID to simplify processing in the view

        return view('backEnd.humanResource.staff_attendance_report', ['attendances' => $attendances, 'staffs' => $staffs, 'days' => $days, 'year' => $year, 'month' => $month, 'current_day' => $current_day, 'roles' => $roles, 'role_id' => $role_id]);

        /*
                } catch (Exception $exception) {
                    Log::error('Staff Attendance Report Error: '.$exception->getMessage());
                    Toastr::error('Operation Failed', 'Failed');

                    return redirect()->back();
                }
                */
    }

    public function staffAttendancePrint($role_id, $month, $year)
    {

        /*
        try {
        */
        $current_day = date('d');
        $user = Auth::user();
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $roles = InfixRole::where('is_saas', 0)
            ->whereNotIn('id', [1, 2, 3])
            ->where(fn ($q) => $q->where('school_id', $user->school_id)->orWhere('type', 'System'))
            ->get();

        $staffs = SmStaff::where('role_id', $role_id)
            ->where('school_id', $user->school_id)
            ->get(['id', 'staff_no', 'full_name']);

        $role = InfixRole::find($role_id);

        $attendances = SmStaffAttendence::whereIn('staff_id', $staffs->pluck('id'))
            ->where('attendence_date', 'like', sprintf('%s-%s-%%', $year, $month))
            ->where('school_id', $user->school_id)
            ->get(['id', 'attendence_type', 'staff_id', 'attendence_date'])
            ->groupBy('staff_id');

        return view('backEnd.humanResource.staff_attendance_print', ['attendances' => $attendances, 'days' => $days, 'year' => $year, 'month' => $month, 'current_day' => $current_day, 'roles' => $roles, 'role_id' => $role_id, 'role' => $role, 'staffs' => $staffs]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function attendanceData(staffAttendanceSearchRequest $staffAttendanceSearchRequest)
    {

        /*
        try {
        */
        return $this->staffAttendanceSearch($staffAttendanceSearchRequest);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function teacherMyAttendanceSearchAPI(Request $request, $id = null)
    {

        $input = $request->all();

        $validator = Validator::make($input, [
            'month' => 'required',
            'year' => 'required',
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }
        /*
        try {
        */
        $teacher = SmStaff::where('user_id', $id)->where('school_id', Auth::user()->school_id)->first();

        $year = $request->year;
        $month = $request->month;
        if ($month < 10) {
            $month = '0'.$month;
        }

        $current_day = date('d');

        $days = cal_days_in_month(CAL_GREGORIAN, $month, $request->year);
        $days2 = '';
        if ($month !== 1) {
            $days2 = cal_days_in_month(CAL_GREGORIAN, $month - 1, $request->year);
        } else {
            $days2 = cal_days_in_month(CAL_GREGORIAN, $month, $request->year);
        }

        if ($month !== 1) {
            $previous_month = $month - 1;
            $previous_date = $year.'-'.$previous_month.'-'.$days2;
        } else {
            $previous_month = 12;
            $previous_date = $year - 1 .'-'.$previous_month.'-'.$days2;
        }

        $previousMonthDetails['date'] = $previous_date;
        $previousMonthDetails['day'] = $days2;
        $previousMonthDetails['week_name'] = date('D', strtotime($previous_date));

        $attendances = SmStaffAttendence::where('staff_id', $teacher->id)
            ->where('attendence_date', 'like', '%'.$request->year.'-'.$month.'%')
            ->select('attendence_type as attendance_type', 'attendence_date as attendance_date')
            ->where('school_id', Auth::user()->school_id)->get();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function staffAttendanceImport()
    {

        /*
        try {
        */
        return view('backEnd.humanResource.staff_attendance_import');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function downloadStaffAttendanceFile()
    {
        /*
        try {
        */
        $studentsArray = ['staff_id', 'attendance_date', 'in_time', 'out_time'];

        return Excel::create('staff_attendance_sheet', function ($excel) use ($studentsArray): void {
            $excel->sheet('staff_attendance_sheet', function ($sheet) use ($studentsArray): void {
                $sheet->fromArray($studentsArray);
            });
        })->download('xlsx');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function staffAttendanceBulkStore(staffAttendanceBulkStoreRequest $staffAttendanceBulkStoreRequest)
    {

        $file_type = mb_strtolower($staffAttendanceBulkStoreRequest->file->getClientOriginalExtension());

        if ($file_type !== 'csv' && $file_type !== 'xlsx' && $file_type !== 'xls') {
            Toastr::warning('The file must be a file of type: xlsx, csv or xls', 'Warning');

            return redirect()->back();
        }

        /*
            try {
            */
        Excel::import(new StaffAttendanceBulk(), $staffAttendanceBulkStoreRequest->file('file'), 's3', \Maatwebsite\Excel\Excel::XLSX);
        $data = SmStaffAttendanceImport::get();
        if (! empty($data)) {
            DB::beginTransaction();
            $staffs = SmStaff::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            $all_staff_ids = [];
            $present_staffs = [];

            foreach ($staffs as $staff) {
                $all_staff_ids[] = $staff->id;
            }

            try {
                SmStaffAttendanceImport::where('attendence_date', date('Y-m-d', strtotime($staffAttendanceBulkStoreRequest->attendance_date)))->delete();

                foreach ($data as $val) {
                    SmStaffAttendence::where('attendence_date', date('Y-m-d', strtotime($val->attendence_date)))
                        ->where('school_id', Auth::user()->school_id)
                        ->delete();
                }

                foreach ($data as $value) {
                    if (empty($value)) {
                        continue;
                    }

                    if (date('d/m/Y', strtotime($staffAttendanceBulkStoreRequest->attendance_date)) !== date('d/m/Y', strtotime($value->attendence_date))) {
                        continue;
                    }

                    $staff = SmStaff::find($value->staff_id);
                    $attendance = SmStaffAttendence::where('staff_id', $staff->id)
                        ->where('attendence_date', date('Y-m-d', strtotime($value->attendence_date)))
                        ->where('school_id', Auth::user()->school_id)
                        ->first();
                    if ($attendance !== '') {
                        $attendance->delete();
                    }

                    if ($staff !== '') {
                        $present_staffs[] = $staff->id;
                        $import = new SmStaffAttendence();
                        $import->staff_id = $staff->id;
                        $import->attendence_date = date('Y-m-d', strtotime($staffAttendanceBulkStoreRequest->attendance_date));
                        $import->attendence_type = $value->attendance_type;
                        $import->notes = $value->notes;
                        $import->school_id = Auth::user()->school_id;
                        $import->academic_id = getAcademicId();
                        $import->save();
                    }
                }

                foreach ($all_staff_ids as $all_staff_id) {
                    if (! in_array($all_staff_id, $present_staffs)) {
                        $import = new SmStaffAttendence();
                        $import->staff_id = $all_staff_id;
                        $import->attendence_type = 'A';
                        $import->attendence_date = date('Y-m-d', strtotime($staffAttendanceBulkStoreRequest->attendance_date));
                        $import->school_id = Auth::user()->school_id;
                        $import->academic_id = getAcademicId();
                        $import->save();
                    }
                }
            } catch (Exception $e) {
                DB::rollback();
                Toastr::error('Operation Failed', 'Failed');

                return redirect()->back();
            }
            DB::commit();
            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        }
        /*
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
        */

    }
}
