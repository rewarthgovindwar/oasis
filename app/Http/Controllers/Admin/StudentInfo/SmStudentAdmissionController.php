<?php

namespace App\Http\Controllers\Admin\StudentInfo;

use App\User;
use Exception;
use Throwable;
use App\SmClass;
use App\SmRoute;
use App\SmStaff;
use App\SmParent;
use App\SmSchool;
use App\SmSection;
use App\SmStudent;
use App\SmVehicle;
use Carbon\Carbon;
use App\SmExamType;
use App\SmBaseSetup;
use App\Models\Shift;
use App\SmMarksGrade;
use App\SmLeaveDefine;
use App\SmAcademicYear;
use App\SmClassTeacher;
use App\SmExamSchedule;
use App\SmStudentGroup;
use App\SmDormitoryList;
use App\SmGeneralSettings;
use App\SmStudentCategory;
use App\SmStudentTimeline;
use App\Imports\BulkImport;
use App\CustomResultSetting;
use App\SmStudentAttendance;
use App\SmSubjectAttendance;
use App\Traits\CustomFields;
use Illuminate\Http\Request;
use App\Models\SmCustomField;
use App\Models\StudentRecord;
use App\StudentBulkTemporary;
use App\Imports\StudentsImport;
use App\Traits\FeesAssignTrait;
use Modules\Lead\Entities\Lead;
use App\Traits\NotificationSend;
use Modules\Lead\Entities\Source;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Modules\Lead\Entities\LeadCity;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\DirectFeesAssignTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\HeadingRowImport;
use Modules\Saas\Entities\SmPackagePlan;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Support\Facades\Validator;
use App\Models\SmStudentRegistrationField;
use Modules\University\Entities\UnSubject;
use Modules\University\Entities\UnAcademicYear;
use Modules\University\Entities\UnAssignSubject;
use Modules\University\Entities\UnSemesterLabel;
use Modules\University\Entities\UnSubjectComplete;
use Modules\BehaviourRecords\Entities\AssignIncident;
use Modules\University\Entities\UnSubjectAssignStudent;
use App\Http\Controllers\Admin\Hr\StaffAsParentController;
use Modules\BehaviourRecords\Entities\BehaviourRecordSetting;
use Modules\ParentRegistration\Entities\SmStudentRegistration;
use App\Http\Requests\Admin\StudentInfo\SmStudentAdmissionRequest;
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;

class SmStudentAdmissionController extends Controller
{
    use CustomFields;
    use DirectFeesAssignTrait;
    use FeesAssignTrait;
    use NotificationSend;

    public static function loadData()
    {
        $base_setup = SmBaseSetup::get(['id', 'base_setup_name', 'base_group_id']);
        $data['classes'] = SmClass::get(['id', 'class_name']);
        $data['religions'] = $base_setup->where('base_group_id', '=', '2');
        $data['blood_groups'] = $base_setup->where('base_group_id', '=', '3');
        $data['genders'] = $base_setup->where('base_group_id', '=', '1');
        $data['route_lists'] = SmRoute::get(['id', 'title']);
        $data['dormitory_lists'] = SmDormitoryList::get(['id', 'dormitory_name']);
        $data['categories'] = SmStudentCategory::get(['id', 'category_name']);
        $data['groups'] = SmStudentGroup::get(['id', 'group']);
        $data['sessions'] = SmAcademicYear::get(['id', 'year', 'title']);
        $data['custom_fields'] = SmCustomField::where('form_name', 'student_registration')->where('school_id', Auth::user()->school_id)->get();
        $data['staffs'] = SmStaff::where('role_id', '!=', 1)->get(['first_name', 'last_name', 'full_name', 'id', 'user_id', 'parent_id']);
        $data['lead_city'] = [];
        $data['sources'] = [];

        if (moduleStatusCheck('Lead') == true) {
            $data['lead_city'] = LeadCity::where('school_id', auth()->user()->school_id)->get(['id', 'city_name']);
            $data['sources'] = Source::where('school_id', auth()->user()->school_id)->get(['id', 'source_name']);
        }

        if (moduleStatusCheck('University') == true) {
            $data['un_session'] = \Modules\University\Entities\UnSession::where('school_id', auth()->user()->school_id)->get(['id', 'name']);
            $data['un_academic_year'] = UnAcademicYear::where('school_id', auth()->user()->school_id)->get(['id', 'name']);
        }

        session()->forget(['fathers_photo', 'mothers_photo', 'guardians_photo', 'student_photo']);

        return $data;
    }

    public static function staffAsParent(int $staff_id) {}

