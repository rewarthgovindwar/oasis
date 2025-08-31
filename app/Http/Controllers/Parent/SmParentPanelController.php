<?php

namespace App\Http\Controllers\Parent;

use App\User;
use Exception;
use App\SmBook;
use App\SmExam;
use App\SmClass;
use App\SmEvent;
use App\SmRoute;
use App\SmStaff;
use App\SmParent;
use App\SmHoliday;
use App\SmSection;
use App\SmStudent;
use App\SmVehicle;
use App\SmWeekend;
use Carbon\Carbon;
use App\SmExamType;
use App\SmHomework;
use App\SmRoomList;
use App\SmRoomType;
use App\SmBaseSetup;
use App\SmBookIssue;
use App\SmClassTime;
use App\SmComplaint;
use App\SmFeesAssign;
use App\SmMarksGrade;
use App\SmOnlineExam;
use App\ApiBaseMethod;
use App\SmBankAccount;
use App\SmLeaveDefine;
use App\SmNoticeBoard;
use App\SmAcademicYear;
use App\SmExamSchedule;
use App\SmLeaveRequest;
use App\SmStudentGroup;
use App\SmAssignSubject;
use App\SmAssignVehicle;
use App\SmDormitoryList;
use App\SmLibraryMember;
use App\SmPaymentMethhod;
use App\SmGeneralSettings;
use App\SmStudentCategory;
use App\SmStudentDocument;
use App\SmStudentTimeline;
use App\Models\FeesInvoice;
use App\SmStudentAttendance;
use App\SmSubjectAttendance;
use App\Traits\CustomFields;
use Illuminate\Http\Request;
use App\Models\SmCustomField;
use App\Models\StudentRecord;
use App\SmFeesAssignDiscount;
use App\SmClassOptionalSubject;
use Barryvdh\DomPDF\Facade\Pdf;
use App\SmOptionalSubjectAssign;
use App\SmStudentTakeOnlineExam;
use App\Traits\NotificationSend;
use App\Models\SmCalendarSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\TeacherEvaluationSetting;
use Illuminate\Support\Facades\Response;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Support\Facades\Validator;
use App\Models\SmStudentRegistrationField;
use Modules\RolePermission\Entities\InfixRole;
use Modules\Wallet\Entities\WalletTransaction;
use Modules\OnlineExam\Entities\InfixOnlineExam;
use Modules\BehaviourRecords\Entities\AssignIncident;
use App\Http\Controllers\SmAcademicCalendarController;
use Modules\OnlineExam\Entities\InfixStudentTakeOnlineExam;
use Modules\BehaviourRecords\Entities\BehaviourRecordSetting;
use App\Http\Requests\Admin\StudentInfo\SmStudentAdmissionRequest;

class SmParentPanelController extends Controller
{
    use CustomFields;
    use NotificationSend;

    public function parentDashboard()
    {
            $holidays = SmHoliday::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', auth()->user()->school_id)->get();
            $my_childrens = auth()->user()->parent ? auth()->user()->parent->childrens->load('assignSubjects', 'assignSubject', 'studentOnlineExams', 'studentRecords', 'studentRecords.feesInvoice', 'studentRecords.class', 'studentRecords.section', 'studentRecords.incidents', 'examSchedule', 'attendances') : [];

            $sm_weekends = SmWeekend::orderBy('order', 'ASC')->where('active_status', 1)->where('school_id', auth()->user()->school_id)->get();
            $smevents = SmEvent::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', auth()->user()->school_id)
                ->where(function ($q): void {
                    $q->where('for_whom', 'All')->orWhere('for_whom', 'Parents');
                })
                ->get();

            $count_event = 0;
            $calendar_events = [];

            foreach ($holidays as $k => $holiday) {

                $calendar_events[$k]['title'] = $holiday->holiday_title;

                $calendar_events[$k]['start'] = $holiday->from_date;

                $calendar_events[$k]['end'] = Carbon::parse($holiday->to_date)->addDays(1)->format('Y-m-d');

                $calendar_events[$k]['description'] = $holiday->details;

                $calendar_events[$k]['url'] = $holiday->upload_image_file;

                $count_event = $k;
                $count_event++;
            }

            foreach ($smevents as $smevent) {

                $calendar_events[$count_event]['title'] = $smevent->event_title;

                $calendar_events[$count_event]['start'] = $smevent->from_date;

                $calendar_events[$count_event]['end'] = Carbon::parse($smevent->to_date)->addDays(1)->format('Y-m-d');
                $calendar_events[$count_event]['description'] = $smevent->event_des;
                $calendar_events[$count_event]['url'] = $smevent->uplad_image_file;
                $count_event++;
            }

            $totalNotices = SmNoticeBoard::where('active_status', 1)->where('inform_to', 'LIKE', '%3%')
                ->orderBy('id', 'DESC')
                ->where('publish_on', '<=', date('Y-m-d'))
                ->where('academic_id', getAcademicId())
                ->where('school_id', auth()->user()->school_id)->get();
            $currency = SmGeneralSettings::find(1);

            $complaints = SmComplaint::with('complaintType', 'complaintSource')->get();

            $data['settings'] = SmCalendarSetting::get();
            $data['roles'] = InfixRole::where('is_saas', 0)->where(function ($q): void {
                $q->where('school_id', auth()->user()->school_id)->orWhere('type', 'System');
            })
                ->whereNotIn('id', [1, 2])
                ->get();
            $smAcademicCalendarController = new SmAcademicCalendarController();
            $data['events'] = $smAcademicCalendarController->calenderData();

            return view('backEnd.parentPanel.parent_dashboard', compact('holidays', 'calendar_events', 'smevents', 'totalNotices', 'my_childrens', 'sm_weekends', 'currency', 'complaints'), $data);
    }