    public function index()
    {

        /*
        try {
        */
            if (isSubscriptionEnabled() && auth()->user()->school_id != 1) {

                $active_student = SmStudent::where('school_id', Auth::user()->school_id)->where('active_status', 1)->count();

                if (SmPackagePlan::student_limit() <= $active_student) {

                    Toastr::error('Your student limit has been crossed.', 'Failed');

                    return redirect()->back();
                }
            }

            $data = static::loadData();
            $data['max_admission_id'] = SmStudent::where('school_id', Auth::user()->school_id)->max('admission_no');
            $data['max_roll_id'] = SmStudent::where('school_id', Auth::user()->school_id)->max('roll_no');

            if (moduleStatusCheck('University')) {
                return view('university::admission.add_student_admission', $data);
            }

            return view('backEnd.studentInformation.student_admission', $data);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmStudentAdmissionRequest $smStudentAdmissionRequest)
    {
        $parentInfo = $smStudentAdmissionRequest->fathers_name || $smStudentAdmissionRequest->fathers_phone || $smStudentAdmissionRequest->mothers_name || $smStudentAdmissionRequest->mothers_phone || $smStudentAdmissionRequest->guardians_email || $smStudentAdmissionRequest->guardians_phone;
        // add student record
        if ($smStudentAdmissionRequest->filled('phone_number') || $smStudentAdmissionRequest->filled('email_address')) {
            $user = User::where('school_id', auth()->user()->school_id)
                ->when($smStudentAdmissionRequest->filled('phone_number') && ! $smStudentAdmissionRequest->email_address, function ($q) use ($smStudentAdmissionRequest): void {
                    $q->where(function ($q) use ($smStudentAdmissionRequest) {
                        return $q->where('phone_number', $smStudentAdmissionRequest->phone_number)->orWhere('username', $smStudentAdmissionRequest->phone_number);
                    });
                })
                ->when($smStudentAdmissionRequest->filled('email_address') && ! $smStudentAdmissionRequest->phone_number, function ($q) use ($smStudentAdmissionRequest): void {
                    $q->where(function ($q) use ($smStudentAdmissionRequest) {
                        return $q->where('email', $smStudentAdmissionRequest->email_address)->orWhere('username', $smStudentAdmissionRequest->email_address);
                    });
                })
                ->when($smStudentAdmissionRequest->filled('email_address') && $smStudentAdmissionRequest->filled('phone_number'), function ($q) use ($smStudentAdmissionRequest): void {
                    $q->where('phone_number', $smStudentAdmissionRequest->phone_number);
                })

                ->first();
            if ($user && $user->role_id == 2) {
                if (moduleStatusCheck('University')) {
                    $model = StudentRecord::query();
                    $studentRecord = universityFilter($model, $smStudentAdmissionRequest)->first();
                } else {
                    $studentRecord = StudentRecord::where('class_id', $smStudentAdmissionRequest->class)
                        ->where('section_id', $smStudentAdmissionRequest->section)
                        ->where('academic_id', $smStudentAdmissionRequest->session)
                        ->where('student_id', $user->student->id)
                        ->where('school_id', auth()->user()->school_id)
                        ->first();
                }

                if (! $studentRecord) {
                    if ($smStudentAdmissionRequest->edit_info == 'yes') {
                        $this->updateStudentInfo($smStudentAdmissionRequest->merge([
                            'id' => $user->student->id,
                        ]));
                    }

                    $this->insertStudentRecord($smStudentAdmissionRequest->merge([
                        'student_id' => $user->student->id,

                    ]));
                    if (moduleStatusCheck('Lead') == true && $smStudentAdmissionRequest->lead_id) {
                        Lead::where('id', $smStudentAdmissionRequest->lead_id)->update(['is_converted' => 1]);
                        Toastr::success('Operation successful', 'Success');

                        return redirect()->route('lead.index');
                    }

                    if ($smStudentAdmissionRequest->has('parent_registration_student_id') && moduleStatusCheck('ParentRegistration') == true) {
                        $registrationStudent = SmStudentRegistration::find($smStudentAdmissionRequest->parent_registration_student_id);
                        if ($registrationStudent) {
                            $registrationStudent->delete();
                        }

                        Toastr::success('Operation successful', 'Success');

                        return redirect()->route('parentregistration.student-list');
                    }

                    Toastr::success('Operation successful', 'Success');

                    return redirect()->back();

                }

                Toastr::warning('Already Enroll', 'Warning');

                return redirect()->back();
            }
        }

        // end student record

        // staff as parent
        $guardians_phone = $smStudentAdmissionRequest->guardians_phone;
        $guardians_email = $smStudentAdmissionRequest->guardians_email;

        $staffAsParentController = new StaffAsParentController();
        $staff = $staffAsParentController->staff($guardians_email, $guardians_phone, $smStudentAdmissionRequest->staff_parent);
        $exitStaffParent = $staffAsParentController->parent($guardians_email, $guardians_phone);
        // end

        $destination = 'public/uploads/student/document/';
        $student_file_destination = 'public/uploads/student/';

        if ($smStudentAdmissionRequest->relation == 'Father') {
            $guardians_photo = session()->get('fathers_photo');
        } elseif ($smStudentAdmissionRequest->relation == 'Mother') {
            $guardians_photo = session()->get('mothers_photo');
        } else {
            $guardians_photo = session()->get('guardians_photo');
        }

        DB::beginTransaction();

        try {

            if (moduleStatusCheck('University')) {
                $academic_year = UnAcademicYear::find($smStudentAdmissionRequest->un_academic_id);
            } else {
                $academic_year = SmAcademicYear::find($smStudentAdmissionRequest->session);
            }

            $currentLanguage = userLanguage();

            $user_stu = new User();
            $user_stu->role_id = 2;
            $user_stu->full_name = $smStudentAdmissionRequest->first_name.' '.$smStudentAdmissionRequest->last_name;
            $user_stu->username = $smStudentAdmissionRequest->phone_number ?: ($smStudentAdmissionRequest->email_address ?: $smStudentAdmissionRequest->admission_number);
            $user_stu->email = $smStudentAdmissionRequest->email_address;
            $user_stu->phone_number = $smStudentAdmissionRequest->phone_number;
            $user_stu->password = Hash::make(123456);
            $user_stu->language = $currentLanguage;
            $user_stu->school_id = Auth::user()->school_id;
            $user_stu->created_at = $academic_year->year.'-01-01 12:00:00';
            $user_stu->save();
            $user_stu->toArray();

            if (!$smStudentAdmissionRequest->parent_id) {
                $userIdParent = null;
                $hasParent = null;
                if ($smStudentAdmissionRequest->filled('guardians_phone') || $smStudentAdmissionRequest->filled('guardians_email')) {

                    if (! $staff) {
                        $user_parent = new User();
                        $user_parent->role_id = 3;
                        $user_parent->username = $guardians_phone ?: $guardians_email;
                        $user_parent->full_name = $smStudentAdmissionRequest->fathers_name;
                        if (! empty($guardians_email)) {
                            $user_parent->username = $guardians_phone ?: $guardians_email;
                        }

                        $user_parent->email = $guardians_email;
                        $user_parent->phone_number = $guardians_phone;
                        $user_parent->password = Hash::make(123456);
                        $user_parent->language = $currentLanguage;
                        $user_parent->school_id = Auth::user()->school_id;
                        $user_parent->created_at = $academic_year->year.'-01-01 12:00:00';
                        $user_parent->save();
                        $user_parent->toArray();
                    }

                    $userIdParent = $staff ? $staff->user_id : $user_parent->id;
                }

                if ($parentInfo && !$smStudentAdmissionRequest->staff_parent) {

                    $parent = new SmParent();
                    $parent->user_id = $staff ? $staff->user_id : $userIdParent;
                    $parent->fathers_name = $smStudentAdmissionRequest->fathers_name;
                    $parent->fathers_mobile = $smStudentAdmissionRequest->fathers_phone;
                    $parent->fathers_occupation = $smStudentAdmissionRequest->fathers_occupation;
                    $parent->fathers_photo = session()->get('fathers_photo') ?? fileUpload($smStudentAdmissionRequest->file('fathers_photo'), $student_file_destination);
                    $parent->mothers_name = $smStudentAdmissionRequest->mothers_name;
                    $parent->mothers_mobile = $smStudentAdmissionRequest->mothers_phone;
                    $parent->mothers_occupation = $smStudentAdmissionRequest->mothers_occupation;
                    $parent->mothers_photo = session()->get('mothers_photo') ?? fileUpload($smStudentAdmissionRequest->file('mothers_photo'), $student_file_destination);
                    $parent->guardians_name = $smStudentAdmissionRequest->guardians_name;
                    $parent->guardians_mobile = $smStudentAdmissionRequest->guardians_phone;
                    $parent->guardians_email = $smStudentAdmissionRequest->guardians_email;
                    $parent->guardians_occupation = $smStudentAdmissionRequest->guardians_occupation;
                    $parent->guardians_relation = $smStudentAdmissionRequest->relation;
                    $parent->relation = $smStudentAdmissionRequest->relationButton;
                    $parent->guardians_photo = $guardians_photo;
                    $parent->guardians_address = $smStudentAdmissionRequest->guardians_address;
                    $parent->is_guardian = $smStudentAdmissionRequest->is_guardian;
                    $parent->school_id = Auth::user()->school_id;
                    $parent->academic_id = $smStudentAdmissionRequest->session;
                    $parent->created_at = $academic_year->year.'-01-01 12:00:00';
                    $parent->save();
                    $parent->toArray();
                    $hasParent = $parent->id;
                    if ($staff) {
                        $staff->update(['parent_id' => $hasParent]);
                    }
                }
            } else {
                $parent = SmParent::find($smStudentAdmissionRequest->parent_id);
                $hasParent = $parent->id;
            }

            if ($smStudentAdmissionRequest->staff_parent) {
                $hasParent = $staffAsParentController->staffParentStore($staff, $smStudentAdmissionRequest, $academic_year);
                $staff->update(['parent_id' => $hasParent]);
                $parent = SmParent::find($hasParent);
            }

            $smStudent = new SmStudent();
            $smStudent->user_id = $user_stu->id;
            $smStudent->parent_id = $exitStaffParent ? $exitStaffParent->id : (!$smStudentAdmissionRequest->parent_id ? $hasParent : $smStudentAdmissionRequest->parent_id);
            $smStudent->role_id = 2;
            $smStudent->admission_no = $smStudentAdmissionRequest->admission_number;
            if ($smStudentAdmissionRequest->roll_number) {
                $smStudent->roll_no = $smStudentAdmissionRequest->roll_number;
            }

            $smStudent->first_name = $smStudentAdmissionRequest->first_name;
            $smStudent->last_name = $smStudentAdmissionRequest->last_name;
            $smStudent->full_name = $smStudentAdmissionRequest->first_name.' '.$smStudentAdmissionRequest->last_name;
            $smStudent->gender_id = $smStudentAdmissionRequest->gender;
            $smStudent->date_of_birth = date('Y-m-d', strtotime($smStudentAdmissionRequest->date_of_birth));
            $smStudent->caste = $smStudentAdmissionRequest->caste;
            $smStudent->email = $smStudentAdmissionRequest->email_address;
            $smStudent->mobile = $smStudentAdmissionRequest->phone_number;
            $smStudent->admission_date = date('Y-m-d', strtotime($smStudentAdmissionRequest->admission_date));
            $smStudent->student_photo = session()->get('student_photo') ?? fileUpload($smStudentAdmissionRequest->photo, $student_file_destination);
            $smStudent->bloodgroup_id = $smStudentAdmissionRequest->blood_group;
            $smStudent->religion_id = $smStudentAdmissionRequest->religion;
            $smStudent->height = $smStudentAdmissionRequest->height;
            $smStudent->weight = $smStudentAdmissionRequest->weight;
            $smStudent->current_address = $smStudentAdmissionRequest->current_address;
            $smStudent->permanent_address = $smStudentAdmissionRequest->permanent_address;
            $smStudent->route_list_id = $smStudentAdmissionRequest->route;
            $smStudent->dormitory_id = $smStudentAdmissionRequest->dormitory_name;
            $smStudent->room_id = $smStudentAdmissionRequest->room_number;

            if (! empty($smStudentAdmissionRequest->vehicle)) {
                $driver = SmVehicle::where('id', '=', $smStudentAdmissionRequest->vehicle)
                    ->select('driver_id')
                    ->first();
                if (! empty($driver)) {
                    $smStudent->vechile_id = $smStudentAdmissionRequest->vehicle;
                    $smStudent->driver_id = $driver->driver_id;
                }
            }

            $smStudent->national_id_no = $smStudentAdmissionRequest->national_id_number;
            $smStudent->local_id_no = $smStudentAdmissionRequest->local_id_number;
            $smStudent->bank_account_no = $smStudentAdmissionRequest->bank_account_number;
            $smStudent->bank_name = $smStudentAdmissionRequest->bank_name;
            $smStudent->previous_school_details = $smStudentAdmissionRequest->previous_school_details;
            $smStudent->aditional_notes = $smStudentAdmissionRequest->additional_notes;
            $smStudent->ifsc_code = $smStudentAdmissionRequest->ifsc_code;
            $smStudent->document_title_1 = $smStudentAdmissionRequest->document_title_1;
            $smStudent->document_file_1 = fileUpload($smStudentAdmissionRequest->file('document_file_1'), $destination);
            $smStudent->document_title_2 = $smStudentAdmissionRequest->document_title_2;
            $smStudent->document_file_2 = fileUpload($smStudentAdmissionRequest->file('document_file_2'), $destination);
            $smStudent->document_title_3 = $smStudentAdmissionRequest->document_title_3;
            $smStudent->document_file_3 = fileUpload($smStudentAdmissionRequest->file('document_file_3'), $destination);
            $smStudent->document_title_4 = $smStudentAdmissionRequest->document_title_4;
            $smStudent->document_file_4 = fileUpload($smStudentAdmissionRequest->file('document_file_4'), $destination);
            $smStudent->school_id = Auth::user()->school_id;
            $smStudent->academic_id = $smStudentAdmissionRequest->session;
            $smStudent->student_category_id = $smStudentAdmissionRequest->student_category_id;
            $smStudent->student_group_id = $smStudentAdmissionRequest->student_group_id;
            $smStudent->created_at = $academic_year->year.'-01-01 12:00:00';

            if ($smStudentAdmissionRequest->customF) {
                $dataImage = $smStudentAdmissionRequest->customF;
                foreach ($dataImage as $label => $field) {
                    if (is_object($field) && $field != '') {
                        $dataImage[$label] = fileUpload($field, 'public/uploads/customFields/');
                    }
                }

                // Custom Field Start
                $smStudent->custom_field_form_name = 'student_registration';
                $smStudent->custom_field = json_encode($dataImage, true);
                // Custom Field End
            }

            // add by abu nayem for lead convert to student
            if (moduleStatusCheck('Lead')) {
                $smStudent->lead_id = $smStudentAdmissionRequest->lead_id;
                $smStudent->lead_city_id = $smStudentAdmissionRequest->lead_city;
                $smStudent->source_id = $smStudentAdmissionRequest->source_id;
            }

            // end lead convert to student
            $smStudent->save();

            $paren_s = @$parent;
            $childrens = !empty($paren_s) ? $paren_s?->childrens:null;
            Session::put('childrens', $childrens);

            generateQRCode('student-'.$smStudent->id);
            // instert into student define leave
            $st_role_id = 2;
            $school_id = Auth::user()->school_id;
            $academic_id = getAcademicId();
            $user_id = $user_stu->id;

            $existingLeaveDefines = SmLeaveDefine::where('role_id', $st_role_id)
                ->where('school_id', $school_id)
                ->where('academic_id', $academic_id)
                ->get();

            $existingTypes = [];

            foreach ($existingLeaveDefines as $existingLeaveDefine) {
                if (! isset($existingTypes[$existingLeaveDefine->type_id])) {
                    $leaveDefineInstance = new SmLeaveDefine();
                    $leaveDefineInstance->role_id = $st_role_id;
                    $leaveDefineInstance->type_id = $existingLeaveDefine->type_id;
                    $leaveDefineInstance->days = $existingLeaveDefine->days;
                    $leaveDefineInstance->school_id = $school_id;
                    $leaveDefineInstance->user_id = $user_id;

                    if (moduleStatusCheck('University')) {
                        $leaveDefineInstance->un_academic_id = $academic_id;
                    } else {
                        $leaveDefineInstance->academic_id = $academic_id;
                    }

                    $leaveDefineInstance->save();
                    $existingTypes[$existingLeaveDefine->type_id] = true;
                }
            }

            if (! empty($smStudentAdmissionRequest->route) && ! empty($smStudentAdmissionRequest->vehicle)) {
                $data['route'] = $smStudent->route->title;
                $data['vehicle_no'] = $smStudent->vehicle->vehicle_no;
                $this->sent_notifications('Assign_Vehicle', [$user_stu->id], $data, ['Student', 'Parent']);
            }

            if (! empty($smStudentAdmissionRequest->dormitory_name) && ! empty($smStudentAdmissionRequest->room_number)) {
                $data['dormitory'] = $smStudent->dormitory->dormitory_name;
                $data['room'] = $smStudent->room->name;
                $this->sent_notifications('Assign_Dormitory', [$user_stu->id], $data, ['Student', 'Parent']);
            }

            $class_teacher = SmClassTeacher::whereHas('teacherClass', function ($q) use ($smStudentAdmissionRequest): void {
                $q->where('active_status', 1)
                    ->where('class_id', $smStudentAdmissionRequest->class)
                    ->where('section_id', $smStudentAdmissionRequest->section);
            })
                ->where('academic_id', getAcademicId())
                ->where('school_id', auth()->user()->school_id)
                ->first();
            $data['class_id'] = $smStudentAdmissionRequest->class;
            $data['section_id'] = $smStudentAdmissionRequest->section;
            if (! is_null($class_teacher)) {
                $data['teacher_name'] = $class_teacher->teacher->full_name;
                $this->sent_notifications('Student_Admission', (array) $class_teacher->teacher->user_id, $data, ['Teacher']);
            }

            $this->sent_notifications('Student_Admission', [$user_stu->id], $data, ['Student', 'Parent', 'Super admin']);

            $smStudent->toArray();
            if (moduleStatusCheck('Lead') == true) {
                Lead::where('id', $smStudentAdmissionRequest->lead_id)->update(['is_converted' => 1]);
            }

            // insert Into student record
            $this->insertStudentRecord($smStudentAdmissionRequest->merge([
                'student_id' => $smStudent->id,
                'is_default' => 1,

            ]));
            // end insert
            if ($smStudent) {
                $compact['user_email'] = $smStudentAdmissionRequest->email_address;
                $compact['slug'] = 'student';
                $compact['id'] = $smStudent->id;
                @send_mail($smStudentAdmissionRequest->email_address, $smStudentAdmissionRequest->first_name.' '.$smStudentAdmissionRequest->last_name, 'student_login_credentials', $compact);
                @send_sms($smStudentAdmissionRequest->phone_number, 'student_admission', $compact);
            }

            if ($parentInfo && $parent) {
                $compact['user_email'] = $parent->guardians_email;
                $compact['slug'] = 'parent';
                $compact['parent_name'] = $smStudentAdmissionRequest->guardians_name;
                $compact['id'] = $parent->id;
                @send_mail($parent->guardians_email, $smStudentAdmissionRequest->fathers_name, 'parent_login_credentials', $compact);
                @send_sms($smStudentAdmissionRequest->guardians_phone, 'student_admission_for_parent', $compact);
            }

            // add by abu nayem for lead convert to student
            if (moduleStatusCheck('Lead') == true && $smStudentAdmissionRequest->lead_id) {
                $lead = Lead::find($smStudentAdmissionRequest->lead_id);
                $lead->class_id = $smStudentAdmissionRequest->class;
                $lead->section_id = $smStudentAdmissionRequest->section;
                $lead->save();
            }

            // end lead convert to student
            DB::commit();
            if ($smStudentAdmissionRequest->has('parent_registration_student_id') && moduleStatusCheck('ParentRegistration') == true) {

                $registrationStudent = SmStudentRegistration::find($smStudentAdmissionRequest->parent_registration_student_id);
                if ($registrationStudent) {
                    $registrationStudent->delete();
                }

                Toastr::success('Operation successful', 'Success');

                return redirect()->route('parentregistration.student-list');
            }

            if (moduleStatusCheck('Lead') == true && $smStudentAdmissionRequest->lead_id) {
                Toastr::success('Operation successful', 'Success');

                return redirect()->route('lead.index');
            }

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();

        } catch (Exception $exception) {
           
            DB::rollback();
             dd($exception);
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back()->withInput();
        }
    }