    public function studentUpdate(SmStudentAdmissionRequest $smStudentAdmissionRequest)
    {
        try {
            $student_detail = SmStudent::find($smStudentAdmissionRequest->id);
            $validator = Validator::make($smStudentAdmissionRequest->all(), $this->generateValidateRules('student_registration', $student_detail));
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
            $student = SmStudent::find($smStudentAdmissionRequest->id);

            $academic_year = $smStudentAdmissionRequest->session ? SmAcademicYear::find($smStudentAdmissionRequest->session) : '';
            DB::beginTransaction();

            if ($student) {
                
                $username = $smStudentAdmissionRequest->phone_number ?: $smStudentAdmissionRequest->admission_number;
                $phone_number = $smStudentAdmissionRequest->phone_number ?: null;
                $user_stu = $this->addUser($student_detail->user_id, 2, $username, $smStudentAdmissionRequest->email_address, $phone_number);

                // sibling || parent info user update
                if (($smStudentAdmissionRequest->sibling_id == 0 || $smStudentAdmissionRequest->sibling_id == 1) && $smStudentAdmissionRequest->parent_id == '') {
                    $username = $smStudentAdmissionRequest->guardians_phone ?: $smStudentAdmissionRequest->guardians_email;
                    $phone_number = $smStudentAdmissionRequest->guardians_phone;
                    $user_parent = $this->addUser($student_detail->parents->user_id, 3, $username, $smStudentAdmissionRequest->guardians_email, $phone_number);

                    $user_parent->toArray();
                } elseif ($smStudentAdmissionRequest->sibling_id == 0 && $smStudentAdmissionRequest->parent_id != '') {
                    User::destroy($student_detail->parents->user_id);
                } elseif (($smStudentAdmissionRequest->sibling_id == 2 || $smStudentAdmissionRequest->sibling_id == 1) && $smStudentAdmissionRequest->parent_id != '') {
                } elseif ($smStudentAdmissionRequest->sibling_id == 2 && $smStudentAdmissionRequest->parent_id == '') {

                    $username = $smStudentAdmissionRequest->guardians_phone ?: $smStudentAdmissionRequest->guardians_email;
                    $phone_number = $smStudentAdmissionRequest->guardians_phone;
                    $user_parent = $this->addUser(null, 3, $username, $smStudentAdmissionRequest->guardians_email, $phone_number);
                    $user_parent->toArray();
                }
                // end
                // sibling & parent info update
                if ($smStudentAdmissionRequest->sibling_id == 0 && $smStudentAdmissionRequest->parent_id != '') {
                    SmParent::destroy($student_detail->parent_id);
                } elseif (($smStudentAdmissionRequest->sibling_id == 2 || $smStudentAdmissionRequest->sibling_id == 1) && $smStudentAdmissionRequest->parent_id != '') {
                } else {

                    if (($smStudentAdmissionRequest->sibling_id == 0 || $smStudentAdmissionRequest->sibling_id == 1) && $smStudentAdmissionRequest->parent_id == '') {
                        $parent = SmParent::find($student_detail->parent_id);
                    } elseif ($smStudentAdmissionRequest->sibling_id == 2 && $smStudentAdmissionRequest->parent_id == '') {
                        $parent = new SmParent();
                    }

                    $parent->user_id = $user_parent->id;
                    
                    if ($smStudentAdmissionRequest->filled('fathers_name')) {
                        $parent->fathers_name = $smStudentAdmissionRequest->fathers_name;
                    }

                    if ($smStudentAdmissionRequest->filled('fathers_phone')) {
                        $parent->fathers_mobile = $smStudentAdmissionRequest->fathers_phone;
                    }

                    if ($smStudentAdmissionRequest->filled('fathers_occupation')) {
                        $parent->fathers_occupation = $smStudentAdmissionRequest->fathers_occupation;
                    }

                    if ($smStudentAdmissionRequest->filled('fathers_photo')) {
                        $parent->fathers_photo = fileUpdate($parent->fathers_photo, $smStudentAdmissionRequest->fathers_photo, $student_file_destination);
                    }

                    if ($smStudentAdmissionRequest->filled('mothers_name')) {
                        $parent->mothers_name = $smStudentAdmissionRequest->mothers_name;
                    }

                    if ($smStudentAdmissionRequest->filled('mothers_phone')) {
                        $parent->mothers_mobile = $smStudentAdmissionRequest->mothers_phone;
                    }

                    if ($smStudentAdmissionRequest->filled('mothers_occupation')) {
                        $parent->mothers_occupation = $smStudentAdmissionRequest->mothers_occupation;
                    }

                    if ($smStudentAdmissionRequest->filled('mothers_photo')) {
                        $parent->mothers_photo = fileUpdate($parent->mothers_photo, $smStudentAdmissionRequest->mothers_photo, $student_file_destination);
                    }

                    if ($smStudentAdmissionRequest->filled('guardians_name')) {
                        $parent->guardians_name = $smStudentAdmissionRequest->guardians_name;
                    }

                    if ($smStudentAdmissionRequest->filled('guardians_phone')) {
                        $parent->guardians_mobile = $smStudentAdmissionRequest->guardians_phone;
                    }

                    if ($smStudentAdmissionRequest->filled('guardians_email')) {
                        $parent->guardians_email = $smStudentAdmissionRequest->guardians_email;
                    }

                    if ($smStudentAdmissionRequest->filled('guardians_occupation')) {
                        $parent->guardians_occupation = $smStudentAdmissionRequest->guardians_occupation;
                    }

                    if ($smStudentAdmissionRequest->filled('relation')) {
                        $parent->guardians_relation = $smStudentAdmissionRequest->relation;
                    }

                    if ($smStudentAdmissionRequest->filled('relationButton')) {
                        $parent->relation = $smStudentAdmissionRequest->relationButton;
                    }

                    if ($smStudentAdmissionRequest->filled('guardians_photo')) {
                        $parent->guardians_photo = fileUpdate($student->parents->guardians_photo, $smStudentAdmissionRequest->guardians_photo, $student_file_destination);
                    }

                    if ($smStudentAdmissionRequest->filled('guardians_address')) {
                        $parent->guardians_address = $smStudentAdmissionRequest->guardians_address;
                    }

                    if ($smStudentAdmissionRequest->filled('is_guardian')) {
                        $parent->is_guardian = $smStudentAdmissionRequest->is_guardian;
                    }

                    if ($smStudentAdmissionRequest->filled('session')) {
                        $parent->created_at = $academic_year->year.'-01-01 12:00:00';
                    }

                    $parent->save();
                    $parent->toArray();
                }

                // end sibling & parent info update
                // student info update
                $student = SmStudent::find($smStudentAdmissionRequest->id);
                if (($smStudentAdmissionRequest->sibling_id == 0 || $smStudentAdmissionRequest->sibling_id == 1) && $smStudentAdmissionRequest->parent_id == '') {
                    $student->parent_id = $parent->id;
                } elseif ($smStudentAdmissionRequest->sibling_id == 0 && $smStudentAdmissionRequest->parent_id != '') {
                    $student->parent_id = $smStudentAdmissionRequest->parent_id;
                } elseif (($smStudentAdmissionRequest->sibling_id == 2 || $smStudentAdmissionRequest->sibling_id == 1) && $smStudentAdmissionRequest->parent_id != '') {
                    $student->parent_id = $smStudentAdmissionRequest->parent_id;
                } elseif ($smStudentAdmissionRequest->sibling_id == 2 && $smStudentAdmissionRequest->parent_id == '') {
                    $student->parent_id = $parent->id;
                }

                if ($smStudentAdmissionRequest->filled('class')) {
                    $student->class_id = $smStudentAdmissionRequest->class;
                }

                if ($smStudentAdmissionRequest->filled('section')) {
                    $student->section_id = $smStudentAdmissionRequest->section;
                }

                if ($smStudentAdmissionRequest->filled('session')) {
                    $student->session_id = $smStudentAdmissionRequest->session;
                }

                if ($smStudentAdmissionRequest->filled('admission_number')) {
                    $student->admission_no = $smStudentAdmissionRequest->admission_number;
                }

                $student->user_id = $user_stu->id;
                if ($smStudentAdmissionRequest->filled('roll_number')) {
                    $student->roll_no = $smStudentAdmissionRequest->roll_number;
                }

                if ($smStudentAdmissionRequest->filled('first_name')) {
                    $student->first_name = $smStudentAdmissionRequest->first_name;
                }

                if ($smStudentAdmissionRequest->filled('last_name')) {
                    $student->last_name = $smStudentAdmissionRequest->last_name;
                }

                if ($smStudentAdmissionRequest->filled('first_name') && $smStudentAdmissionRequest->filled('last_name')) {
                    $student->full_name = $smStudentAdmissionRequest->first_name.' '.$smStudentAdmissionRequest->last_name;
                }

                if ($smStudentAdmissionRequest->filled('gender')) {
                    $student->gender_id = $smStudentAdmissionRequest->gender;
                }

                if ($smStudentAdmissionRequest->filled('date_of_birth')) {
                    $student->date_of_birth = date('Y-m-d', strtotime($smStudentAdmissionRequest->date_of_birth));
                }

                if ($smStudentAdmissionRequest->filled('age')) {
                    $student->age = $smStudentAdmissionRequest->age;
                }

                if ($smStudentAdmissionRequest->filled('caste')) {
                    $student->caste = $smStudentAdmissionRequest->caste;
                }

                if ($smStudentAdmissionRequest->filled('email_address')) {
                    $student->email = $smStudentAdmissionRequest->email_address;
                }

                if ($smStudentAdmissionRequest->filled('phone_number')) {
                    $student->mobile = $smStudentAdmissionRequest->phone_number;
                }

                if ($smStudentAdmissionRequest->filled('admission_date')) {
                    $student->admission_date = date('Y-m-d', strtotime($smStudentAdmissionRequest->admission_date));
                }

                if ($smStudentAdmissionRequest->filled('photo')) {
                    $student->student_photo = fileUpdate($parent->student_photo, $smStudentAdmissionRequest->photo, $student_file_destination);
                }

                if ($smStudentAdmissionRequest->filled('blood_group')) {
                    $student->bloodgroup_id = $smStudentAdmissionRequest->blood_group;
                }

                if ($smStudentAdmissionRequest->filled('religion')) {
                    $student->religion_id = $smStudentAdmissionRequest->religion;
                }

                if ($smStudentAdmissionRequest->filled('height')) {
                    $student->height = $smStudentAdmissionRequest->height;
                }

                if ($smStudentAdmissionRequest->filled('weight')) {
                    $student->weight = $smStudentAdmissionRequest->weight;
                }

                if ($smStudentAdmissionRequest->filled('current_address')) {
                    $student->current_address = $smStudentAdmissionRequest->current_address;
                }

                if ($smStudentAdmissionRequest->filled('permanent_address')) {
                    $student->permanent_address = $smStudentAdmissionRequest->permanent_address;
                }

                if ($smStudentAdmissionRequest->filled('student_category_id')) {
                    $student->student_category_id = $smStudentAdmissionRequest->student_category_id;
                }

                if ($smStudentAdmissionRequest->filled('student_group_id')) {
                    $student->student_group_id = $smStudentAdmissionRequest->student_group_id;
                }

                if ($smStudentAdmissionRequest->filled('route')) {
                    $student->route_list_id = $smStudentAdmissionRequest->route;
                }

                if ($smStudentAdmissionRequest->filled('dormitory_name')) {
                    $student->dormitory_id = $smStudentAdmissionRequest->dormitory_name;
                }

                if ($smStudentAdmissionRequest->filled('room_number')) {
                    $student->room_id = $smStudentAdmissionRequest->room_number;
                }

                if (! empty($smStudentAdmissionRequest->vehicle)) {
                    $driver = SmVehicle::where('id', '=', $smStudentAdmissionRequest->vehicle)
                        ->select('driver_id')
                        ->first();
                    $student->vechile_id = $smStudentAdmissionRequest->vehicle;
                    $student->driver_id = $driver->driver_id;
                }

                if ($smStudentAdmissionRequest->filled('national_id_number')) {
                    $student->national_id_no = $smStudentAdmissionRequest->national_id_number;
                }

                if ($smStudentAdmissionRequest->filled('local_id_number')) {
                    $student->local_id_no = $smStudentAdmissionRequest->local_id_number;
                }

                if ($smStudentAdmissionRequest->filled('bank_account_number')) {
                    $student->bank_account_no = $smStudentAdmissionRequest->bank_account_number;
                }

                if ($smStudentAdmissionRequest->filled('bank_name')) {
                    $student->bank_name = $smStudentAdmissionRequest->bank_name;
                }

                if ($smStudentAdmissionRequest->filled('previous_school_details')) {
                    $student->previous_school_details = $smStudentAdmissionRequest->previous_school_details;
                }

                if ($smStudentAdmissionRequest->filled('additional_notes')) {
                    $student->aditional_notes = $smStudentAdmissionRequest->additional_notes;
                }

                if ($smStudentAdmissionRequest->filled('ifsc_code')) {
                    $student->ifsc_code = $smStudentAdmissionRequest->ifsc_code;
                }

                if ($smStudentAdmissionRequest->filled('document_title_1')) {
                    $student->document_title_1 = $smStudentAdmissionRequest->document_title_1;
                }

                if ($smStudentAdmissionRequest->filled('document_file_1')) {
                    $student->document_file_1 = fileUpdate($student->document_file_1, $smStudentAdmissionRequest->file('document_file_1'), $destination);
                }

                if ($smStudentAdmissionRequest->filled('document_title_2')) {
                    $student->document_title_2 = $smStudentAdmissionRequest->document_title_2;
                }

                if ($smStudentAdmissionRequest->filled('document_file_2')) {
                    $student->document_file_2 = fileUpdate($student->document_file_2, $smStudentAdmissionRequest->file('document_file_2'), $destination);
                }

                if ($smStudentAdmissionRequest->filled('document_title_3')) {
                    $student->document_title_3 = $smStudentAdmissionRequest->document_title_3;
                }

                if ($smStudentAdmissionRequest->filled('document_file_3')) {
                    $student->document_file_3 = fileUpdate($student->document_file_3, $smStudentAdmissionRequest->file('document_file_3'), $destination);
                }

                if ($smStudentAdmissionRequest->filled('document_title_4')) {
                    $student->document_title_4 = $smStudentAdmissionRequest->document_title_4;
                }

                if ($smStudentAdmissionRequest->filled('document_title_4')) {
                    $student->document_file_4 = fileUpdate($student->document_file_4, $smStudentAdmissionRequest->file('document_file_3'), $destination);
                }

                if ($smStudentAdmissionRequest->filled('session')) {
                    $student->created_at = $academic_year->year.'-01-01 12:00:00';
                    $student->academic_id = $academic_year->id;
                }

                if ($smStudentAdmissionRequest->customF) {
                    $dataImage = $smStudentAdmissionRequest->customF;
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

                if (moduleStatusCheck('Lead') == true) {
                    if ($smStudentAdmissionRequest->filled('lead_city')) {
                        $student->lead_city_id = $smStudentAdmissionRequest->lead_city;
                    }

                    if ($smStudentAdmissionRequest->filled('source_id')) {
                        $student->source_id = $smStudentAdmissionRequest->source_id;
                    }
                }

                $student->save();
                DB::commit();
            }

            // session null
            $update_stud = SmStudent::where('user_id', $student->user_id)->first('student_photo');
            Session::put('profile', $update_stud->student_photo);
            Toastr::success('Operation successful', 'Success');

            return redirect()->route('my_children', [$student->id]);
        } catch (Exception $exception) {
            DB::rollback();
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }
    
    public function UpdatemyChildren($id)
    {

            $student = SmStudent::find($id);

            $classes = SmClass::where('active_status', '=', '1')
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $religions = SmBaseSetup::where('active_status', '=', '1')
                ->where('base_group_id', '=', '2')
                ->get();

            $blood_groups = SmBaseSetup::where('active_status', '=', '1')
                ->where('base_group_id', '=', '3')
                ->get();

            $genders = SmBaseSetup::where('active_status', '=', '1')
                ->where('base_group_id', '=', '1')
                ->get();

            $route_lists = SmRoute::where('active_status', '=', '1')
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $vehicles = SmVehicle::where('active_status', '=', '1')
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $dormitory_lists = SmDormitoryList::where('active_status', '=', '1')
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $driver_lists = SmStaff::where([['active_status', '=', '1'], ['role_id', 9]])
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $categories = SmStudentCategory::where('school_id', Auth::user()->school_id)
                ->get();

            $groups = SmStudentGroup::where('school_id', Auth::user()->school_id)
                ->get();

            $sessions = SmAcademicYear::where('active_status', '=', '1')
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $siblings = SmStudent::where('parent_id', $student->parent_id)
                ->where('school_id', Auth::user()->school_id)
                ->get();
            $lead_city = [];
            $sources = [];

            if (moduleStatusCheck('Lead') == true) {
                $lead_city = \Modules\Lead\Entities\LeadCity::where('school_id', auth()->user()->school_id)->get(['id', 'city_name']);
                $sources = \Modules\Lead\Entities\Source::where('school_id', auth()->user()->school_id)->get(['id', 'source_name']);
            }

            $fields = SmStudentRegistrationField::where('school_id', auth()->user()->school_id)
                ->when(auth()->user()->role_id == 2, function ($query): void {
                    $query->where('student_edit', 1);
                })
                ->when(auth()->user()->role_id == 3, function ($query): void {
                    $query->where('parent_edit', 1);
                })
                ->pluck('field_name')->toArray();
            $custom_fields = SmCustomField::where('form_name', 'student_registration')->where('school_id', Auth::user()->school_id)->orderby('id', 'DESC')->get();

            return view('backEnd.parentPanel.update_my_children', compact('student', 'classes', 'religions', 'blood_groups', 'genders', 'route_lists', 'vehicles', 'dormitory_lists', 'categories', 'groups', 'sessions', 'siblings', 'driver_lists', 'lead_city', 'fields', 'sources','custom_fields'));
    }

    public function myChildren($id)
    {
            $parent_info = Auth::user()->parent;
            $student_detail = SmStudent::where('id', $id)->where('parent_id', $parent_info->id)->with('studentRecords.directFeesInstallments.payments', 'studentAttendances', 'studentRecords.directFeesInstallments.installment', 'feesAssign', 'feesAssignDiscount', 'academicYear', 'defaultClass.class', 'category', 'religion')->first();
            $records = $student_detail->studentRecords;
            if ($student_detail) {
                $driver = SmVehicle::where('sm_vehicles.id', $student_detail->vechile_id)
                    ->join('sm_staffs', 'sm_vehicles.driver_id', '=', 'sm_staffs.id')
                    ->where('sm_staffs.school_id', Auth::user()->school_id)
                    ->first();

                $optional_subject_setup = SmClassOptionalSubject::where('class_id', '=', $student_detail->class_id)->first();
                $student_optional_subject = SmOptionalSubjectAssign::where('student_id', $student_detail->id)
                    ->where('session_id', '=', $student_detail->session_id)
                    ->first();

                $fees_assigneds = $student_detail->feesAssign;
                $invoice_settings = FeesInvoice::where('school_id', Auth::user()->school_id)->first();
                $fees_discounts = $student_detail->feesAssignDiscount;

                $documents = SmStudentDocument::where('student_staff_id', $student_detail->id)
                    ->where('type', 'stu')
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                $timelines = SmStudentTimeline::where('staff_student_id', $student_detail->id)
                    ->where('type', 'stu')
                    ->where('academic_id', getAcademicId())
                    ->where('visible_to_student', 1)
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                $exams = SmExamSchedule::where('class_id', $student_detail->class_id)
                    ->where('section_id', $student_detail->section_id)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                $grades = SmMarksGrade::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                $maxgpa = $grades->max('gpa');

                $failgpa = $grades->min('gpa');

                $failgpaname = $grades->where('gpa', $failgpa)
                    ->first();

                $academic_year = $student_detail->academicYear;

                $exam_terms = SmExamType::where('school_id', Auth::user()->school_id)
                    ->where('academic_id', getAcademicId())
                    ->get();
                $custom_field_data = $student_detail->custom_field;

                $custom_field_values = is_null($custom_field_data) ? null : json_decode($custom_field_data);

                $paymentMethods = SmPaymentMethhod::whereNotIn('method', ['Cash', 'Wallet'])
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                $bankAccounts = SmBankAccount::where('active_status', 1)
                    ->where('school_id', Auth::user()->school_id)
                    ->get();

                if (moduleStatusCheck('Wallet')) {
                    $walletAmounts = WalletTransaction::where('user_id', Auth::user()->id)
                        ->where('school_id', Auth::user()->school_id)
                        ->get();
                } else {
                    $walletAmounts = null;
                }

                $custom_field_data = $student_detail->custom_field;

                $custom_field_values = is_null($custom_field_data) ? null : json_decode($custom_field_data);

                $data['bank_info'] = SmPaymentMethhod::where('method', 'Bank')->where('school_id', Auth::user()->school_id)->first();
                $data['cheque_info'] = SmPaymentMethhod::where('method', 'Cheque')->where('school_id', Auth::user()->school_id)->first();

                $leave_details = SmLeaveRequest::where('staff_id', $student_detail->user_id)->where('role_id', 2)->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
                $payment_gateway = SmPaymentMethhod::first();
                $student = SmStudent::where('id', $id)->where('parent_id', $parent_info->id)->first();

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

                if (moduleStatusCheck('University')) {
                    $student_id = $student_detail->id;
                    $studentDetails = SmStudent::find($student_id);
                    $studentRecordDetails = StudentRecord::where('student_id', $student_id);
                    $studentRecords = $studentRecordDetails->distinct('un_academic_id')->get();
                    $print = 1;

                    return view('backEnd.parentPanel.my_children', ['student_detail' => $student_detail, 'fees_assigneds' => $fees_assigneds, 'driver' => $driver, 'fees_discounts' => $fees_discounts, 'exams' => $exams, 'documents' => $documents, 'timelines' => $timelines, 'grades' => $grades, 'exam_terms' => $exam_terms, 'academic_year' => $academic_year, 'leave_details' => $leave_details, 'optional_subject_setup' => $optional_subject_setup, 'student_optional_subject' => $student_optional_subject, 'maxgpa' => $maxgpa, 'failgpaname' => $failgpaname, 'custom_field_values' => $custom_field_values, 'walletAmounts' => $walletAmounts, 'bankAccounts' => $bankAccounts, 'paymentMethods' => $paymentMethods, 'records' => $records, 'studentDetails' => $studentDetails, 'studentRecordDetails' => $studentRecordDetails, 'studentRecords' => $studentRecords, 'print' => $print, 'payment_gateway' => $payment_gateway, 'student' => $student, 'data' => $data, 'invoice_settings' => $invoice_settings, 'studentBehaviourRecords' => $studentBehaviourRecords, 'behaviourRecordSetting' => $behaviourRecordSetting]);
                }else {
                    return view('backEnd.parentPanel.my_children', ['student_detail' => $student_detail, 'fees_assigneds' => $fees_assigneds, 'driver' => $driver, 'fees_discounts' => $fees_discounts, 'exams' => $exams, 'documents' => $documents, 'timelines' => $timelines, 'grades' => $grades, 'exam_terms' => $exam_terms, 'academic_year' => $academic_year, 'leave_details' => $leave_details, 'optional_subject_setup' => $optional_subject_setup, 'student_optional_subject' => $student_optional_subject, 'maxgpa' => $maxgpa, 'failgpaname' => $failgpaname, 'custom_field_values' => $custom_field_values, 'walletAmounts' => $walletAmounts, 'bankAccounts' => $bankAccounts, 'paymentMethods' => $paymentMethods, 'records' => $records, 'payment_gateway' => $payment_gateway, 'student' => $student, 'data' => $data, 'invoice_settings' => $invoice_settings, 'studentBehaviourRecords' => $studentBehaviourRecords, 'behaviourRecordSetting' => $behaviourRecordSetting]);
                }

            } else {
                Toastr::warning('Invalid Student ID', 'Invalid');
                return redirect()->back();
            }
    }

    public function onlineExamination($id)
    {

            // $student = Auth::user()->student;
            $student = SmStudent::findOrfail($id);
            $records = studentRecords(null, $student->id)->get();

            $time_zone_setup = SmGeneralSettings::join('sm_time_zones', 'sm_time_zones.id', '=', 'sm_general_settings.time_zone_id')
                ->where('school_id', Auth::user()->school_id)->first();
            date_default_timezone_set($time_zone_setup->time_zone);
            // $now = date('H:i:s');

            // ->where('start_time', '<', $now)
            if (moduleStatusCheck('OnlineExam') == true) {
                $online_exams = InfixOnlineExam::where('active_status', 1)->where('status', 1)->where('class_id', $student->class_id)->where('section_id', $student->section_id)
                    ->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

                $marks_assigned = InfixStudentTakeOnlineExam::whereIn('online_exam_id', $online_exams->pluck('id')->toArray())->where('student_id', $student->id)->where('status', 2)
                    ->where('school_id', Auth::user()->school_id)->pluck('online_exam_id')->toArray();
            } else {
                $online_exams = SmOnlineExam::where('active_status', 1)->where('status', 1)->where('class_id', $student->class_id)->where('section_id', $student->section_id)
                    ->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

                $marks_assigned = SmStudentTakeOnlineExam::whereIn('online_exam_id', $online_exams->pluck('id')->toArray())->where('student_id', $student->id)->where('status', 2)
                    ->where('school_id', Auth::user()->school_id)->pluck('online_exam_id')->toArray();
            }

            return view('backEnd.parentPanel.parent_online_exam', compact('online_exams', 'marks_assigned', 'student', 'records'));
    }

    public function onlineExaminationResult($id)
    {
            if (moduleStatusCheck('OnlineExam') == true) {
                $result_views = InfixStudentTakeOnlineExam::where('active_status', 1)->where('status', 2)
                    ->where('academic_id', getAcademicId())
                    ->where('student_id', $id)
                    ->where('school_id', Auth::user()->school_id)
                    ->get();
            } else {
                $result_views = SmStudentTakeOnlineExam::where('active_status', 1)->where('status', 2)
                    ->where('academic_id', getAcademicId())
                    ->where('student_id', $id)
                    ->where('school_id', Auth::user()->school_id)
                    ->get();
            }

            $records = studentRecords(null, $id)->get();

            return view('backEnd.parentPanel.parent_online_exam_result', compact('result_views', 'records', 'id'));
    }

    public function parentAnswerScript($exam_id, $s_id)
    {
            if (moduleStatusCheck('OnlineExam') == true) {
                $take_online_exam = InfixStudentTakeOnlineExam::where('online_exam_id', $exam_id)->where('student_id', $s_id)->where('school_id', Auth::user()->school_id)->first();
            } else {
                $take_online_exam = SmStudentTakeOnlineExam::where('online_exam_id', $exam_id)->where('student_id', $s_id)->where('school_id', Auth::user()->school_id)->first();
            }

            return view('backEnd.examination.online_answer_view_script_modal', compact('take_online_exam', 's_id'));
    }

    public function parentLeave($id)
    {

            $student = SmStudent::findOrfail($id);
            $apply_leaves = SmLeaveRequest::where('staff_id', '=', $student->user_id)
                ->join('sm_leave_defines', 'sm_leave_defines.id', '=', 'sm_leave_requests.leave_define_id')
                ->join('sm_leave_types', 'sm_leave_types.id', '=', 'sm_leave_defines.type_id')
                ->where('sm_leave_requests.academic_id', getAcademicId())
                ->where('sm_leave_requests.school_id', Auth::user()->school_id)->get();

            return view('backEnd.parentPanel.parent_leave', compact('apply_leaves', 'student'));
    }

    public function leaveApply(Request $request)
    {
            $user = Auth::user();
            $std_id = SmStudent::leftjoin('sm_parents', 'sm_parents.id', 'sm_students.parent_id')
                ->where('sm_parents.user_id', $user->id)
                ->where('sm_students.active_status', 1)
                ->where('sm_students.school_id', Auth::user()->school_id)
                ->select('sm_students.user_id')
                ->get();
            $my_leaves = SmLeaveDefine::where('role_id', 2)->whereIn('user_id', $std_id->pluck('user_id'))->where('school_id', Auth::user()->school_id)->get();
            $apply_leaves = SmLeaveRequest::whereIn('staff_id', $std_id->pluck('user_id'))->where('role_id', 2)->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $leave_types = SmLeaveDefine::where('role_id', 2)->where('active_status', 1)->whereIn('user_id', $std_id)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.parentPanel.apply_leave', compact('apply_leaves', 'leave_types', 'my_leaves', 'user'));
    }

    public function leaveStore(Request $request)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $input = $request->all();
        $validator = Validator::make($input, [
            'student_id' => 'required',
            'apply_date' => 'required',
            'leave_type' => 'required',
            'leave_from' => 'required|before_or_equal:leave_to',
            'leave_to' => 'required',
            'attach_file' => 'sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt',
            'reason' => 'required',
        ]);
        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
            $input = $request->all();
            $fileName = '';
            if ($request->file('attach_file') != '') {
                //                'attach_file' => "sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt",
                $maxFileSize = SmGeneralSettings::first('file_size')->file_size;
                $file = $request->file('attach_file');
                $fileSize = filesize($file);
                $fileSizeKb = ($fileSize / 1000000);
                if ($fileSizeKb >= $maxFileSize) {
                    Toastr::error('Max upload file size '.$maxFileSize.' Mb is set in system', 'Failed');

                    return redirect()->back();
                }
                if($request->file('attach_file'))
                {
                    $file = $request->file('attach_file');
                    $fileName = md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();
                    $file->move('public/uploads/leave_request/', $fileName);
                    $fileName = 'public/uploads/leave_request/'.$fileName;
                }
                
            }

            $leaveDefine = SmLeaveDefine::where('user_id', $request->student_id)->where('type_id', $request->leave_type)->first();
            if (! $leaveDefine) {
                Toastr::warning('Please Add Leave Define First', 'Warning');
                return redirect()->back();
            }

            $smLeaveRequest = new SmLeaveRequest();
            $smLeaveRequest->staff_id = $request->student_id;
            $smLeaveRequest->role_id = 2;
            $smLeaveRequest->apply_date = date('Y-m-d', strtotime($request->apply_date));
            $smLeaveRequest->leave_define_id = $request->leave_type;
            $smLeaveRequest->type_id = $request->leave_type;
            $smLeaveRequest->leave_from = date('Y-m-d', strtotime($request->leave_from));
            $smLeaveRequest->leave_to = date('Y-m-d', strtotime($request->leave_to));
            $smLeaveRequest->approve_status = 'P';
            $smLeaveRequest->reason = $request->reason;
            $smLeaveRequest->file = $fileName;
            $smLeaveRequest->school_id = Auth::user()->school_id;
            $smLeaveRequest->academic_id = getAcademicId();
            $result = $smLeaveRequest->save();

            $studentInfo = SmStudent::where('user_id', $request->student_id)->first();
            $data['to_date'] = $smLeaveRequest->leave_to;
            $data['name'] = $smLeaveRequest->user->full_name;
            $data['from_date'] = $smLeaveRequest->leave_from;
            $data['class'] = $studentInfo->studentRecord->class->class_name;
            $data['section'] = $studentInfo->studentRecord->section->section_name;
            $this->sent_notifications('Leave_Apply', [$studentInfo->user_id], $data, ['Parent']);
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Leave Request has been created successfully.');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
    }

    public function viewLeaveDetails(Request $request, $id)
    {
            $leaveDetails = SmLeaveRequest::find($id);
            $apply = '';
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['leaveDetails'] = $leaveDetails->toArray();
                $data['apply'] = $apply;

                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.parentPanel.viewLeaveDetails', compact('leaveDetails', 'apply'));
    }

    public function leaveEdit($id) {}

    public function pendingLeave(Request $request)
    {
            $user = Auth::user();
            $std_id = SmStudent::leftjoin('sm_parents', 'sm_parents.id', 'sm_students.parent_id')
                ->where('sm_parents.user_id', $user->id)
                ->where('sm_students.active_status', 1)
                ->where('sm_students.academic_id', getAcademicId())
                ->where('sm_students.school_id', Auth::user()->school_id)
                ->select('sm_students.user_id')
                ->get();

            $apply_leaves = SmLeaveRequest::whereIn('staff_id', $std_id->pluck('user_id'))->where('role_id', 2)->where([['active_status', 1], ['approve_status', 'P']])->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();


            return view('backEnd.parentPanel.pending_leave', compact('apply_leaves'));
    }

    public function parentLeaveEdit(Request $request, $id)
    {
            $user = Auth::user();
            if ($user) {
                $my_leaves = SmLeaveDefine::where('role_id', 2)->where('school_id', Auth::user()->school_id)->get();
                $apply_leaves = SmLeaveRequest::where('role_id', 2)->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
                $leave_types = SmLeaveDefine::where('role_id', 2)->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            } else {
                $my_leaves = SmLeaveDefine::where('role_id', $request->role_id)->where('school_id', Auth::user()->school_id)->get();
                $apply_leaves = SmLeaveRequest::where('role_id', $request->role_id)->where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
                $leave_types = SmLeaveDefine::where('role_id', $request->role_id)->where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            }

            $apply_leave = SmLeaveRequest::find($id);

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['my_leaves'] = $my_leaves->toArray();
                $data['apply_leaves'] = $apply_leaves->toArray();
                $data['leave_types'] = $leave_types->toArray();
                $data['apply_leave'] = $apply_leave->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }
            return view('backEnd.parentPanel.apply_leave', compact('apply_leave', 'apply_leaves', 'leave_types', 'my_leaves', 'user'));
    }