    public function edit(Request $request, $id)
    {
        /*
        try {
        */
            $data = static::loadData();
            $data['student'] = SmStudent::with('sections')->select('sm_students.*')->find($id);
            $data['siblings'] = SmStudent::where('parent_id', $data['student']->parent_id)->whereNotNull('parent_id')->where('id', '!=', $id)->get();
            $data['custom_filed_values'] = json_decode($data['student']->custom_field);
            if (moduleStatusCheck('University')) {
                return view('university::admission.edit_student_admission', $data);
            }
            return view('backEnd.studentInformation.student_edit', $data);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function updateStudentInfo($request, $studentRecord = null)
    {

        $parentInfo = $request->fathers_name || $request->fathers_phone || $request->mothers_name || $request->mothers_phone || $request->guardians_email || $request->guardians_phone;
        $student_detail = SmStudent::find($request->id);
        $parentUserId = $student_detail->parents ? $student_detail->parents->user_id : null;
        // custom field validation start
        $validator = Validator::make($request->all(), $this->generateValidateRules('student_registration', $student_detail));
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $error) {
                Toastr::error(str_replace('custom f.', '', $error), 'Failed');
            }

            return redirect()->back()->withInput();
        }

        // custom field validation End

        $destination = 'public/uploads/student/document/';
        $student_file_destination = 'public/uploads/student/';
        $student = SmStudent::find($request->id);
        if ($request->relation == 'Father') {
            $guardians_photo = fileUpdate($student->parents->guardians_photo, $request->fathers_photo, $student_file_destination);
        } elseif ($request->relation == 'Mother') {
            if ($request->mothers_photo != null) {
                $guardians_photo = fileUpdate($student->parents->guardians_photo, $request->mothers_photo, $student_file_destination);
            }
        } elseif ($request->guardians_photo != null) {
            $guardians_photo = fileUpdate($student->parents->guardians_photo, $request->guardians_photo, $student_file_destination);
        }

        DB::beginTransaction();
        try {

            $username = $request->phone_number ?: $request->admission_number;
            $phone_number = $request->phone_number;
            $user_stu = $this->add_user($student_detail->user_id, 2, $username, $request->email_address, $phone_number, $request->first_name.' '.$request->last_name);

            if (($request->sibling_id == 0 || $request->sibling_id == 1) && !$request->parent_id) {
                $username = $request->guardians_phone ?: $request->guardians_email;
                $phone_number = $request->guardians_phone;

                if ($request->guardians_phone || $request->guardians_email) {
                    $user_parent = $this->add_user($parentUserId, 3, $username, $request->guardians_email, $phone_number, $request->guardians_name);
                }
            } elseif ($request->sibling_id == 0 && $request->parent_id ) {
                User::destroy($student_detail->parents->user_id);
            } elseif (($request->sibling_id == 2 || $request->sibling_id == 1) && $request->parent_id) {
            } elseif ($request->sibling_id == 2 && !$request->parent_id ) {
                $username = $request->guardians_phone ?: $request->guardians_email;
                $phone_number = $request->guardians_phone;
                if ($request->guardians_phone || $request->guardians_email) {
                    $user_parent = $this->add_user(null, 3, $username, $request->guardians_email, $phone_number, $request->guardians_name);
                }
            }

            if ($request->sibling_id == 0 && $request->parent_id ) {
                SmParent::destroy($student_detail->parent_id);
            } elseif (($request->sibling_id == 2 || $request->sibling_id == 1) && $request->parent_id) {
            } elseif ($parentInfo) {
                if (($request->sibling_id == 0 || $request->sibling_id == 1) && !$request->parent_id) {
                    // when find parent
                    $parent = $parentUserId ? SmParent::find($student_detail->parent_id) : new SmParent();
                } elseif ($request->sibling_id == 2 && !$request->parent_id) {
                    $parent = new SmParent();
                }

                $parent->user_id = $user_parent->id ?? null;
                $parent->fathers_name = $request->fathers_name;
                $parent->fathers_mobile = $request->fathers_phone;
                $parent->fathers_occupation = $request->fathers_occupation;
                if ($request->fathers_photo != null) {
                    $parent->fathers_photo = fileUpdate($parent->fathers_photo, $request->fathers_photo, $student_file_destination);
                }

                $parent->mothers_name = $request->mothers_name;
                $parent->mothers_mobile = $request->mothers_phone;
                $parent->mothers_occupation = $request->mothers_occupation;
                if ($request->mothers_photo != null) {
                    $parent->mothers_photo = fileUpdate($parent->mothers_photo, $request->mothers_photo, $student_file_destination);
                }

                $parent->guardians_name = $request->guardians_name;
                $parent->guardians_mobile = $request->guardians_phone;
                $parent->guardians_email = $request->guardians_email;
                $parent->guardians_occupation = $request->guardians_occupation;
                $parent->guardians_relation = $request->relation;
                $parent->relation = $request->relationButton;
                if ($request->guardians_photo != null) {
                    $parent->guardians_photo = $guardians_photo;
                }

                $parent->guardians_address = $request->guardians_address;
                $parent->is_guardian = $request->is_guardian;
                $parent->save();
            }

            $student = SmStudent::find($request->id);
            if (($request->sibling_id == 0 || $request->sibling_id == 1) && $request->parent_id == '') {
                $student->parent_id = @$parent->id ?? null;
            } elseif ($request->sibling_id == 0 && $request->parent_id != '') {
                $student->parent_id = $request->parent_id;
            } elseif (($request->sibling_id == 2 || $request->sibling_id == 1) && $request->parent_id != '') {
                $student->parent_id = $request->parent_id;
            } elseif ($request->sibling_id == 2 && $request->parent_id == '') {
                $student->parent_id = $parent->id;
            }

            $student->user_id = $user_stu->id;
            $student->admission_no = $request->admission_number;
            if ($request->roll_number) {
                $student->roll_no = $request->roll_number;
            }

            $student->first_name = $request->first_name;
            $student->last_name = $request->last_name;
            $student->full_name = $request->first_name.' '.$request->last_name;
            $student->gender_id = $request->gender;
            $student->date_of_birth = date('Y-m-d', strtotime($request->date_of_birth));
            $student->age = $request->age;
            $student->caste = $request->caste;
            $student->email = $request->email_address;
            $student->mobile = $request->phone_number;
            $student->admission_date = date('Y-m-d', strtotime($request->admission_date));
            if ($request->photo) {
                $student->student_photo = fileUpdate($student->student_photo, $request->photo, $student_file_destination);
            }

            $student->bloodgroup_id = $request->blood_group;
            $student->religion_id = $request->religion;
            $student->height = $request->height;
            $student->weight = $request->weight;
            $student->current_address = $request->current_address;
            $student->permanent_address = $request->permanent_address;
            $student->student_category_id = $request->student_category_id;
            $student->student_group_id = $request->student_group_id;
            $student->route_list_id = $request->route;
            $student->dormitory_id = $request->dormitory_name;
            $student->room_id = $request->room_number;
            if (! empty($request->vehicle)) {
                $driver = SmVehicle::where('id', '=', $request->vehicle)
                    ->select('driver_id')
                    ->first();
                $student->vechile_id = $request->vehicle;
                $student->driver_id = $driver->driver_id;
            } else {
                $student->vechile_id = null;
                $student->driver_id = null;
            }

            $student->national_id_no = $request->national_id_number;
            $student->local_id_no = $request->local_id_number;
            $student->bank_account_no = $request->bank_account_number;
            $student->bank_name = $request->bank_name;
            $student->previous_school_details = $request->previous_school_details;
            $student->aditional_notes = $request->additional_notes;
            $student->ifsc_code = $request->ifsc_code;
            $student->document_title_1 = $request->document_title_1;
            $student->document_file_1 = fileUpdate($student->document_file_1, $request->file('document_file_1'), $destination);
            $student->document_title_2 = $request->document_title_2;
            $student->document_file_2 = fileUpdate($student->document_file_2, $request->file('document_file_2'), $destination);
            $student->document_title_3 = $request->document_title_3;
            $student->document_file_3 = fileUpdate($student->document_file_3, $request->file('document_file_3'), $destination);
            $student->document_title_4 = $request->document_title_4;
            $student->document_file_4 = fileUpdate($student->document_file_4, $request->file('document_file_4'), $destination);
            if ($request->customF) {
                $dataImage = $request->customF;
                foreach ($dataImage as $label => $field) {
                    if (is_object($field) && $field != '') {
                        $key = '';
                        $maxFileSize = generalSetting()->file_size;
                        $file = $field;
                        $fileSize = filesize($file);
                        $fileSizeKb = ($fileSize / 1000000);
                        if ($fileSizeKb >= $maxFileSize) {
                            Toastr::error('Max upload file size '.$maxFileSize.' Mb is set in system', 'Failed');
                            return redirect()->back();
                        }

                        $file = $field;
                        $key = $file->getClientOriginalName();
                        $file->move('public/uploads/customFields/', $key);
                        $dataImage[$label] = 'public/uploads/customFields/'.$key;
                    }
                }

                // Custom Field Start
                $student->custom_field_form_name = 'student_registration';
                $student->custom_field = json_encode($dataImage, true);
                // Custom Field End
            }

            if (moduleStatusCheck('Lead')) {
                $student->lead_city_id = $request->lead_city;
                $student->source_id = $request->source_id;
            }

            if (! empty($request->route) && ! empty($request->vehicle)) {
                $data['route'] = $student->route->title;
                $data['vehicle_no'] = $student->vehicle->vehicle_no;
                $this->sent_notifications('Assign_Vehicle', [$user_stu->id], $data, ['Student', 'Parent']);
            }

            if (! empty($request->dormitory_name) && ! empty($request->room_number)) {
                $data['dormitory'] = $student->dormitory->dormitory_name;
                $data['room'] = $student->room->name;
                $this->sent_notifications('Assign_Dormitory', [$user_stu->id], $data, ['Student', 'Parent']);
            }

            $student->save();
            if ($studentRecord && generalSetting()->multiple_roll == 0 && $request->roll_number) {
                $studentRecord->update(['roll_no' => $request->roll_number]);
            }

            DB::commit();
        } catch (Exception $exception) {
            DB::rollback();
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

        return null;
    }

    public function update(SmStudentAdmissionRequest $smStudentAdmissionRequest)
    {
        /*
        try {
        */
        $studentRecord = StudentRecord::where('student_id', $smStudentAdmissionRequest->id)->orderBy('created_at')->where('school_id', auth()->user()->school_id)->first();
        if (generalSetting()->multiple_roll == 0 && $smStudentAdmissionRequest->roll_number && $studentRecord) {
            $exitRoll = StudentRecord::where('class_id', $studentRecord->class_id)
                ->where('section_id', $studentRecord->section_id)
                ->where('roll_no', $smStudentAdmissionRequest->roll_number)
                ->where('id', '!=', $studentRecord->id)
                ->where('school_id', auth()->user()->school_id)->first();
            if ($exitRoll) {
                Toastr::error('Sorry! Roll Number Already Exit.', 'Failed');
                return redirect()->route('student_edit', [$smStudentAdmissionRequest->id]);
            }
        }

        $this->updateStudentInfo($smStudentAdmissionRequest, $studentRecord);
        Toastr::success('Operation successful', 'Success');
        return redirect('student-list');
        /*
        } catch (\Throwable $th) {
            throw $th;
            DB::rollback();
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
        */
    }

    private function add_user($user_id, $role_id, $username, $email, $phone_number, $full_name = null)
    {
        /*
        try {
        */
            $user = $user_id == null ? new User() : User::find($user_id);
            $user->role_id = $role_id;
            $user->username = $username;
            $user->email = $email;
            $user->phone_number = $phone_number;
            if ($full_name) {
                $user->full_name = $full_name;
            }
            $user->save();
            return $user;
        /*
        } catch (\Exception $e) {
            Log::info($e->getMessage());
        }
        */
    }