    public function update(Request $request)
    {
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'id' => 'required',
                'apply_date' => 'required',
                'leave_type' => 'required',
                'leave_from' => 'required|before_or_equal:leave_to',
                'leave_to' => 'required',
                'login_id' => 'required',
                'role_id' => 'required',
                'file' => 'sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt',
            ]);
        } else {
            $validator = Validator::make($input, [
                'apply_date' => 'required',
                'leave_type' => 'required',
                'leave_from' => 'required|before_or_equal:leave_to',
                'leave_to' => 'required',
                'file' => 'sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt',
            ]);
        }

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
            $fileName = '';
            if ($request->file('attach_file') != '') {
                $maxFileSize = SmGeneralSettings::first('file_size')->file_size;
                $file = $request->file('attach_file');
                $fileSize = filesize($file);
                $fileSizeKb = ($fileSize / 1000000);
                if ($fileSizeKb >= $maxFileSize) {
                    Toastr::error('Max upload file size '.$maxFileSize.' Mb is set in system', 'Failed');

                    return redirect()->back();
                }

                $apply_leave = SmLeaveRequest::find($request->id);
                if (file_exists($apply_leave->file)) {
                    unlink($apply_leave->file);
                }

                if($request->file('attach_file'))
                {
                    $file = $request->file('attach_file');
                    $fileName = md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();
                    $file->move('public/uploads/leave_request/', $fileName);
                    $fileName = 'public/uploads/leave_request/'.$fileName;
                }
            }

            $user = Auth()->user();
            $apply_leave = SmLeaveRequest::find($request->id);
            $apply_leave->staff_id = $request->student_id;
            $apply_leave->role_id = 2;
            $apply_leave->apply_date = date('Y-m-d', strtotime($request->apply_date));
            $apply_leave->leave_define_id = $request->leave_type;
            $apply_leave->leave_from = date('Y-m-d', strtotime($request->leave_from));
            $apply_leave->leave_to = date('Y-m-d', strtotime($request->leave_to));
            $apply_leave->approve_status = 'P';
            $apply_leave->reason = $request->reason;
            $apply_leave->file = $fileName;
            $result = $apply_leave->save();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Leave Request has been updated successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('parent-apply-leave');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
    }

    public function DeleteLeave(Request $request, $id)
    {
            $apply_leave = SmLeaveRequest::find($id);
            if ($apply_leave->file != '' && file_exists($apply_leave->file)) {
                unlink($apply_leave->file);
            }

            $result = $apply_leave->delete();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Request has been deleted successfully');
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('parent-apply-leave');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
    }

    public function classRoutine($id)
    {
            
            $student_detail = SmStudent::where('id', $id)->with([
                'studentRecords' => function($query){
                    $query->when(moduleStatusCheck("University"),function($query){
                        $query->whereNotNull('un_session_id')
                              ->whereNotNull('un_faculty_id')
                              ->whereNotNull('un_department_id')
                              ->whereNotNull('un_academic_id')
                              ->whereNotNull('un_semester_id')
                              ->whereNotNull('un_semester_label_id')
                              ->whereNotNull('un_section_id');
                    },function($query){
                        $query->whereNotNull('class_id')
                              ->whereNotNull('section_id')
                              ->whereNotNull('academic_id');
                    });
                }
            ])->first();
        
            
            $class_id = $student_detail->class_id;
            $section_id = $student_detail->section_id;

            $sm_weekends = SmWeekend::orderBy('order', 'ASC')->where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            $class_times = SmClassTime::where('type', 'class')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $records = $student_detail->studentRecords;
            
            
            return view('backEnd.parentPanel.class_routine', compact('class_times', 'class_id', 'section_id', 'sm_weekends', 'student_detail', 'records'));
    }

    public function attendance($id)
    {
            $student_detail = SmStudent::where('id', $id)->first();
            $academic_years = SmAcademicYear::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.parentPanel.attendance', compact('student_detail', 'academic_years'));
    }

    public function attendanceSearch(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'month' => 'required',
            'year' => 'required',
        ]);
        if ($validator->fails()) {
            Toastr::error('Please fill the required fields', 'Failed');

            return redirect()->back()->withErrors($validator)->withInput();
        }

            $student_detail = SmStudent::where('id', $request->student_id)->first();
            $year = $request->year;
            $month = $request->month;
            $current_day = date('d');
            $days = cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year);
            $records = studentRecords(null, $student_detail->id)->with('studentAttendance')->get();
            $attendances = SmStudentAttendance::where('student_id', $student_detail->id)->where('academic_id', getAcademicId())->where('attendance_date', 'like', $request->year.'-'.$request->month.'%')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $academic_years = SmAcademicYear::where('active_status', '=', 1)->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.parentPanel.attendance', compact('records', 'days', 'year', 'month', 'current_day', 'student_detail', 'academic_years'));
    }

    public function attendancePrint($student_id, $id, string $month, string $year)
    {
            $student_detail = SmStudent::where('id', $student_id)->first();
            $current_day = date('d');
            $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            // $students = SmStudent::where('class_id', $request->class)->where('section_id', $request->section)->where('school_id',Auth::user()->school_id)->get();
            $attendances = SmStudentAttendance::where('student_record_id', $id)->where('student_id', $student_detail->id)->where('attendance_date', 'like', $year.'-'.$month.'%')->where('school_id', Auth::user()->school_id)->get();
            $customPaper = [0, 0, 700.00, 1000.80];
            $pdf = Pdf::loadView(
                'backEnd.parentPanel.attendance_print',
                [
                    'attendances' => $attendances,
                    'days' => $days,
                    'year' => $year,
                    'month' => $month,
                    'current_day' => $current_day,
                    'student_detail' => $student_detail,
                ]
            )->setPaper('A4', 'landscape');

            return $pdf->stream('my_child_attendance.pdf');
    }

    public function examinationSchedule($id)
    {
            $user = Auth::user();
            $parent = SmParent::where('user_id', $user->id)->first();
            $student_detail = SmStudent::where('id', $id)->first();
            $student_id = $student_detail->id;
            $records = studentRecords(null, $student_detail->id)->get();
            return view('backEnd.parentPanel.parent_exam_schedule', compact('student_id', 'records'));
    }

    public function examRoutinePrint($class_id, $section_id, $exam_term_id)
    {

            $exam_type_id = $exam_term_id;
            $exam_type = SmExamType::find($exam_type_id)->title;
            $academic_id = SmExamType::find($exam_type_id)->academic_id;
            $academic_year = SmAcademicYear::find($academic_id);
            $class_name = SmClass::find($class_id)->class_name;
            $section_name = SmSection::find($section_id)->section_name;
            $exam_schedules = SmExamSchedule::where('class_id', $class_id)->where('section_id', $section_id)
                ->where('exam_term_id', $exam_type_id)->orderBy('date', 'ASC')->get();
            $print = request()->print;

            return view(
                'backEnd.examination.exam_schedule_print',
                [
                    'exam_schedules' => $exam_schedules,
                    'exam_type' => $exam_type,
                    'class_name' => $class_name,
                    'academic_year' => $academic_year,
                    'section_name' => $section_name,
                    'print' => $print,
                ]
            );
            $pdf = Pdf::loadView(
                'backEnd.examination.exam_schedule_print',
                [
                    'exam_schedules' => $exam_schedules,
                    'exam_type' => $exam_type,
                    'class_name' => $class_name,
                    'academic_year' => $academic_year,
                    'section_name' => $section_name,

                ]
            )->setPaper('A4', 'landscape');
            return $pdf->stream('EXAM_SCHEDULE.pdf');
    }

    public function parentBookList()
    {
            $books = SmBook::where('active_status', 1)
                ->orderBy('id', 'DESC')
                ->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.parentPanel.parentBookList', compact('books'));
    }

    public function parentBookIssue()
    {
            $user = Auth::user();
            $parent_detail = SmParent::where('user_id', $user->id)->first();

            $library_member = SmLibraryMember::where('member_type', 3)->where('student_staff_id', $parent_detail->user_id)->first();
            if (empty($library_member)) {
                Toastr::error('You are not library member ! Please contact with librarian', 'Failed');

                return redirect()->back();
            }

            $issueBooks = SmBookIssue::where('member_id', $library_member->student_staff_id)
                ->leftjoin('sm_books', 'sm_books.id', 'sm_book_issues.book_id')
                ->leftjoin('library_subjects', 'library_subjects.id', 'sm_books.book_subject_id')
                /* ->where('sm_book_issues.issue_status', 'I') */
                ->where('sm_book_issues.school_id', Auth::user()->school_id)->get();

            return view('backEnd.parentPanel.parentBookIssue', compact('issueBooks'));
    }

    public function examinationScheduleSearch(Request $request)
    {

            $request->validate([
                'exam' => 'required',
            ]);
            $user = Auth::user();
            $parent = SmParent::where('user_id', $user->id)->first();
            $student_detail = SmStudent::find($request->student_id);
            $records = studentRecords(null, $student_detail->id)->get();
            $smExam = SmExam::findOrFail($request->exam);
            $student_id = $student_detail->id;
            $assign_subjects = SmAssignSubject::where('class_id', $smExam->class_id)->where('section_id', $smExam->section_id)
                ->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            if ($assign_subjects->count() == 0) {
                Toastr::error('No Subject Assigned. Please assign subjects in this class.', 'Failed');

                return redirect()->back();
            }

            $exams = SmExam::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $class_id = $smExam->class_id;
            $section_id = $smExam->section_id;
            $exam_id = $smExam->id;
            $exam_type_id = $smExam->exam_type_id;
            $exam_periods = SmClassTime::where('type', 'exam')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $exam_schedule_subjects = '';
            $assign_subject_check = '';

            $exam_routines = SmExamSchedule::where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('exam_term_id', $exam_type_id)->orderBy('date', 'ASC')->get();
            
            return view('backEnd.parentPanel.parent_exam_schedule', compact('exams', 'assign_subjects', 'class_id', 'section_id', 'exam_id', 'exam_schedule_subjects', 'assign_subject_check', 'records', 'exam_type_id', 'exam_routines', 'exam_periods', 'student_id'));
    }

    public function examination($id)
    {
            $student_detail = SmStudent::withoutGlobalScope(StatusAcademicSchoolScope::class)->find($id);
            $records = studentRecords(null, $student_detail->id)->get();
            $optional_subject_setup = SmClassOptionalSubject::where('class_id', '=', $student_detail->class_id)->first();

            $student_optional_subject = SmOptionalSubjectAssign::where('student_id', $student_detail->id)
                ->where('session_id', '=', $student_detail->session_id)
                ->first();

            $exams = SmExamSchedule::where('class_id', $student_detail->class_id)
                ->where('section_id', $student_detail->section_id)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $grades = SmMarksGrade::where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $failgpa = SmMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->min('gpa');

            $failgpaname = SmMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->where('gpa', $failgpa)
                ->first();
            $maxgpa = SmMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->max('gpa');

            $exam_terms = SmExamType::where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            return view('backEnd.parentPanel.student_result', compact('student_detail', 'exams', 'grades', 'exam_terms', 'failgpaname', 'optional_subject_setup', 'student_optional_subject', 'maxgpa', 'records'));
    }

    public function subjects($id)
    {
            $student_detail = SmStudent::where('id', $id)->first();
            $records = studentRecords(null, $student_detail->id)->get();
            return view('backEnd.parentPanel.subject', compact('records', 'student_detail'));
    }

    public function teacherList($id)
    {
            $student_detail = SmStudent::where('id', $id)->first();
            $records = studentRecords(null, $student_detail->id)->get();
            $teacherEvaluationSetting = TeacherEvaluationSetting::find(1);
            return view('backEnd.parentPanel.teacher_list', compact('records', 'student_detail', 'teacherEvaluationSetting'));
    }

    public function transport($id)
    {
            $behaviourRecordSetting = BehaviourRecordSetting::where('id', 1)->first();
            $studentBehaviourRecords = (moduleStatusCheck('BehaviourRecords')) ? AssignIncident::where('student_id', $id)->with('incident', 'user', 'academicYear')->get() : null;
            $student_detail = SmStudent::where('id', $id)->first();
            $routes = SmAssignVehicle::join('sm_vehicles', 'sm_assign_vehicles.vehicle_id', 'sm_vehicles.id')
                ->join('sm_students', 'sm_vehicles.id', 'sm_students.vechile_id')
                ->join('sm_parents', 'sm_parents.id', 'sm_students.parent_id')
                ->where('sm_assign_vehicles.active_status', 1)
                ->where('sm_parents.user_id', Auth::user()->id)
                ->where('sm_assign_vehicles.school_id', Auth::user()->school_id)
                ->get();

            return view('backEnd.parentPanel.transport', compact('routes', 'student_detail', 'behaviourRecordSetting', 'studentBehaviourRecords'));
    }

    public function dormitory($id)
    {
            $behaviourRecordSetting = BehaviourRecordSetting::where('id', 1)->first();
            $studentBehaviourRecords = (moduleStatusCheck('BehaviourRecords')) ? AssignIncident::where('student_id', $id)->with('incident', 'user', 'academicYear')->get() : null;
            $student_detail = SmStudent::where('id', $id)->first();
            $room_lists = SmRoomList::where('active_status', 1)->where('id', $student_detail->room_id)->where('school_id', Auth::user()->school_id)->get();
            $room_lists = $room_lists->groupBy('dormitory_id');
            $room_types = SmRoomType::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            $dormitory_lists = SmDormitoryList::where('active_status', 1)->where('id', $student_detail->dormitory_id)->where('school_id', Auth::user()->school_id)->get();
            return view('backEnd.parentPanel.dormitory', compact('room_lists', 'room_types', 'dormitory_lists', 'student_detail', 'behaviourRecordSetting', 'studentBehaviourRecords'));
    }

    public function homework($id)
    {
            $student_detail = SmStudent::where('id', $id)->first();

            if (moduleStatusCheck('University')) {
                $records = $student_detail->studentRecords;
            } else {
                $records = studentRecords(null, $student_detail->id)->with('homework')->get();
            }
            return view('backEnd.parentPanel.homework', compact('records', 'student_detail'));
    }

    public function homeworkView($class_id, $section_id, $homework_id)
    {
            $homeworkDetails = SmHomework::where('class_id', '=', $class_id)->where('section_id', '=', $section_id)->where('id', '=', $homework_id)->first();
            return view('backEnd.parentPanel.homeworkView', compact('homeworkDetails', 'homework_id'));
    }

    public function unStudentHomeworkView($sem_label_id, $homework)
    {
            $homeworkDetails = SmHomework::find($homework);
            $homework_id = $homework;
            return view('backEnd.studentPanel.studentHomeworkView', compact('homeworkDetails', 'homework_id'));
    }

    public function parentNoticeboard()
    {
            $allNotices = SmNoticeBoard::where('active_status', 1)->where('inform_to', 'LIKE', '%3%')->where('publish_on', '<=', date('Y-m-d'))
                ->orderBy('id', 'DESC')
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.parentPanel.parentNoticeboard', compact('allNotices'));
    }

    public function childListApi(Request $request, $id)
    {
            $parent = SmParent::where('user_id', $id)->first();
            $student_info = DB::table('sm_students')
                ->join('sm_classes', 'sm_classes.id', '=', 'sm_students.class_id')
                ->join('sm_sections', 'sm_sections.id', '=', 'sm_students.section_id')
                // ->join('sm_exams','sm_exams.id','=','sm_exam_types.id' )
                // ->join('sm_subjects','sm_subjects.id','=','sm_result_stores.subject_id' )

                ->where('sm_students.parent_id', '=', $parent->id)

                ->select('sm_students.user_id', 'student_photo', 'sm_students.full_name as student_name', 'class_name', 'section_name', 'roll_no')

                ->where('sm_students.school_id', Auth::user()->school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse($student_info, null);
            }
    }

    public function childProfileApi(Request $request, $id)
    {
            $student_detail = SmStudent::where('id', $id)->first();
            $siblings = SmStudent::where('parent_id', $student_detail->parent_id)->where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            $fees_assigneds = SmFeesAssign::where('student_id', $student_detail->id)->where('school_id', Auth::user()->school_id)->get();
            $fees_discounts = SmFeesAssignDiscount::where('student_id', $student_detail->id)->where('school_id', Auth::user()->school_id)->get();
            $documents = SmStudentDocument::where('student_staff_id', $student_detail->id)->where('type', 'stu')->where('school_id', Auth::user()->school_id)->get();
            $timelines = SmStudentTimeline::where('staff_student_id', $student_detail->id)->where('type', 'stu')->where('visible_to_student', 1)->where('school_id', Auth::user()->school_id)->get();
            $exams = SmExamSchedule::where('class_id', $student_detail->class_id)->where('section_id', $student_detail->section_id)->where('school_id', Auth::user()->school_id)->get();
            $grades = SmMarksGrade::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['student_detail'] = $student_detail->toArray();
                $data['fees_assigneds'] = $fees_assigneds->toArray();
                $data['fees_discounts'] = $fees_discounts->toArray();
                $data['exams'] = $exams->toArray();
                $data['documents'] = $documents->toArray();
                $data['timelines'] = $timelines->toArray();
                $data['siblings'] = $siblings->toArray();
                $data['grades'] = $grades->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            //return view('backEnd.studentPanel.my_profile', compact('student_detail', 'fees_assigneds', 'fees_discounts', 'exams', 'documents', 'timelines', 'siblings', 'grades'));
    }

    public function collectFeesChildApi(Request $request, $id)
    {
            $student = SmStudent::where('id', $id)->first();
            $fees_assigneds = SmFeesAssign::where('student_id', $id)->orderBy('id', 'desc')->where('school_id', Auth::user()->school_id)->get();

            $fees_assigneds2 = DB::table('sm_fees_assigns')
                ->select('sm_fees_types.id as fees_type_id', 'sm_fees_types.name', 'sm_fees_masters.date as due_date', 'sm_fees_masters.amount as amount')
                ->join('sm_fees_masters', 'sm_fees_masters.id', '=', 'sm_fees_assigns.fees_master_id')
                ->join('sm_fees_types', 'sm_fees_types.id', '=', 'sm_fees_masters.fees_type_id')
                ->join('sm_fees_payments', 'sm_fees_payments.fees_type_id', '=', 'sm_fees_masters.fees_type_id')
                ->where('sm_fees_assigns.student_id', $student->id)
                // ->where('sm_fees_payments.student_id', $student->id)
                ->where('sm_fees_assigns.school_id', Auth::user()->school_id)->get();
            $i = 0;

            return $fees_assigneds2;
            foreach ($fees_assigneds2 as $row) {
                $d[$i]['fees_name'] = $row->name;
                $d[$i]['due_date'] = $row->due_date;
                $d[$i]['amount'] = $row->amount;
                $d[$i]['paid'] = DB::table('sm_fees_payments')->where('fees_type_id', $row->fees_type_id)->sum('amount');
                $d[$i]['fine'] = DB::table('sm_fees_payments')->where('fees_type_id', $row->fees_type_id)->sum('fine');
                $d[$i]['discount_amount'] = DB::table('sm_fees_payments')->where('fees_type_id', $row->fees_type_id)->sum('discount_amount');
                $d[$i]['balance'] = ((float) $d[$i]['amount'] + (float) $d[$i]['fine']) - ((float) $d[$i]['paid'] + (float) $d[$i]['discount_amount']);
                $i++;
            }
            $fees_discounts = SmFeesAssignDiscount::where('student_id', $id)->where('school_id', Auth::user()->school_id)->get();

            $applied_discount = [];
            foreach ($fees_discounts as $fees_discount) {
                $fees_payment = SmFeesPayment::where('active_status', 1)->select('fees_discount_id')->where('fees_discount_id', $fees_discount->id)->first();
                if (isset($fees_payment->fees_discount_id)) {
                    $applied_discount[] = $fees_payment->fees_discount_id;
                }
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['fees'] = $d;
                return ApiBaseMethod::sendResponse($fees_assigneds2, null);
            }

            return view('backEnd.feesCollection.collect_fees_student_wise', compact('student', 'fees_assigneds', 'fees_discounts', 'applied_discount'));
    }

    public function classRoutineApi(Request $request, $id)
    {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $user_id = $id;
            } else {
                $user = Auth::user();

                $user_id = $user ? $user->id : $request->user_id;
            }

            $student_detail = SmStudent::where('id', $id)->first();
            $class_id = $student_detail->class_id;
            $section_id = $student_detail->section_id;

            $sm_weekends = SmWeekend::where('school_id', Auth::user()->school_id)->orderBy('order', 'ASC')->where('active_status', 1)->get();
            $class_times = SmClassTime::where('type', 'class')->where('school_id', Auth::user()->school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['student_detail'] = $student_detail->toArray();
                // $data['class_id'] = $class_id;
                // $data['section_id'] = $section_id;
                // $data['sm_weekends'] = $sm_weekends->toArray();
                // $data['class_times'] = $class_times->toArray();

                $weekenD = SmWeekend::where('school_id', Auth::user()->school_id)->get();

                foreach ($weekenD as $weeken) {
                    $data[$weeken->name] = DB::table('sm_class_routine_updates')
                        ->select('sm_class_times.period', 'sm_class_times.start_time', 'sm_class_times.end_time', 'sm_subjects.subject_name', 'sm_class_rooms.room_no')
                        ->join('sm_classes', 'sm_classes.id', '=', 'sm_class_routine_updates.class_id')
                        ->join('sm_sections', 'sm_sections.id', '=', 'sm_class_routine_updates.section_id')
                        ->join('sm_class_times', 'sm_class_times.id', '=', 'sm_class_routine_updates.class_period_id')
                        ->join('sm_subjects', 'sm_subjects.id', '=', 'sm_class_routine_updates.subject_id')
                        ->join('sm_class_rooms', 'sm_class_rooms.id', '=', 'sm_class_routine_updates.room_id')

                        ->where([
                            ['sm_class_routine_updates.class_id', $class_id], ['sm_class_routine_updates.section_id', $section_id], ['sm_class_routine_updates.day', $weeken->id],
                        ])->where('sm_classes.school_id', Auth::user()->school_id)->get();
                }

                return ApiBaseMethod::sendResponse($data, null);
            }

            //return view('backEnd.studentPanel.class_routine', compact('class_times', 'class_id', 'section_id', 'sm_weekends'));
    }

    public function childHomework(Request $request, $id)
    {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $student_detail = SmStudent::where('id', $id)->first();

                $class_id = $student_detail->class->id;
                $subject_list = SmAssignSubject::where([['class_id', $class_id], ['section_id', $student_detail->section_id]])->where('school_id', Auth::user()->school_id)->get();

                $i = 0;
                foreach ($subject_list as $subject) {
                    $homework_subject_list[$subject->subject->subject_name] = $subject->subject->subject_name;
                    $allList[$subject->subject->subject_name] =
                        DB::table('sm_homeworks')
                            ->select('sm_homeworks.description', 'sm_subjects.subject_name', 'sm_homeworks.homework_date', 'sm_homeworks.submission_date', 'sm_homeworks.evaluation_date', 'sm_homeworks.file', 'sm_homeworks.marks', 'sm_homework_students.complete_status as status')
                            ->leftjoin('sm_homework_students', 'sm_homework_students.homework_id', '=', 'sm_homeworks.id')
                            ->leftjoin('sm_subjects', 'sm_subjects.id', '=', 'sm_homeworks.subject_id')
                            ->where('class_id', $student_detail->class_id)->where('section_id', $student_detail->section_id)->where('subject_id', $subject->subject_id)->where('sm_homeworks.school_id', Auth::user()->school_id)->get();
                }

                $homeworkLists = SmHomework::where('class_id', $student_detail->class_id)->where('section_id', $student_detail->section_id)->where('school_id', Auth::user()->school_id)->get();
            }

            $data = [];

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                foreach ($allList as $r) {
                    foreach ($r as $s) {
                        $data[] = $s;
                    }
                }

                return ApiBaseMethod::sendResponse($data, null);
            }

            // return view('backEnd.studentPanel.student_homework', compact('homeworkLists', 'student_detail'));
    }

    public function childAttendanceAPI(Request $request, $id)
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
            $student_detail = SmStudent::where('id', $id)->first();

            $year = $request->year;
            $month = $request->month;
            if ($month < 10) {
                $month = '0'.$month;
            }

            $current_day = date('d');

            $days = cal_days_in_month(CAL_GREGORIAN, $month, $request->year);
            $days2 = cal_days_in_month(CAL_GREGORIAN, $month - 1, $request->year);
            $previous_month = $month - 1;
            $previous_date = $year.'-'.$previous_month.'-'.$days2;
            $previousMonthDetails['date'] = $previous_date;
            $previousMonthDetails['day'] = $days2;
            $previousMonthDetails['week_name'] = date('D', strtotime($previous_date));
            $attendances = SmStudentAttendance::where('student_id', $student_detail->id)
                ->where('attendance_date', 'like', '%'.$request->year.'-'.$month.'%')
                ->select('attendance_type', 'attendance_date')
                ->where('school_id', Auth::user()->school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data['attendances'] = $attendances;
                $data['previousMonthDetails'] = $previousMonthDetails;
                $data['days'] = $days;
                $data['year'] = $year;
                $data['month'] = $month;
                $data['current_day'] = $current_day;
                $data['status'] = 'Present: P, Late: L, Absent: A, Holiday: H, Half Day: F';

                return ApiBaseMethod::sendResponse($data, null);
            }
            //Test
            //return view('backEnd.studentPanel.student_attendance', compact('attendances', 'days', 'year', 'month', 'current_day'));
    }

    public function aboutApi(Request $request)
    {
            $about = DB::table('sm_general_settings')
                ->join('sm_languages', 'sm_general_settings.language_id', '=', 'sm_languages.id')
                ->join('sm_academic_years', 'sm_general_settings.session_id', '=', 'sm_academic_years.id')
                ->join('sm_about_pages', 'sm_general_settings.school_id', '=', 'sm_about_pages.school_id')
                ->select('main_description', 'school_name', 'site_title', 'school_code', 'address', 'phone', 'email', 'logo', 'sm_languages.language_name', 'year as session', 'copyright_text')
                ->where('sm_general_settings.school_id', Auth::user()->school_id)->first();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {

                return ApiBaseMethod::sendResponse($about, null);
            }
    }

    public function StudentDownload(string $file_name)
    {
            $file = public_path().'/uploads/student/timeline/'.$file_name;
            if (file_exists($file)) {
                return Response::download($file);
            }
            return redirect()->back();
    }

    private function addUser($user_id, int $role_id, $username, $email, $phone_number)
    {
        try {

            $user = $user_id == null ? new User() : User::find($user_id);
            $user->role_id = $role_id;
            if ($username != null) {
                $user->username = $username;
            }

            if ($email != null) {
                $user->email = $email;
            }

            if ($phone_number != null) {
                $user->phone_number = $phone_number;
            }

            $user->save();

            return $user;
        } catch (Exception $exception) {
            dd($exception);
            Log::info($exception->getMessage());
        }

        return null;
    }
    
}