    public function view(Request $request, $id, $type = null)
    {
        
        /*
        try {
        */
            $next_labels = null;
            $student_detail = SmStudent::withoutGlobalScope(StatusAcademicSchoolScope::class)->find($id);
            $records = $student_detail->allRecords;
            $siblings = SmStudent::where('parent_id', '!=', 0)->where('parent_id', $student_detail->parent_id)->where('id', '!=', $id)->status()->withoutGlobalScope(StatusAcademicSchoolScope::class)->get();
            $exams = SmExamSchedule::where('class_id', $student_detail->class_id)
                ->where('section_id', $student_detail->section_id)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $academic_year = $student_detail->academicYear;

            $result_setting = CustomResultSetting::where('school_id', auth()->user()->school_id)->where('academic_id', getAcademicId())->get();

            $grades = SmMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $max_gpa = $grades->max('gpa');

            $fail_gpa = $grades->min('gpa');

            $fail_gpa_name = $grades->where('gpa', $fail_gpa)
                ->first();

            $timelines = SmStudentTimeline::where('staff_student_id', $id)
                ->where('type', 'stu')->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            if (! empty($student_detail->vechile_id)) {
                $driver_id = SmVehicle::where('id', '=', $student_detail->vechile_id)->first();
                $driver_info = SmStaff::where('id', '=', $driver_id->driver_id)->first();
            } else {
                $driver_id = '';
                $driver_info = '';
            }

            $exam_terms = SmExamType::where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $custom_field_data = $student_detail->custom_field;

            $custom_field_values = is_null($custom_field_data) ? null : json_decode($custom_field_data);
            $sessions = SmAcademicYear::get(['id', 'year', 'title']);

            $now = Carbon::now();
            $year = $now->year;
            $month = $now->month;
            $days = cal_days_in_month(CAL_GREGORIAN, $now->month, $now->year);
            $studentRecord = StudentRecord::where('student_id', $student_detail->id)
                ->where('academic_id', getAcademicId())
                ->where('school_id', $student_detail->school_id)
                ->get();
            $attendance = SmStudentAttendance::where('student_id', $student_detail->id)
                ->whereIn('academic_id', $studentRecord->pluck('academic_id'))
                ->whereIn('student_record_id', $studentRecord->pluck('id'))
                ->get();

            $subjectAttendance = SmSubjectAttendance::with('student')
                ->whereIn('academic_id', $studentRecord->pluck('academic_id'))
                ->whereIn('student_record_id', $studentRecord->pluck('id'))
                ->where('school_id', $student_detail->school_id)
                ->get();

            $studentBehaviourRecords = (moduleStatusCheck('BehaviourRecords')) ? AssignIncident::where('student_id', $id)->with('incident', 'user', 'academicYear')->get() : null;
            $behaviourRecordSetting = BehaviourRecordSetting::where('id', 1)->first();

            // generate barcode
            generateQRCode('student-'.$student_detail->id);

            if (moduleStatusCheck('University')) {
                $next_labels = null;
                $assinged_exam_types = [];
                if ($student_detail->defaultClass) {
                    $next_labels = UnSemesterLabel::where('un_department_id', $student_detail->defaultClass->un_department_id)
                        ->where('un_faculty_id', $student_detail->defaultClass->un_faculty_id)
                        ->whereNotIn('id', $student_detail->studentRecords->pluck('un_semester_label_id')->toArray())->get()->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'name' => $item->name,
                                'title' => $item->semesterDetails->name . '[' . $item->academicYearDetails->name . '] ' . $item->name,
                            ];
                        });
                }
                $student_id = $student_detail->id;
                $studentDetails = SmStudent::find($student_id);
                $studentRecordDetails = StudentRecord::where('student_id', $student_id);
                $studentRecords = StudentRecord::where('student_id', $student_id)->distinct('un_academic_id')->get();                
                return view('backEnd.studentInformation.student_view', compact('timelines', 'student_detail', 'driver_info', 'exams', 'siblings', 'grades', 'academic_year', 'exam_terms', 'max_gpa', 'fail_gpa_name', 'custom_field_values', 'sessions', 'records', 'next_labels', 'type', 'studentRecordDetails', 'studentDetails', 'studentRecords', 'result_setting', 'assinged_exam_types', 'studentBehaviourRecords', 'behaviourRecordSetting'));
            } else {
                return view('backEnd.studentInformation.student_view', compact('timelines', 'student_detail', 'driver_info', 'exams', 'siblings', 'grades', 'academic_year', 'exam_terms', 'max_gpa', 'fail_gpa_name', 'custom_field_values', 'sessions', 'records', 'next_labels', 'type', 'result_setting', 'attendance', 'subjectAttendance', 'days', 'year', 'month', 'studentBehaviourRecords', 'behaviourRecordSetting'));
            }

        /*
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function studentDetails(Request $request)
    {
        /*
        try {
        */
            $classes = SmClass::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $students = SmStudent::where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $sessions = SmAcademicYear::where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            return view('backEnd.studentInformation.student_details', ['classes' => $classes, 'sessions' => $sessions]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function settings()
    {
        /*
        try {
        */
            $student_settings = SmStudentRegistrationField::where('school_id', auth()->user()->school_id)->where('active_status', 1)->get()->filter(function ($field): bool {
                return ! $field->admin_section || isMenuAllowToShow($field->admin_section);
            });
            $system_required = $student_settings->whereNotIn('field_name', ['guardians_email', 'email_address'])->where('is_system_required')->pluck('field_name')->toArray();

        return view('backEnd.studentInformation.student_settings', ['student_settings' => $student_settings, 'system_required' => $system_required]);
        /*
        } catch (Throwable $throwable) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function statusUpdate(Request $request)
    {
        $user = auth()->user();
        $field = SmStudentRegistrationField::where('school_id', $user->school_id)
            ->where('id', $request->filed_id)->firstOrFail();
        if ($field) {
            if ($request->type == 'required') {
                $field->is_required = $request->field_status;
            }

            if ($request->type == 'student') {
                $field->student_edit = $request->field_status;
            }

            if ($request->type == 'parent') {
                $field->parent_edit = $request->field_status;
            }

            $field->save();
            Cache::forget('student_field_'.$user->school_id);

            return response()->json(['message' => 'Operation Success']);
        }

        return response()->json(['error' => 'Operation Failed']);
    }

    public function studentFieldShow(Request $request)
    {
        $user = auth()->user();
        $field = SmStudentRegistrationField::where('school_id', $user->school_id)
            ->where('id', $request->filed_id)->firstOrFail();
        if ($field) {
            $field->is_show = $request->field_show;
            if ($field->is_show == 0) {
                $field->is_required = 0;
                $field->student_edit = 0;
                $field->parent_edit = 0;
            }

            $field->save();
            Cache::forget('student_field_'.$user->school_id);

            return response()->json(['message' => 'Operation Success']);
        }

        return response()->json(['error' => 'Operation Failed']);

    }

    public function updateRecord(Request $request): void
    {
        $this->insertStudentRecord($request);
    }

    public function recordStore(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'session' => 'required',
            'class' => 'required',
            'section' => 'required',
        ]);
        if ($validator->fails()) {
            Toastr::error('Please fill up the required fields', 'Failed');

            return redirect()->back()->withErrors($validator)->withInput();
        }
        /*
        try {
        */
            $exitRoll = null;
            if (moduleStatusCheck('University')) {
                $model =  StudentRecord::query();
                $model->where('student_id', $request->student_id);
                $studentRecord = universityFilter($model, $request)->first();
                $pre_record = StudentRecord::where('student_id', $request->student_id)->orderBy('id', 'DESC')->first();
            } else {
                $studentRecord = StudentRecord::where('class_id', $request->class)
                    ->where('section_id', $request->section)
                    ->where('academic_id', $request->session)
                    ->where('student_id', $request->student_id)
                    ->where('school_id', auth()->user()->school_id)
                    ->when($request->shift, function ($query, $shift) {
                        return $query->where('shift_id', $shift);
                    })
                    ->first();

                $pre_record = null;
            }
            if ($studentRecord) {
                Toastr::error('Already Assign', 'Failed');
                return redirect()->back();
            } else {
                $this->insertStudentRecord($request, $pre_record);
            }
            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        /*
        } catch (\Throwable $th) {
            throw $th;
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
        */
    }

    public function insertStudentRecord($request, $pre_record = null): void
    {
        $user = auth()->user();

        if (! $request->filled('is_default') || $request->is_default) {
            StudentRecord::when(moduleStatusCheck('University'), function ($query): void {
                $query->where('un_academic_id', getAcademicId());
            }, function ($query): void {
                $query->where('academic_id', getAcademicId());
            })->where('student_id', $request->student_id)
                ->where('school_id', $user->school_id)->update([
                    'is_default' => 0,
                ]);
        }

        if (generalSetting()->multiple_roll == 0 && $request->roll_number) {

            StudentRecord::where('student_id', $request->student_id)
                ->where('school_id', $user->school_id)
                ->when(moduleStatusCheck('University'), function ($query): void {
                    $query->where('un_academic_id', getAcademicId());
                }, function ($query): void {
                    $query->where('academic_id', getAcademicId());
                })->update([
                    'roll_no' => $request->roll_number,
                ]);
        }

        if ($request->record_id) {
            $studentRecord = StudentRecord::with('studentDetail')->find($request->record_id);
            $groups = \Modules\Chat\Entities\Group::where([
                'class_id' => $studentRecord->class_id,
                'section_id' => $studentRecord->section_id,
                'shift_id' => $studentRecord->shift_id,
                'academic_id' => $studentRecord->academic_id,
                'school_id' => $studentRecord->school_id,
            ])->get();
            if ($studentRecord->studentDetail) {
                $user = $studentRecord->studentDetail->user;
                if ($user) {
                    foreach ($groups as $group) {
                        removeGroupUser($group, $user->id);
                    }
                }
            }
        } else {
            $studentRecord = new StudentRecord;
        }

        $studentRecord->student_id = $request->student_id;
        if ($request->roll_number) {
            $studentRecord->roll_no = $request->roll_number;
        }

        $studentRecord->is_promote = $request->is_promote ?? 0;
        $studentRecord->is_default = ! $request->filled('is_default') || $request->is_default;

        if (moduleStatusCheck('Lead') == true) {
            $studentRecord->lead_id = $request->lead_id;
        }

        if (moduleStatusCheck('University')) {
            $studentRecord->un_academic_id = $request->un_academic_id;
            $studentRecord->un_section_id = $request->un_section_id;
            $studentRecord->un_session_id = $request->un_session_id;
            $studentRecord->un_department_id = $request->un_department_id;
            $studentRecord->un_faculty_id = $request->un_faculty_id;
            $studentRecord->un_semester_id = $request->un_semester_id;
            $studentRecord->un_semester_label_id = $request->un_semester_label_id;
        } else {
            $studentRecord->class_id = $request->class;
            $studentRecord->section_id = $request->section;
            $studentRecord->shift_id = shiftEnable() ? $request->shift : '';
            $studentRecord->session_id = $request->session;
        }

        $studentRecord->school_id = $user->school_id;
        $studentRecord->academic_id = $request->session;

        $studentRecord->save();

        if (moduleStatusCheck('University')) {
            $subjectIds = [];
            $this->assignSubjectStudent($studentRecord, $subjectIds, $pre_record);
        }

        if (directFees()) {
            $this->assignDirectFees($studentRecord->id, $studentRecord->class_id, $studentRecord->section_id, null);
        }

        $groups = \Modules\Chat\Entities\Group::where([
            'class_id' => $request->class,
            'section_id' => $request->section,
            'shift_id' => shiftEnable() ? $request->shift : '',
            'academic_id' => $request->session,
            'school_id' => $user->school_id,
        ])->get();
        $student = SmStudent::where('school_id', $user->school_id)->find($request->student_id);

        if ($student) {
            $student->roll_no = $request->roll_number;
            $student->save();
            $user = $student->user;
            foreach ($groups as $group) {
                createGroupUser($group, $user->id, 2, $user->id);
            }
        }
    }

    public function assignClass($id)
    {
        $data['schools'] = SmSchool::get();
        $data['sessions'] = SmAcademicYear::get(['id', 'year', 'title']);
        $data['student_records'] = StudentRecord::where('student_id', $id)->where('active_status', 1)
            ->when(moduleStatusCheck('University'), function ($query): void {
                $query->whereNull('class_id');
            })->get();
        $data['student_detail'] = SmStudent::where('id', $id)->first();
        $data['classes'] = SmClass::get(['id', 'class_name']);
        $data['siblings'] = SmStudent::where('parent_id', $data['student_detail']->parent_id)->whereNotNull('parent_id')->where('id', '!=', $id)->status()->withoutGlobalScope(StatusAcademicSchoolScope::class)->get();
        return view('backEnd.studentInformation.assign_class', $data);
    }

    public function recordEdit($student_id, $record_id)
    {
        $data['schools'] = SmSchool::get();
        $data['record'] = StudentRecord::where('id', $record_id)->first();
        $data['editData'] = $data['record'];
        $data['modelId'] = $data['record'];
        $data['sessions'] = SmAcademicYear::get(['id', 'year', 'title']);
        $data['student_records'] = StudentRecord::where('student_id', $student_id)->get();
        $data['student_detail'] = SmStudent::where('id', $student_id)->first();
        $data['classes'] = SmClass::get(['id', 'class_name']);
        $data['siblings'] = SmStudent::where('parent_id', $data['student_detail']->parent_id)->where('id', '!=', $student_id)->status()->withoutGlobalScope(StatusAcademicSchoolScope::class)->get();
        if (moduleStatusCheck('University')) {
            $interface = App::make(UnCommonRepositoryInterface::class);
            $data += $interface->getCommonData($data['record']);
        }

        return view('backEnd.studentInformation.assign_class_edit', $data);
    }

    public function recordUpdate(Request $request)
    {
        /*
        try {
        */
            $user = auth()->user();
            $exitRoll = null;
            if (moduleStatusCheck('University')) {
                $studentRecord = StudentRecord::where('un_faculty_id', $request->un_faculty_id)
                    ->where('un_department_id', $request->un_department_id)
                    ->where('un_academic_id', $request->un_academic_id)
                    ->where('un_semester_id', $request->un_semester_id)
                    ->where('un_semester_label_id', $request->un_semester_label_id)
                    ->where('un_academic_id', $request->un_academic_id)
                    ->where('student_id', $request->student_id)
                    ->where('id', '!=', $request->record_id)
                    ->where('school_id', $user->school_id)
                    ->first();
            } else {
                $studentRecord = StudentRecord::where('class_id', $request->class)
                    ->where('section_id', $request->section)
                    ->where('academic_id', $request->session)
                    ->where('student_id', $request->student_id)
                    ->where('id', '!=', $request->record_id)
                    ->where('school_id', $user->school_id)
                    ->when($request->shift, function ($query, $shift) {
                        return $query->where('shift_id', $shift);
                    })
                    ->first();
            }
            if ($studentRecord) {
                Toastr::error('Already Assign', 'Failed');
                return redirect()->back();
            } else {
                $this->insertStudentRecord($request);
                if (directFees() && $studentRecord) {
                    $this->assignDirectFees($studentRecord->id, $studentRecord->class_id, $studentRecord->section_id, null);
                } else {
                    $studentRecord = StudentRecord::find($request->record_id);
                    $this->assignDirectFees($studentRecord->id, $studentRecord->class_id, $studentRecord->section_id, null);
                }
            }
            Toastr::success('Operation successful', 'Success');
            return redirect()->back();
        /*
        } catch (\Throwable $th) {
            throw $th;
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
        */
    }

    public function checkExitStudent(Request $request)
    {
        /*
        try {
        */
            $email = $request->email;
            $phone = $request->phone;
            $student = null;
            if ($email || $phone) {
                $x_student = SmStudent::query();
                if ($email && $phone) {
                    $x_student->where('mobile', $phone);
                } elseif ($email) {
                    $x_student->where('email', $email);
                } elseif ($phone) {
                    $x_student->where('mobile', $phone);
                }

                $student = $x_student->first();
            }

            return response()->json(['student' => $student]);
        /*
        } catch (Exception $exception) {
            return response()->json('', 404);
        }
        */
    }

    public function assignSubjectStudent($studentRecord, $subjectIds = null, $pre_record = null): ?bool
    {

        if (! $studentRecord) {
            return false;
        }

        if ($subjectIds) {
            $assignSubjects = UnSubject::whereIn('id', $subjectIds)
                ->where('school_id', auth()->user()->school_id)
                ->get()->map(function ($item, $key): array {
                    return [
                        'un_subject_id' => $item->id,

                    ];
                });
        } else {
            $assignSubjects = UnAssignSubject::where('un_semester_label_id', $studentRecord->un_semester_label_id)
                ->where('school_id', auth()->user()->school_id)->get()->map(function ($item, $key): array {
                    return [
                        'un_subject_id' => $item->un_subject_id,
                    ];
                });
        }

        if ($assignSubjects) {
            foreach ($assignSubjects as $assignSubject) {
                $studentSubject = new UnSubjectAssignStudent;
                $studentSubject->student_record_id = $studentRecord->id;
                $studentSubject->student_id = $studentRecord->student_id;
                $studentSubject->un_academic_id = $studentRecord->un_academic_id;
                $studentSubject->un_semester_id = $studentRecord->un_semester_id;
                $studentSubject->un_semester_label_id = $studentRecord->un_semester_label_id;
                $studentSubject->un_subject_id = $assignSubject['un_subject_id'];
                $result = $studentSubject->save();
                if ($result) {
                    $this->assignSubjectFees($studentRecord->id, $assignSubject['un_subject_id'], $studentRecord->un_semester_label_id);
                }
            }

            $have_credit = $studentRecord->student->feesCredits->sum('amount');
            if ($have_credit) {
                $this->adjustCreditWithFees($studentRecord->id);
            }
        }

        if ($pre_record) {
            $preSubjects = UnSubjectAssignStudent::where('student_record_id', $pre_record->id)
                ->where('un_semester_label_id', $pre_record->un_semester_label_id)
                ->where('student_id', $pre_record->student_id)
                ->where('un_academic_id', $pre_record->un_academic_id)
                ->where('un_semester_id', $pre_record->un_semester_id)
                ->get();
            foreach ($preSubjects as $preSubject) {
                $result = labelWiseStudentResult($pre_record, $preSubject->un_subject_id);
                $completeSubject = new UnSubjectComplete();
                $completeSubject->student_id = $pre_record->student_id;
                $completeSubject->student_record_id = $pre_record->id;
                $completeSubject->un_semester_label_id = $pre_record->un_semester_label_id;
                $completeSubject->un_subject_id = $preSubject->un_subject_id;
                $completeSubject->un_academic_id = $pre_record->un_academic_id;
                $completeSubject->is_pass = $result['result'];
                $completeSubject->total_mark = $result['total_mark'];
                $completeSubject->save();
            }
        }

        return null;
    }

    public function getSchool(Request $request)
    {
        /*
        try {
        */
            $academic_years = SmAcademicYear::where('school_id', $request->school_id)->get();

            return response()->json([$academic_years]);
        /*
        } catch (Exception $exception) {
            return response()->json('', 404);
        }
        */
    }

    public function deleteRecord(Request $request)
    {
        /*
        try {
        */
            $record = StudentRecord::with('studentDetail')->where('id', $request->record_id)
                ->where('student_id', $request->student_id)
                ->first();
            $type = $request->type ? 'delete' : 'disable';

            
            // code...

            if ($record && $type == 'delete') {
                $groups = \Modules\Chat\Entities\Group::where([
                    'class_id' => $record->class_id,
                    'section_id' => $record->section_id,
                    'shift_id' => $record->shift_id,
                    'academic_id' => $record->academic_id,
                    'school_id' => $record->school_id,
                ])->get();
                if ($record->studentDetail) {
                    $user = $record->studentDetail->user;
                    if ($user) {
                        foreach ($groups as $group) {
                            removeGroupUser($group, $user->id);
                        }
                    }
                }

                $record->delete();
            }else{
                $studentMultiRecordController = new StudentMultiRecordController();
            $studentMultiRecordController->deleteRecordCondition($record->student_id, $record->id, $type);
            }

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        /*
        } catch (Throwable $throwable) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function importStudent()
    {
        try {
            // start check student limitation for subscription
            if (isSubscriptionEnabled()) {

                $active_student = SmStudent::where('school_id', Auth::user()->school_id)->where('active_status', 1)->count();

                if (\Modules\Saas\Entities\SmPackagePlan::student_limit() <= $active_student && saasDomain() !== 'school') {

                    Toastr::error('Your student limit has been crossed.', 'Failed');

                    return redirect()->back();
                }
            }

            // End check student limitation for subscription

            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $genders = SmBaseSetup::where('base_group_id', 1)->where('school_id', Auth::user()->school_id)->get();
            $blood_groups = SmBaseSetup::where('base_group_id', 3)->where('school_id', Auth::user()->school_id)->get();
            $religions = SmBaseSetup::where('base_group_id', 2)->where('school_id', Auth::user()->school_id)->get();
            $sessions = SmAcademicYear::where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.studentInformation.import_student', ['classes' => $classes, 'genders' => $genders, 'blood_groups' => $blood_groups, 'religions' => $religions, 'sessions' => $sessions]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function studentBulkStore(Request $request)
    {
        ini_set('max_execution_time', 0);
        $file = $request->file('file');
        $step = $request->get('step', 'upload');

        if ($step === 'upload' || $step === 'map') {
            $validate_rules = [
                'file' => 'required|mimes:csv,xls,xlsx|max:2048',
            ];
        } elseif ($step === 'import') {
            $validate_rules = [
                'index' => ['required', 'array'],
            ];
        } else {
            $validate_rules = [
            ];
        }

        $request->validate($validate_rules, validationMessage($validate_rules));

        $expectedHeaders = [
            'Session',
            'Admission Number',
            'Roll No',
            'First Name',
            'Last Name',
            'Date of Birth',
            'Gender',
            'Caste',
            'Mobile',
            'Email',
            'Admission Date',
            'Blood Group',
            'Height',
            'Weight',
            'Father Name',
            'Father Phone',
            'Father Occupation',
            'Mother Name',
            'Mother Phone',
            'Mother Occupation',
            'Guardian Name',
            'Guardian Relation',
            'Guardian Email',
            'Guardian Phone',
            'Guardian Occupation',
            'Current Address',
            'Bank Account No',
            'Bank Name',
            'National Identification No',
            'Previous School Details',
            'Note',
            'Religion',
        ];

        $requiredHeaders = [
            'admission_number',
            'first_name',
            'last_name',
            'mobile',
            'email',
            'date_of_birth',
            'gender',
            'guardian_email',
        ];
        if(moduleStatusCheck('University'))
        {
            $session_id = $request->session_id;
            $faculty_id = $request->faculty_id;
            $dept_id = $request->dept_id;
            $academic_id = $request->academic_id;
            $semester_id = $request->semester_id;
            $semester_label_id = $request->semester_label_id;
            $section_id = $request->section_id;
        }else{
            $session = SmAcademicYear::findOrFail($request->session_id);
            $class = SmClass::findOrFail($request->class_id);
            $section = SmSection::findOrFail($request->section_id);
            if(shiftEnable())
            {
                $shift = Shift::findOrFail($request->shift_id);
            }else{
                $shift = '';
            }
        }

        if ($step === 'upload') {
            $headers = (new HeadingRowImport())->toArray($file);

            $headers = $headers[0][0];
            $filteredHeaders = array_filter($headers, fn ($header): bool => ! is_numeric($header));
            $filteredHeaders = array_values($filteredHeaders);
            if(moduleStatusCheck('University')){
                return view('backEnd.partials.student-import._map', ['filteredHeaders' => $filteredHeaders, 'expectedHeaders' => $expectedHeaders, 'file' => $file, 'session_id' => $session_id, 'faculty_id' => $faculty_id, 'dept_id' => $dept_id, 'academic_id' => $academic_id, 'semester_id' => $semester_id, 'semester_label_id' => $semester_label_id, 'section_id' => $section_id]);
            }else{
                return view('backEnd.partials.student-import._map', ['filteredHeaders' => $filteredHeaders, 'expectedHeaders' => $expectedHeaders, 'file' => $file, 'session' => $session, 'class' => $class, 'section' => $section, 'shift' => $shift]);
            }
        }
        if ($step === 'map') {
            $allData = Excel::toArray(new BulkImport(), $file)[0];
            $mappedHeaders = json_decode((string) $request->get('headers'));
            $url = route('student_bulk_store');

            if(moduleStatusCheck('University')){
                return view('backEnd.partials.student-import._import', ['expectedHeaders' => $expectedHeaders, 'allData' => $allData, 'mappedHeaders' => $mappedHeaders, 'url' => $url, 'requiredHeaders' => $requiredHeaders, 'session_id' => $session_id, 'faculty_id' => $faculty_id, 'dept_id' => $dept_id, 'academic_id' => $academic_id, 'semester_id' => $semester_id, 'semester_label_id' => $semester_label_id, 'section_id' => $section_id]);
            }else{
                return view('backEnd.partials.student-import._import', ['expectedHeaders' => $expectedHeaders, 'allData' => $allData, 'mappedHeaders' => $mappedHeaders, 'url' => $url, 'requiredHeaders' => $requiredHeaders, 'session' => $session, 'class' => $class, 'section' => $section, 'shift' => $shift]);
            }
        }

        if ($step == 'import') {
            DB::beginTransaction();
            try {
                $count = count($request->admission_number);
                $school_id = Auth::user()->school_id;
                $session_id = $request->session_id;

                for ($i = 0; $i < $count; $i++) {
                    $admission_number = $request->admission_number[$i];
                    $email = $request->email[$i];
                    $guardian_email = $request->guardian_email[$i];

                    // Admission number uniqueness check
                    if (SmStudent::where('admission_no', $admission_number)->where('school_id', $school_id)->exists()) {
                        Toastr::error("Duplicate admission number: $admission_number", 'Failed');
                        DB::rollBack();
                        return redirect()->back();
                    }

                    // Student email uniqueness
                    if (!empty($email) && SmStudent::where('email', $email)->where('school_id', $school_id)->exists()) {
                        Toastr::error("Duplicate student email: $email", 'Failed');
                        DB::rollBack();
                        return redirect()->back();
                    }

                    // Guardian email uniqueness
                    if (!empty($guardian_email) && SmParent::where('guardians_email', $guardian_email)->where('school_id', $school_id)->exists()) {
                        Toastr::error("Duplicate guardian email: $guardian_email", 'Failed');
                        DB::rollBack();
                        return redirect()->back();
                    }

                    // Subscription limit check
                    if (isSubscriptionEnabled()) {
                        $active_student = SmStudent::where('school_id', auth()->user()->school_id)->count();

                        if (\Modules\Saas\Entities\SmPackagePlan::staff_limit() <= $active_student && saasDomain() != 'school') {
                            DB::rollBack();
                            Toastr::error('Your staff limit has been exceeded.', 'Failed');
                            return redirect()->route('staff_directory');
                        }
                    }

                    // Create Student User
                    $user = new User();
                    $user->role_id = 2;
                    $user->full_name = $request->first_name[$i] . ' ' . $request->last_name[$i];
                    $user->username = $request->mobile[$i] ?? $request->email[$i] ?? $admission_number;
                    $user->email = $email;
                    $user->phone_number = $request->mobile[$i] ?? null;
                    $user->school_id = $school_id;
                    $user->password = Hash::make('123456');
                    $user->save();

                    $user_id = $user->id;

                    // Create Guardian User & Parent
                    $parent_id = null;
                    if (!empty($guardian_email) || !empty($request->guardian_phone[$i])) {
                        $guardianUser = new User();
                        $guardianUser->role_id = 3;
                        $guardianUser->full_name = $request->father_name[$i] ?? null;
                        $guardianUser->username = $request->guardian_phone[$i] ?? ('par_' . $admission_number);
                        $guardianUser->email = $guardian_email;
                        $guardianUser->password = Hash::make('123456');
                        $guardianUser->school_id = $school_id;
                        $guardianUser->save();

                        $guardian_user_id = $guardianUser->id;

                        $parent = new SmParent();
                        $parent->user_id = $guardian_user_id;
                        $parent->fathers_name = $request->father_name[$i] ?? null;
                        $parent->fathers_mobile = $request->father_phone[$i] ?? null;
                        $parent->fathers_occupation = $request->father_occupation[$i] ?? null;
                        $parent->mothers_name = $request->mother_name[$i] ?? null;
                        $parent->mothers_mobile = $request->mother_phone[$i] ?? null;
                        $parent->mothers_occupation = $request->mother_occupation[$i] ?? null;
                        $parent->guardians_name = $request->guardian_name[$i] ?? null;
                        $parent->guardians_mobile = $request->guardian_phone[$i] ?? null;
                        $parent->guardians_occupation = $request->guardian_occupation[$i] ?? null;
                        $parent->guardians_address = $request->guardian_address[$i] ?? null;
                        $parent->guardians_email = $guardian_email;
                        $parent->school_id = $school_id;
                        $parent->academic_id = $session_id;
                        $parent->relation = $request->guardian_relation[$i] ?? null;

                        $relation = match($request->guardian_relation[$i]) {
                            'F' => 'Father',
                            'M' => 'Mother',
                            default => 'Other',
                        };

                        $parent->guardians_relation = $relation ?? null;
                        $parent->save();

                        $parent_id = $parent->id;
                    }

                    // Format dates from Excel
                    try {
                        $dob = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($request->date_of_birth[$i])->format('Y-m-d');
                        $admission_date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($request->admission_date[$i])->format('Y-m-d');
                    } catch (\Exception $dateEx) {
                        throw new \Exception("Invalid date format at row $i");
                    }

                    // Create Student
                    $student = new SmStudent();
                    $student->user_id = $user_id;
                    $student->parent_id = $parent_id;
                    $student->role_id = 2;
                    $student->admission_no = $admission_number;
                    $student->roll_no = $request->roll_no[$i] ?? null;
                    $student->first_name = $request->first_name[$i] ?? null;
                    $student->last_name = $request->last_name[$i] ?? null;
                    $student->full_name = $request->first_name[$i] . ' ' . $request->last_name[$i];
                    $student->gender_id = $request->gender[$i] ?? null;
                    $student->date_of_birth = $dob;
                    $student->caste = $request->caste[$i] ?? null;
                    $student->email = $email;
                    $student->mobile = $request->mobile[$i] ?? null;
                    $student->admission_date = $admission_date;
                    $student->bloodgroup_id = $request->blood_group[$i] ?? null;
                    $student->height = $request->height[$i] ?? null;
                    $student->weight = $request->weight[$i] ?? null;
                    $student->school_id = $school_id;
                    $student->academic_id = $session_id;
                    $student->save();

                    // Insert Student Record
                    $recordData = new \Illuminate\Http\Request([
                        'student_id' => $student->id,
                        'class_id' => $request->class_id,
                        'section_id' => $request->section_id,
                        'shift_id' => $request->shift_id ?? null,
                        'academic_id' => $session_id,
                        'is_default' => 1,
                        'roll_number' => $request->roll_no[$i],
                    ]);
                    $this->insertStudentRecord($recordData);
                }

                DB::commit();
                Toastr::success('Students imported successfully.', 'Success');
                return redirect()->back();
            } catch (\Exception $e) {
                DB::rollBack();
                Toastr::error('Import failed: ' . $e->getMessage(), 'Error');
                return redirect()->back();
            }
}

    }
    
    // public function ab(Request $request)
    // {


    //     /*
    //         try {
    //         */
    //         DB::beginTransaction();
    //         $path = $request->file('file');
    //         Excel::import(new StudentsImport, $request->file('file'), \Maatwebsite\Excel\Excel::XLSX);
    //         $data = StudentBulkTemporary::where('user_id', Auth::user()->id)->where('email', '!=', '')->get();

    //         $emailCounts = $data->groupBy('email')->map(function ($rows) {
    //             return $rows->count();
    //         });

    //         $duplicateEmails = $emailCounts->filter(function ($count, $row): bool {
    //             return $count > 1;
    //         });

    //         if ($duplicateEmails->isNotEmpty()) {
    //             foreach ($duplicateEmails as $email => $count) {
    //                 toastr()->error('Duplicate email found: '.$email);
    //             }

    //             StudentBulkTemporary::where('user_id', Auth::user()->id)->delete();

    //             return redirect()->back();
    //         }

    //         $data = StudentBulkTemporary::where('user_id', Auth::user()->id)->get();
    //         $shcool_details = SmGeneralSettings::where('school_id', auth()->user()->school_id)->first();
    //         $school_name = explode(' ', $shcool_details->school_name);
    //         $short_form = '';
    //         foreach ($school_name as $value) {
    //             $ch = mb_str_split($value);
    //             $short_form = $short_form.''.$ch[0];
    //         }

    //         if (! empty($data)) {
    //             foreach ($data as $value) {

    //                 if (isSubscriptionEnabled()) {

    //                     $active_student = SmStudent::where('school_id', Auth::user()->school_id)->where('active_status', 1)->count();

    //                     if (SmPackagePlan::student_limit() <= $active_student && saasDomain() != 'school') {

    //                         DB::commit();
    //                         StudentBulkTemporary::where('user_id', Auth::user()->id)->delete();
    //                         Toastr::error('Your student limit has been crossed.', 'Failed');

    //                         return redirect('student-list');
    //                     }
    //                 }

    //                 $ad_check = SmStudent::where('admission_no', (int) $value->admission_number)->where('school_id', Auth::user()->school_id)->get();
    //                 //  return $ad_check;

    //                 if ($ad_check->count() > 0) {
    //                     if ($value->phone_number || $value->email) {
    //                         $user = User::when($value->phone_number && ! $value->email, function ($q) use ($value): void {
    //                             $q->where('phone_number', $value->phone_number)->orWhere('username', $value->phone_number);
    //                         })
    //                             ->when($value->email && ! $value->phone_number, function ($q) use ($value): void {
    //                                 $q->where('email', $value->email)->orWhere('username', $value->email);
    //                             })
    //                             ->when($value->email && $value->phone_number, function ($q) use ($value): void {
    //                                 $q->where('email', $value->email);
    //                             })->first();
    //                         if ($user && $user->role_id == 2) {
    //                             if (moduleStatusCheck('University')) {
    //                                 $model = StudentRecord::query();
    //                                 $studentRecord = universityFilter($model, $request)->first();
    //                             } else {
    //                                 $studentRecord = StudentRecord::where('class_id', $request->class)
    //                                     ->when($request->shift, function($query) use ($request) {
    //                                         $query->where('shift_id', $request->shift);
    //                                     })
    //                                     ->where('section_id', $request->section)
    //                                     ->where('academic_id', $request->session)
    //                                     ->where('student_id', $user->student->id)
    //                                     ->when(shiftEnable() && !empty($request->shift),function($query) use($request){
    //                                         $query->where('shift_id',$request->shift);
    //                                     })
    //                                     ->where('school_id', auth()->user()->school_id)
    //                                     ->first();
    //                             }

    //                             if (! $studentRecord) {
    //                                 $this->insertStudentRecord($request->merge([
    //                                     'student_id' => $user->student->id,
    //                                     'roll_number' => $request->roll_no,
    //                                 ]));
    //                             }
    //                         }
    //                     }

    //                     DB::rollback();
    //                     StudentBulkTemporary::where('user_id', Auth::user()->id)->delete();
    //                     Toastr::error('Admission number should be unique.', 'Failed');

    //                     return redirect()->back();
    //                 }

    //                 if ($value->email != '') {
    //                     $chk = DB::table('sm_students')->where('email', $value->email)->where('school_id', Auth::user()->school_id)->count();
    //                     if ($chk >= 1) {
    //                         DB::rollback();
    //                         StudentBulkTemporary::where('user_id', Auth::user()->id)->delete();
    //                         Toastr::error('Student Email address should be unique.', 'Failed');

    //                         return redirect()->back();
    //                     }
    //                 }

    //                 if ($value->guardian_email != '') {
    //                     $chk = DB::table('sm_parents')->where('guardians_email', $value->guardian_email)->where('school_id', Auth::user()->school_id)->count();
    //                     if ($chk >= 1) {
    //                         DB::rollback();
    //                         StudentBulkTemporary::where('user_id', Auth::user()->id)->delete();
    //                         Toastr::error('Guardian Email address should be unique.', 'Failed');

    //                         return redirect()->back();
    //                     }
    //                 }

    //                 $parentInfo = $value->father_name || $value->father_phone || $value->mother_name || $value->mother_phone || $value->guardian_email || $value->guardian_phone;
    //                 try {

    //                     if ($value->admission_number == null) {
    //                         continue;
    //                     }

    //                     $academic_year = moduleStatusCheck('University')
    //                         ? UnAcademicYear::find($request->un_session_id) : SmAcademicYear::find($request->session);

    //                     $user_stu = new User();
    //                     $user_stu->role_id = 2;
    //                     $user_stu->full_name = $value->first_name.' '.$value->last_name;
    //                     $user_stu->username = $value->mobile ?: ($value->email ?: $value->admission_number);
    //                     $user_stu->email = $value->email;
    //                     $user_stu->phone_number = $value->mobile ?? null;
    //                     $user_stu->school_id = Auth::user()->school_id;
    //                     $user_stu->password = Hash::make(123456);
    //                     $user_stu->created_at = $academic_year->year.'-01-01 12:00:00';
    //                     $user_stu->save();

    //                     $user_stu->toArray();

    //                     try {
    //                         $userIdParent = null;
    //                         $hasParent = null;
    //                         if ($value->guardian_email || $value->guardian_phone) {
    //                             $user_parent = new User();
    //                             $user_parent->role_id = 3;
    //                             $user_parent->full_name = $value->father_name;

    //                             if (empty($value->guardian_email)) {
    //                                 $data_parent['email'] = 'par_'.$value->admission_number;

    //                                 $user_parent->username = $value->guardian_phone ?: 'par_'.$value->admission_number;
    //                             } else {

    //                                 $data_parent['email'] = $value->guardian_email;

    //                                 $user_parent->username = $value->guardian_phone ?: $value->guardian_email;
    //                             }

    //                             $user_parent->email = $value->guardian_email;

    //                             $user_parent->password = Hash::make(123456);
    //                             $user_parent->school_id = Auth::user()->school_id;

    //                             $user_parent->created_at = $academic_year->year.'-01-01 12:00:00';

    //                             $user_parent->save();
    //                             $user_parent->toArray();
    //                             $userIdParent = $user_parent->id;
    //                         }

    //                         try {
    //                             if ($parentInfo) {
    //                                 $parent = new SmParent();

    //                                 if (
    //                                     $value->relation == 'F' ||
    //                                     $value->guardian_relation == 'F' ||
    //                                     $value->guardian_relation == 'F' ||
    //                                     mb_strtolower($value->guardian_relation) == 'father' ||
    //                                     mb_strtolower($value->guardian_relation) == 'father'
    //                                 ) {
    //                                     $relationFull = 'Father';
    //                                     $relation = 'F';
    //                                 } elseif (
    //                                     $value->relation == 'M' ||
    //                                     $value->guardian_relation == 'M' ||
    //                                     $value->guardian_relation == 'M' ||
    //                                     mb_strtolower($value->guardian_relation) == 'mother' ||
    //                                     mb_strtolower($value->guardian_relation) == 'mother'
    //                                 ) {
    //                                     $relationFull = 'Mother';
    //                                     $relation = 'M';
    //                                 } else {
    //                                     $relationFull = 'Other';
    //                                     $relation = 'O';
    //                                 }

    //                                 $parent->guardians_relation = $relationFull;
    //                                 $parent->relation = $relation;

    //                                 $parent->user_id = $userIdParent;
    //                                 $parent->fathers_name = $value->father_name;
    //                                 $parent->fathers_mobile = $value->father_phone;
    //                                 $parent->fathers_occupation = $value->fathe_occupation;
    //                                 $parent->mothers_name = $value->mother_name;
    //                                 $parent->mothers_mobile = $value->mother_phone;
    //                                 $parent->mothers_occupation = $value->mother_occupation;
    //                                 $parent->guardians_name = $value->guardian_name;
    //                                 $parent->guardians_mobile = $value->guardian_phone;
    //                                 $parent->guardians_occupation = $value->guardian_occupation;
    //                                 $parent->guardians_address = $value->guardian_address;
    //                                 $parent->guardians_email = $value->guardian_email;
    //                                 $parent->school_id = Auth::user()->school_id;
    //                                 $parent->academic_id = $request->session;

    //                                 $parent->created_at = $academic_year->year.'-01-01 12:00:00';

    //                                 $parent->save();
    //                                 $parent->toArray();
    //                                 $hasParent = $parent->id;
    //                             }

    //                             try {
    //                                 $student = new SmStudent();
    //                                 // $student->siblings_id = $value->sibling_id;
    //                                 // $student->class_id = $request->class;
    //                                 // $student->section_id = $request->section;
    //                                 $student->session_id = $request->session;
    //                                 $student->user_id = $user_stu->id;

    //                                 $student->parent_id = $hasParent ? $parent->id : null;
    //                                 $student->role_id = 2;

    //                                 $student->admission_no = $value->admission_number;
    //                                 $student->roll_no = $value->roll_no;
    //                                 $student->first_name = $value->first_name;
    //                                 $student->last_name = $value->last_name;
    //                                 $student->full_name = $value->first_name.' '.$value->last_name;
    //                                 $student->gender_id = $value->gender;
    //                                 $student->date_of_birth = date('Y-m-d', strtotime($value->date_of_birth));
    //                                 $student->caste = $value->caste;
    //                                 $student->email = $value->email;
    //                                 $student->mobile = $value->mobile;
    //                                 $student->admission_date = date('Y-m-d', strtotime($value->admission_date));
    //                                 $student->bloodgroup_id = $value->blood_group;
    //                                 $student->religion_id = $value->religion;
    //                                 $student->height = $value->height;
    //                                 $student->weight = $value->weight;
    //                                 $student->current_address = $value->current_address;
    //                                 $student->permanent_address = $value->permanent_address;
    //                                 $student->national_id_no = $value->national_identification_no;
    //                                 $student->local_id_no = $value->local_identification_no;
    //                                 $student->bank_account_no = $value->bank_account_no;
    //                                 $student->bank_name = $value->bank_name;
    //                                 $student->previous_school_details = $value->previous_school_details;
    //                                 $student->aditional_notes = $value->note;
    //                                 $student->school_id = Auth::user()->school_id;
    //                                 $student->academic_id = $request->session;
    //                                 if (moduleStatusCheck('University')) {

    //                                     $student->un_academic_id = $request->un_academic_id;
    //                                 }

    //                                 $student->created_at = $academic_year->year.'-01-01 12:00:00';
    //                                 $student->save();
    //                                 $this->insertStudentRecord($request->merge([
    //                                     'student_id' => $student->id,
    //                                     'is_default' => 1,
    //                                     'roll_number' => $value->roll_no,
    //                                 ]));

    //                                 $user_info = [];

    //                                 if ($value->email != '') {
    //                                     $user_info[] = ['email' => $value->email, 'username' => $value->email];
    //                                 }

    //                                 if ($value->guardian_email != '') {
    //                                     $user_info[] = ['email' => $value->guardian_email, 'username' => $data_parent['email']];
    //                                 }
    //                             } catch (\Illuminate\Database\QueryException|Exception $e) {
    //                                 DB::rollback();
    //                                 dd($e);
    //                                 Toastr::error('Operation Failed', 'Failed');

    //                                 return redirect()->back();
    //                             }
    //                         } catch (Exception $e) {
    //                             DB::rollback();
    //                             dd($e);
    //                             Toastr::error('Operation Failed', 'Failed');

    //                             return redirect()->back();
    //                         }
    //                     } catch (Exception $e) {
    //                         DB::rollback();
    //                         dd($e);
    //                         Toastr::error('Operation Failed', 'Failed');

    //                         return redirect()->back();
    //                     }
    //                 } catch (Exception $e) {
    //                     DB::rollback();
    //                     dd($e);
    //                     Toastr::error('Operation Failed', 'Failed');

    //                     return redirect()->back();
    //                 }
    //             }

    //             StudentBulkTemporary::where('user_id', Auth::user()->id)->delete();

    //                 DB::commit();
    //                 Toastr::success('Operation successful', 'Success');
    //                 return redirect()->back();
    //             }
    //         /*
    //         } catch (\Exception $e) {
    //             dd($e);
    //             Toastr::error('Operation Failed', 'Failed');
    //             return redirect()->back();
    //         }
    //         */


    // }

    public function mm()
    {
        return view('backEnd.studentInformation.mm');
    }

}
