<?php

namespace App\Http\Controllers\Admin\Hr;

use App\User;
use Exception;
use Throwable;
use App\SmStaff;
use App\SmUserLog;
use App\SmBaseSetup;
use App\ApiBaseMethod;
use App\SmDesignation;
use App\SmLeaveDefine;
use App\SmLeaveRequest;
use BaconQrCode\Writer;
use App\SmGeneralSettings;
use App\SmHumanDepartment;
use App\SmStudentDocument;
use App\SmStudentTimeline;
use App\InfixModuleManager;
use App\SmHrPayrollGenerate;
use App\Traits\CustomFields;
use Illuminate\Http\Request;
use App\Models\SmCustomField;
use App\Models\SmExpertTeacher;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;
use App\Scopes\ActiveStatusSchoolScope;
use BaconQrCode\Renderer\ImageRenderer;
use Illuminate\Support\Facades\Session;
use App\Models\SmStaffRegistrationField;
use Modules\MultiBranch\Entities\Branch;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Admin\Hr\staffRequest;
use Illuminate\Validation\ValidationException;
use Modules\RolePermission\Entities\InfixRole;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;

class SmStaffController extends Controller
{
    use CustomFields;

    /**
     * @var bool|non-empty-string
     */
    public $User;

    /**
     * @var bool|non-empty-string
     */
    public $SmGeneralSettings;

    /**
     * @var bool|non-empty-string
     */
    public $SmUserLog;

    /**
     * @var bool|non-empty-string
     */
    public $InfixModuleManager;

    /**
     * @var string
     */
    public $URL;

    public function __construct()
    {

        $this->User = json_encode(User::find(1));
        $this->SmGeneralSettings = json_encode(generalSetting());
        $this->SmUserLog = json_encode(SmUserLog::find(1));
        $this->InfixModuleManager = json_encode(InfixModuleManager::find(1));
        $this->URL = url('/');
    }

    public function staffList(Request $request)
    {
        /*
        try {
        */

        $roles = InfixRole::query();
        $roles->whereNotIn('id', [2, 3]);
        if (Auth::user()->role_id != 1) {
            $roles->whereNotIn('id', [1]);
        }

        $roles = $roles->where('is_saas', 0)
            ->where('active_status', '=', '1')
            ->where(function ($q): void {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })
            ->orderBy('name', 'asc')
            ->get();

        return view('backEnd.humanResource.staff_list', ['roles' => $roles]);

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function roleStaffList(Request $request, $role_id)
    {

        /*
        try {
        */
        $staffs_api = DB::table('sm_staffs')
            ->where('is_saas', 0)
            ->where('sm_staffs.active_status', 1)
            ->where('role_id', '=', $role_id)
            ->join('roles', 'sm_staffs.role_id', '=', 'roles.id')
            ->join('sm_human_departments', 'sm_staffs.department_id', '=', 'sm_human_departments.id')
            ->join('sm_designations', 'sm_staffs.designation_id', '=', 'sm_designations.id')
            ->join('sm_base_setups', 'sm_staffs.gender_id', '=', 'sm_base_setups.id')
            ->where('sm_staffs.school_id', Auth::user()->school_id)
            ->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {

            return ApiBaseMethod::sendResponse($staffs_api, null);
        }

        if (moduleStatusCheck('MultiBranch')) {
            $branches = Branch::where('active_status', 1)->get();

            return view('backEnd.humanResource.staff_list', ['staffs' => $staffs, 'roles' => $roles, 'branches' => $branches]);
        }

        /*
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function addStaff()
    {

        if (isSubscriptionEnabled() && auth()->user()->school_id != 1) {

            $active_staff = SmStaff::withOutGlobalScope(ActiveStatusSchoolScope::class)->where('role_id', '!=', 1)->where('school_id', Auth::user()->school_id)->where('active_status', 1)->where('is_saas', 0)->count();

            if (\Modules\Saas\Entities\SmPackagePlan::staff_limit() <= $active_staff) {

                Toastr::error('Your staff limit has been crossed.', 'Failed');

                return redirect()->back();

            }
        }
        /*
        try {
        */
        $max_staff_no = SmStaff::withOutGlobalScope(ActiveStatusSchoolScope::class)->where('is_saas', 0)
            ->where('school_id', Auth::user()->school_id)
            ->max('staff_no');

        $roles = InfixRole::where('is_saas', 0)->where('active_status', '=', 1)
            ->where(function ($q): void {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })
            ->whereNotIn('id', [1, 2, 3])
            ->orderBy('name', 'asc')
            ->get();

        $departments = SmHumanDepartment::where('is_saas', 0)
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        $designations = SmDesignation::where('is_saas', 0)
            ->orderBy('title', 'asc')
            ->get(['id', 'title']);

        $marital_ststus = SmBaseSetup::where('base_group_id', '=', '4')
            ->orderBy('base_setup_name', 'asc')
            ->where('school_id', auth()->user()->school_id)
            ->get(['id', 'base_setup_name']);

        $genders = SmBaseSetup::where('base_group_id', '=', '1')
            ->orderBy('base_setup_name', 'asc')
            ->where('school_id', auth()->user()->school_id)
            ->get(['id', 'base_setup_name']);

        $custom_fields = SmCustomField::where('form_name', 'staff_registration')->get();
        $is_required = SmStaffRegistrationField::where('school_id', auth()->user()->school_id)->where('is_required', 1)->pluck('field_name')->toArray();

        session()->forget('staff_photo');

        return view('backEnd.humanResource.addStaff', ['roles' => $roles, 'departments' => $departments, 'designations' => $designations, 'marital_ststus' => $marital_ststus, 'max_staff_no' => $max_staff_no, 'genders' => $genders, 'custom_fields' => $custom_fields, 'is_required' => $is_required]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function staffPicStore(Request $request)
    {

        /*
        try {
        */
        $validator = Validator::make($request->all(), [
            'logo_pic' => 'sometimes|required|mimes:jpg,png|max:40000',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'valid image upload'], 201);
        }

        if ($request->hasFile('logo_pic')) {
            $file = $request->file('logo_pic');
            $images = Image::make($file)->insert($file);
            $pathImage = 'public/uploads/staff/';
            if (! file_exists($pathImage)) {
                mkdir($pathImage, 0777, true);
                $name = md5($file->getClientOriginalName().time()).'.'.'png';
                $images->save('public/uploads/staff/'.$name);
                $imageName = 'public/uploads/staff/'.$name;
                // $data->staff_photo =  $imageName;
                Session::put('staff_photo', $imageName);
            } else {
                $name = md5($file->getClientOriginalName().time()).'.'.'png';
                if (file_exists(Session::get('staff_photo'))) {
                    File::delete(Session::get('staff_photo'));
                }

                $images->save('public/uploads/staff/'.$name);
                $imageName = 'public/uploads/staff/'.$name;
                // $data->staff_photo =  $imageName;
                Session::put('staff_photo', $imageName);
            }
        }

        return response()->json(['success' => 'success'], 200);
        /*
        } catch (Exception $exception) {
            return response()->json(['error' => 'error'], 201);
        }
        */
    }

    public function staffStore(staffRequest $staffRequest)
    {
        // return $request->all();
        /*
        try {
        */
        DB::beginTransaction();
        try {
            $designation = 'public/uploads/resume/';

            $user = new User();
            $user->role_id = $staffRequest->role_id;
            $user->username = $staffRequest->mobile ?: $staffRequest->email;
            $user->email = $staffRequest->email;
            $user->full_name = $staffRequest->first_name.' '.$staffRequest->last_name;
            $user->password = Hash::make(123456);
            $user->school_id = Auth::user()->school_id;
            $user->save();

            if ($staffRequest->role_id == 5) {
                $this->assignChatGroup($user);
            }

            $basic_salary = empty($staffRequest->basic_salary) ? 0 : $staffRequest->basic_salary;

            $smStaff = new SmStaff();
            $smStaff->staff_no = $staffRequest->staff_no;
            $smStaff->role_id = $staffRequest->role_id;
            $smStaff->department_id = $staffRequest->department_id;
            $smStaff->designation_id = $staffRequest->designation_id;

            if (moduleStatusCheck('MultiBranch')) {
                if (Auth::user()->is_administrator == 'yes') {
                    $smStaff->branch_id = $staffRequest->branch_id;
                } else {
                    $smStaff->branch_id = Auth::user()->branch_id;
                }
            }

            $smStaff->first_name = $staffRequest->first_name;
            $smStaff->last_name = $staffRequest->last_name;
            $smStaff->full_name = $staffRequest->first_name.' '.$staffRequest->last_name;
            $smStaff->fathers_name = $staffRequest->fathers_name;
            $smStaff->mothers_name = $staffRequest->mothers_name;
            $smStaff->email = $staffRequest->email;
            $smStaff->school_id = Auth::user()->school_id;
            $smStaff->staff_photo = session()->get('staff_photo') ?? fileUpload($staffRequest->staff_photo, $designation);
            $smStaff->show_public = $staffRequest->show_public;
            $smStaff->gender_id = $staffRequest->gender_id;
            $smStaff->marital_status = $staffRequest->marital_status;
            $smStaff->date_of_birth = date('Y-m-d', strtotime($staffRequest->date_of_birth));
            $smStaff->date_of_joining = date('Y-m-d', strtotime($staffRequest->date_of_joining));
            $smStaff->mobile = $staffRequest->mobile ?? null;
            $smStaff->emergency_mobile = $staffRequest->emergency_mobile;
            $smStaff->current_address = $staffRequest->current_address;
            $smStaff->permanent_address = $staffRequest->permanent_address;
            $smStaff->qualification = $staffRequest->qualification;
            $smStaff->experience = $staffRequest->experience;
            $smStaff->epf_no = $staffRequest->epf_no;
            $smStaff->basic_salary = $basic_salary;
            $smStaff->contract_type = $staffRequest->contract_type;
            $smStaff->location = $staffRequest->location;
            $smStaff->bank_account_name = $staffRequest->bank_account_name;
            $smStaff->bank_account_no = $staffRequest->bank_account_no;
            $smStaff->bank_name = $staffRequest->bank_name;
            $smStaff->bank_brach = $staffRequest->bank_brach;
            $smStaff->facebook_url = $staffRequest->facebook_url;
            $smStaff->twiteer_url = $staffRequest->twiteer_url;
            $smStaff->linkedin_url = $staffRequest->linkedin_url;
            $smStaff->instragram_url = $staffRequest->instragram_url;
            $smStaff->user_id = $user->id;
            $smStaff->resume = fileUpload($staffRequest->resume, $designation);
            $smStaff->joining_letter = fileUpload($staffRequest->joining_letter, $designation);
            $smStaff->other_document = fileUpload($staffRequest->other_document, $designation);
            $smStaff->driving_license = $staffRequest->driving_license;

            // Custom Field Start
            if ($staffRequest->customF) {
                $dataImage = $staffRequest->customF;
                foreach ($dataImage as $label => $field) {
                    if (is_object($field) && $field != '') {
                        $dataImage[$label] = fileUpload($field, 'public/uploads/customFields/');
                    }
                }

                $smStaff->custom_field_form_name = 'staff_registration';
                $smStaff->custom_field = json_encode($dataImage, true);
            }

            // Custom Field End

            // leaver define data  insert for staff
            $results = $smStaff->save();
            generateQRCode('staff-'.$smStaff->id);
            $smStaff->toArray();

            $st_role_id = $staffRequest->role_id;
            $school_id = Auth::user()->school_id;
            $academic_id = getAcademicId();
            $user_id = $user->id;

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

            DB::commit();
            // Expert Staff Start
            if ($staffRequest->show_public == 1) {
                $smExpertTeacher = new SmExpertTeacher();
                $smExpertTeacher->staff_id = $smStaff->id;
                $smExpertTeacher->created_by = auth()->user()->id;
                $smExpertTeacher->school_id = auth()->user()->school_id;
                $smExpertTeacher->save();
            }

            // Expert Staff End
            $user_info = [];
            if ($staffRequest->email != '') {
                $user_info[] = ['email' => $staffRequest->email, 'id' => $smStaff->id, 'slug' => 'staff'];
            }

            try {
                if ($user_info != []) {
                    $compact['user_email'] = $staffRequest->email;
                    $compact['id'] = $smStaff->id;
                    $compact['slug'] = 'staff';
                    $compact['staff_name'] = $smStaff->full_name;
                    @send_mail($staffRequest->email, $smStaff->full_name, 'staff_login_credentials', $compact);
                    @send_sms($staffRequest->mobile, 'staff_credentials', $compact);
                }
            } catch (Exception $e) {
                Toastr::success('Operation successful', 'Success');

                return redirect('staff-directory');
            }

            Toastr::success('Operation successful', 'Success');

            return redirect('staff-directory');
        } catch (Exception $e) {
            DB::rollback();
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

        Toastr::success('Operation successful', 'Success');

        return redirect('staff-directory');
        /*
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function editStaff($id)
    {
        if (auth()->user()->staff->id != $id) {
            abort_if(! userPermission('editStaff'), 404);
        }
        /*
        try {
        */
        $editData = SmStaff::withOutGlobalScopes()->where('school_id', auth()->user()->school_id)->find($id);
        // $has_permission = [];
        if (auth()->user()->staff->id == $id && auth()->user()->role_id != 1) {
            $has_permission = SmStaffRegistrationField::where('school_id', auth()->user()->school_id)
                ->where('staff_edit', 1)->pluck('field_name')->toArray();
        } else {
            $has_permission = SmStaffRegistrationField::where('school_id', auth()->user()->school_id)
                ->pluck('field_name')->toArray();
        }

        $max_staff_no = SmStaff::withOutGlobalScopes()->where('is_saas', 0)->where('school_id', Auth::user()->school_id)->max('staff_no');

        $roles = InfixRole::where('is_saas', 0)->where('active_status', '=', 1)
            ->where(function ($q): void {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })
            ->whereNotIn('id', [1, 2, 3])
            ->orderBy('id', 'desc')
            ->get();

        $departments = SmHumanDepartment::where('active_status', '=', '1')
            ->where('school_id', Auth::user()->school_id)->get();
        $designations = SmDesignation::where('active_status', '=', '1')
            ->where('school_id', Auth::user()->school_id)->get();
        $marital_ststus = SmBaseSetup::where('active_status', '=', '1')
            ->where('base_group_id', '=', '4')
            ->where('school_id', auth()->user()->school_id)
            ->get();
        $genders = SmBaseSetup::where('active_status', '=', '1')
            ->where('base_group_id', '=', '1')
            ->where('school_id', auth()->user()->school_id)
            ->get();

        // Custom Field Start
        $custom_fields = SmCustomField::where('form_name', 'staff_registration')
            ->where('school_id', Auth::user()->school_id)->get();
        $custom_filed_values = json_decode($editData->custom_field);
        $student = $editData;
        // Custom Field End
        $is_required = SmStaffRegistrationField::where('school_id', auth()->user()->school_id)->where('is_required', 1)->pluck('field_name')->toArray();

        return view('backEnd.humanResource.editStaff', ['editData' => $editData, 'roles' => $roles, 'departments' => $departments, 'designations' => $designations, 'marital_ststus' => $marital_ststus, 'max_staff_no' => $max_staff_no, 'genders' => $genders, 'custom_fields' => $custom_fields, 'custom_filed_values' => $custom_filed_values, 'student' => $student, 'is_required' => $is_required, 'has_permission' => $has_permission]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function UpdateStaffApi(Request $request)
    {

        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'field_name' => 'required',
                'staff_photo' => 'sometimes|nullable|mimes:jpg,jpeg,png',
            ]);
        }

        if ($validator->fails() && ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
        }

        /*
        try {
        */
        if (! empty($request->field_name)) {
            $request_string = $request->field_name;
            $request_id = $request->id;
            $data = SmStaff::withOutGlobalScopes()->where('school_id', auth()->user()->school_id)->find($request_id);
            $data->$request_string = $request->$request_string;
            if ($request_string == 'first_name') {
                $full_name = $request->$request_string.' '.$data->last_name;
                $data->full_name = $full_name;
            } elseif ($request_string == 'last_name') {
                $full_name = $data->first_name.' '.$request->$request_string;
                $data->full_name = $full_name;
            } elseif ($request_string == 'staff_photo') {
                $maxFileSize = SmGeneralSettings::first('file_size')->file_size;
                $file = $request->file('staff_photo');
                $fileSize = filesize($file);
                $fileSizeKb = ($fileSize / 1000000);
                if ($fileSizeKb >= $maxFileSize) {
                    Toastr::error('Max upload file size '.$maxFileSize.' Mb is set in system', 'Failed');

                    return redirect()->back();
                }

                $file = $request->file('staff_photo');
                $images = Image::make($file)->resize(100, 100)->insert($file, 'center');
                $staff_photos = md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();
                $images->save('public/uploads/staff/'.$staff_photos);
                $staff_photo = 'public/uploads/staff/'.$staff_photos;
                $data->staff_photo = $staff_photo;
            }

            $data->save();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['message'] = 'Updated';
                $data['flag'] = true;

                return ApiBaseMethod::sendResponse($data, null);
            }
        } elseif (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['message'] = 'Invalid Input';
            $data['flag'] = false;

            return ApiBaseMethod::sendError($data, null);
        }
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function staffProfileUpdate(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'logo_pic' => 'sometimes|required|mimes:jpg,png|max:40000',

        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'Image Validation Failed'], 201);
        }
        /*
                try {
        */
        if (checkAdmin() == true) {
            $data = SmStaff::withOutGlobalScopes()->where('school_id', auth()->user()->school_id)->find($id);
        } else {
            $data = SmStaff::withOutGlobalScopes()->where('id', $id)->where('school_id', Auth::user()->school_id)->first();
        }

        if ($request->hasFile('logo_pic')) {
            $file = $request->file('logo_pic');
            $images = Image::make($file)->insert($file);
            $pathImage = 'public/uploads/staff/';
            if (! file_exists($pathImage)) {
                mkdir($pathImage, 0777, true);
                $name = md5($file->getClientOriginalName().time()).'.'.'png';
                $images->save('public/uploads/staff/'.$name);
                $imageName = 'public/uploads/staff/'.$name;
                $data->staff_photo = $imageName;
            } else {
                $name = md5($file->getClientOriginalName().time()).'.'.'png';
                if (file_exists($data->staff_photo)) {
                    File::delete($data->staff_photo);
                }

                $images->save('public/uploads/staff/'.$name);
                $imageName = 'public/uploads/staff/'.$name;
                $data->staff_photo = $imageName;
            }

            $data->save();
        }

        return response()->json('success', 200);
        /*
        } catch (Exception $exception) {
            return response()->json(['error' => 'error'], 201);
        }
        */
    }

    public function staffUpdate(staffRequest $staffRequest)
    {
        /*
        try {
        */
        $designation = 'public/uploads/resume/';

        $staff = SmStaff::withOutGlobalScopes()->where('school_id', auth()->user()->school_id)->find($staffRequest->staff_id);
        if ($staffRequest->filled('basic_salary')) {
            $basic_salary = empty($staffRequest->basic_salary) ? 0 : $staffRequest->basic_salary;
        }

        if ($staffRequest->filled('staff_no')) {
            $staff->staff_no = $staffRequest->staff_no;
        }

        if ($staffRequest->filled('role_id')) {
            $staff->role_id = $staffRequest->role_id;
        }

        if ($staffRequest->filled('department_id')) {
            $staff->department_id = $staffRequest->department_id;
        }

        if ($staffRequest->filled('designation_id')) {
            $staff->designation_id = $staffRequest->designation_id;
        }

        if ($staffRequest->filled('first_name')) {
            $staff->first_name = $staffRequest->first_name;
        }

        if ($staffRequest->filled('last_name')) {
            $staff->last_name = $staffRequest->last_name;
        }

        if ($staffRequest->filled('first_name') || $staffRequest->filled('last_name')) {
            $staff->full_name = $staffRequest->first_name.' '.$staffRequest->last_name;
        }

        if ($staffRequest->filled('fathers_name')) {
            $staff->fathers_name = $staffRequest->fathers_name;
        }

        if ($staffRequest->filled('mothers_name')) {
            $staff->mothers_name = $staffRequest->mothers_name;
        }

        if ($staffRequest->filled('email')) {
            $staff->email = $staffRequest->email;
        }

        if ($staffRequest->filled('staff_photo')) {
            $staff->staff_photo = fileUpdate($staff->staff_photo, $staffRequest->staff_photo, $designation);
        }

        if ($staffRequest->filled('show_public')) {
            $staff->show_public = $staffRequest->show_public;
        }

        if ($staffRequest->filled('gender_id')) {
            $staff->gender_id = $staffRequest->gender_id;
        }

        if ($staffRequest->filled('marital_status')) {
            $staff->marital_status = $staffRequest->marital_status;
        }

        if ($staffRequest->filled('date_of_birth')) {
            $staff->date_of_birth = date('Y-m-d', strtotime($staffRequest->date_of_birth));
        }

        if ($staffRequest->filled('date_of_joining')) {
            $staff->date_of_joining = date('Y-m-d', strtotime($staffRequest->date_of_joining));
        }

        if ($staffRequest->filled('mobile')) {
            $staff->mobile = $staffRequest->mobile;
        }

        if ($staffRequest->filled('emergency_mobile')) {
            $staff->emergency_mobile = $staffRequest->emergency_mobile;
        }

        if ($staffRequest->filled('current_address')) {
            $staff->current_address = $staffRequest->current_address;
        }

        if ($staffRequest->filled('permanent_address')) {
            $staff->permanent_address = $staffRequest->permanent_address;
        }

        if ($staffRequest->filled('qualification')) {
            $staff->qualification = $staffRequest->qualification;
        }

        if ($staffRequest->filled('experience')) {
            $staff->experience = $staffRequest->experience;
        }

        if ($staffRequest->filled('epf_no')) {
            $staff->epf_no = $staffRequest->epf_no;
        }

        if ($staffRequest->filled('basic_salary')) {
            $staff->basic_salary = $basic_salary;
        }

        if ($staffRequest->filled('contract_type')) {
            $staff->contract_type = $staffRequest->contract_type;
        }

        if ($staffRequest->filled('location')) {
            $staff->location = $staffRequest->location;
        }

        if ($staffRequest->filled('bank_account_name')) {
            $staff->bank_account_name = $staffRequest->bank_account_name;
        }

        if ($staffRequest->filled('bank_account_no')) {
            $staff->bank_account_no = $staffRequest->bank_account_no;
        }

        if ($staffRequest->filled('bank_name')) {
            $staff->bank_name = $staffRequest->bank_name;
        }

        if ($staffRequest->filled('bank_brach')) {
            $staff->bank_brach = $staffRequest->bank_brach;
        }

        if ($staffRequest->filled('facebook_url')) {
            $staff->facebook_url = $staffRequest->facebook_url;
        }

        if ($staffRequest->filled('twiteer_url')) {
            $staff->twiteer_url = $staffRequest->twiteer_url;
        }

        if ($staffRequest->filled('linkedin_url')) {
            $staff->linkedin_url = $staffRequest->linkedin_url;
        }

        if ($staffRequest->filled('instragram_url')) {
            $staff->instragram_url = $staffRequest->instragram_url;
        }

        if ($staffRequest->filled('user_id')) {
        }

        if ($staffRequest->filled('resume')) {
            $staff->resume = fileUpdate($staff->resume, $staffRequest->resume, $designation);
        }

        if ($staffRequest->filled('joining_letter')) {
            $staff->joining_letter = fileUpdate($staff->joining_letter, $staffRequest->joining_letter, $designation);
        }

        if ($staffRequest->filled('other_document')) {
            $staff->other_document = fileUpdate($staff->other_document, $staffRequest->other_document, $designation);
        }

        if ($staffRequest->filled('driving_license')) {
            $staff->driving_license = $staffRequest->driving_license;
        }

        if ($staffRequest->filled('staff_bio')) {
            $staff->staff_bio = $staffRequest->staff_bio;
        }

        // Custom Field Start
        if ($staffRequest->customF) {
            $dataImage = $staffRequest->customF;
            foreach ($dataImage as $label => $field) {
                if (is_object($field) && $field != '') {
                    $dataImage[$label] = fileUpload($field, 'public/uploads/customFields/');
                }
            }
            $staff->custom_field_form_name = 'staff_registration';
            $staff->custom_field = json_encode($dataImage, true);
        }

        // Custom Field End
        // Expert Staff Start
        $expertExists = SmExpertTeacher::where('staff_id', $staffRequest->staff_id)->where('school_id', auth()->user()->school_id)->first();
        if ($staffRequest->show_public == 1 && $expertExists == null) {
            $smExpertTeacher = new SmExpertTeacher();
            $smExpertTeacher->staff_id = $staff->id;
            $smExpertTeacher->created_by = auth()->user()->id;
            $smExpertTeacher->school_id = auth()->user()->school_id;
            $smExpertTeacher->save();
        }

        if ($staffRequest->show_public == 0 && $expertExists != null) {
            $expertExists->delete();
        }

        // Expert Staff End
        $result = $staff->update();

        $user = User::find($staff->user_id);

        if ($staffRequest->filled('mobile') || $staffRequest->filled('email')) {
            $user->username = $staffRequest->mobile ?: $staffRequest->email;
        }

        if ($staffRequest->filled('email')) {
            $user->email = $staffRequest->email;
        }

        if ($staffRequest->filled('role_id')) {
            if ($user->role_id != 5 && $staffRequest->role_id == 5) {
                // assign to group
                $this->assignChatGroup($user);
            }

            if ($user->role_id == 5 && $staffRequest->role_id != 5) {
                // remove chat group
                $this->removeChatGroup($user);
            }

            $user->role_id = $staffRequest->role_id;
        }

        if ($staffRequest->filled('first_name') || $staffRequest->filled('last_name')) {
            $user->full_name = $staffRequest->first_name.' '.$staffRequest->last_name;
        }

        if (moduleStatusCheck('Lms') && $staffRequest->filled('staff_bio')) {
            $user->staff_bio = $staffRequest->staff_bio;
        }

        $user->update();

        Toastr::success('Operation successful', 'Success');

        return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function staffRoles(Request $request)
    {

        /*
        try {
        */
        $roles = InfixRole::where('is_saas', 0)
            ->where('active_status', '=', '1')
            ->select('id', 'name', 'type')
            ->where('id', '!=', 2)
            ->where('id', '!=', 3)
            ->where(function ($q): void {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {

            return ApiBaseMethod::sendResponse($roles, null);
        }
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function viewStaff($id)
    {

        /*
        try {
        */

        if (checkAdmin() == true) {
            $staffDetails = SmStaff::withOutGlobalScope(ActiveStatusSchoolScope::class)->find($id);
        } else {
            $staffDetails = SmStaff::withOutGlobalScope(ActiveStatusSchoolScope::class)->where('id', $id)->where('school_id', Auth::user()->school_id)->first();
        }

        if (Auth::user()->role_id != 1 && (Auth::user()->staff->id != $id && ! userPermission('viewStaff'))) {
            Toastr::error('You are not authorized to view this page', 'Failed');

            return redirect()->back();
        }

        if (! empty($staffDetails)) {
            $staffPayrollDetails = SmHrPayrollGenerate::where('staff_id', $id)->where('payroll_status', '!=', 'NG')->where('school_id', Auth::user()->school_id)->get();
            $staffLeaveDetails = SmLeaveRequest::where('staff_id', $staffDetails->user_id)->where('school_id', Auth::user()->school_id)->get();
            $staffDocumentsDetails = SmStudentDocument::where('student_staff_id', $id)->where('type', '=', 'stf')->where('school_id', Auth::user()->school_id)->get();
            $timelines = SmStudentTimeline::where('staff_student_id', $id)->where('type', '=', 'stf')->where('school_id', Auth::user()->school_id)->get();

            $custom_field_data = $staffDetails->custom_field;

            $custom_field_values = is_null($custom_field_data) ? null : json_decode($custom_field_data);

            $qr_code_path = public_path('qr_codes/staff-'.$staffDetails->id.'-qrcode.png');

            if (! file_exists($qr_code_path)) {
                $imageRenderer = new ImageRenderer(
                    new RendererStyle(400),
                    new ImagickImageBackEnd()
                );
                $writer = new Writer($imageRenderer);
                $qrcode = $writer->writeFile('staff-'.$staffDetails->id, $qr_code_path);
            }

            return view('backEnd.humanResource.viewStaff', ['staffDetails' => $staffDetails, 'staffPayrollDetails' => $staffPayrollDetails, 'staffLeaveDetails' => $staffLeaveDetails, 'staffDocumentsDetails' => $staffDocumentsDetails, 'timelines' => $timelines, 'custom_field_values' => $custom_field_values]);
        }

        Toastr::error('Something went wrong, please try again', 'Failed');

        return redirect()->back();

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function searchStaff(Request $request)
    {
        /*
                try {
                */
        $data = [];
        $data['role_id'] = $request->role_id;
        $data['staff_no'] = $request->staff_no;
        $data['staff_name'] = $request->staff_name;
        $staff = SmStaff::withOutGlobalScope(ActiveStatusSchoolScope::class);
        $staff->where('is_saas', 0)->where('active_status', 1);
        if ($request->role_id != '') {
            $staff->where(function ($q) use ($request): void {
                $q->where('role_id', $request->role_id)->orWhere('previous_role_id', $request->role_id);
            });
        }

        if ($request->staff_no != '') {
            $staff->where('staff_no', $request->staff_no);
        }

        if ($request->staff_name != '') {
            $staff->where('full_name', 'like', '%'.$request->staff_name.'%');
        }

        if (Auth::user()->role_id != 1) {
            $staff->where('role_id', '!=', 1);
        }

        $all_staffs = $staff->where('school_id', Auth::user()->school_id)->get();

        if (Auth::user()->role_id != 1) {
            $roles = InfixRole::where('is_saas', 0)->where('active_status', '=', '1')->where('id', '!=', 1)->where('id', '!=', 2)->where('id', '!=', 3)->where('id', '!=', 5)->where(function ($q): void {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();
        } else {
            $roles = InfixRole::where('is_saas', 0)->where('active_status', '=', '1')->where('id', '!=', 2)->where('id', '!=', 3)->where(function ($q): void {
                $q->where('school_id', Auth::user()->school_id)->orWhere('type', 'System');
            })->get();
        }

        return view('backEnd.humanResource.staff_list', ['all_staffs' => $all_staffs, 'roles' => $roles, 'data' => $data]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function uploadStaffDocuments($staff_id)
    {

        /*
        try {
        */
        return view('backEnd.humanResource.uploadStaffDocuments', ['staff_id' => $staff_id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function saveUploadDocument(Request $request)
    {
        $request->validate([
            'staff_upload_document' => 'sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt',
        ]);
        /*
        try {
        */
        if ($request->file('staff_upload_document') != '' && $request->title != '') {
            $document_photo = '';
            if ($request->file('staff_upload_document') != '') {
                $maxFileSize = SmGeneralSettings::first('file_size')->file_size;
                $file = $request->file('staff_upload_document');
                $fileSize = filesize($file);
                $fileSizeKb = ($fileSize / 1000000);
                if ($fileSizeKb >= $maxFileSize) {
                    Toastr::error('Max upload file size '.$maxFileSize.' Mb is set in system', 'Failed');

                    return redirect()->back()->with(['staffDocuments' => 'active']);
                }

                $file = $request->file('staff_upload_document');
                $document_photo = 'staff-'.md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();
                $file->move('public/uploads/staff/document/', $document_photo);
                $document_photo = 'public/uploads/staff/document/'.$document_photo;
            }

            $smStudentDocument = new SmStudentDocument();
            $smStudentDocument->title = $request->title;
            $smStudentDocument->student_staff_id = $request->staff_id;
            $smStudentDocument->type = 'stf';
            $smStudentDocument->file = $document_photo;
            $smStudentDocument->created_by = Auth()->user()->id;
            $smStudentDocument->school_id = Auth::user()->school_id;
            $smStudentDocument->academic_id = getAcademicId();
            $results = $smStudentDocument->save();
        }

        if ($results) {
            Toastr::success('Document uploaded successfully', 'Success');

            return redirect()->back()->with(['staffDocuments' => 'active']);
        }

        Toastr::error('Operation Failed', 'Failed');

        return redirect()->back()->with(['staffDocuments' => 'active']);

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back()->with(['staffDocuments' => 'active']);
        }
        */
    }

    public function deleteStaffDocumentView(Request $request, $id)
    {

        /*
        try {
        */
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendResponse($id, null);
        }

        return view('backEnd.humanResource.deleteStaffDocumentView', ['id' => $id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteStaffDocument($id)
    {
        /*
        try {
        */
        $result = SmStudentDocument::where('student_staff_id', $id)->first();
        if ($result) {

            if (file_exists($result->file)) {
                File::delete($result->file);
            }
            $result->delete();
            Toastr::success('Operation successful', 'Success');
            return redirect()->back()->with(['staffDocuments' => 'active']);
        }
        Toastr::error('Operation Failed', 'Failed');
        return redirect()->back()->with(['staffDocuments' => 'active']);

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back()->with(['staffDocuments' => 'active']);
        }
        */
    }

    public function addStaffTimeline($id)
    {
        /*
        try {
        */
        return view('backEnd.humanResource.addStaffTimeline', ['id' => $id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function storeStaffTimeline(Request $request)
    {

        $request->validate([
            'document_file_4' => 'sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt',
        ]);
        /*
        try {
        */
        if ($request->title != '') {

            $document_photo = '';
            if ($request->file('document_file_4') != '') {
                $maxFileSize = SmGeneralSettings::first('file_size')->file_size;
                $file = $request->file('document_file_4');
                $fileSize = filesize($file);
                $fileSizeKb = ($fileSize / 1000000);
                if ($fileSizeKb >= $maxFileSize) {
                    Toastr::error('Max upload file size '.$maxFileSize.' Mb is set in system', 'Failed');
                    return redirect()->back();
                }
                $file = $request->file('document_file_4');
                $document_photo = 'stu-'.md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();
                $file->move('public/uploads/staff/timeline/', $document_photo);
                $document_photo = 'public/uploads/staff/timeline/'.$document_photo;
            }

            $smStudentTimeline = new SmStudentTimeline();
            $smStudentTimeline->staff_student_id = $request->staff_student_id;
            $smStudentTimeline->title = $request->title;
            $smStudentTimeline->type = 'stf';
            $smStudentTimeline->date = date('Y-m-d', strtotime($request->date));
            $smStudentTimeline->description = $request->description;

            if (property_exists($request, 'visible_to_student') && $request->visible_to_student != null) {
                $smStudentTimeline->visible_to_student = $request->visible_to_student;
            }

            $smStudentTimeline->file = $document_photo;
            $smStudentTimeline->school_id = Auth::user()->school_id;
            $smStudentTimeline->academic_id = getAcademicId();
            $smStudentTimeline->save();
        }
        Toastr::success('Document uploaded successfully', 'Success');
        return redirect()->back()->with(['staffTimeline' => 'active']);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back()->with(['staffTimeline' => 'active']);
        }
        */
    }

    public function deleteStaffTimelineView($id)
    {

        /*
        try {
        */
        return view('backEnd.humanResource.deleteStaffTimelineView', ['id' => $id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteStaffTimeline($id)
    {

        /*
        try {
        */
        $result = SmStudentTimeline::destroy($id);
        if ($result) {
            Toastr::success('Operation successful', 'Success');
            return redirect()->back()->with(['staffTimeline' => 'active']);
        }
        Toastr::error('Operation Failed', 'Failed');
        return redirect()->back()->with(['staffTimeline' => 'active']);

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back()->with(['staffTimeline' => 'active']);
        }
        */
    }

    public function deleteStaffView($id)
    {

        /*
        try {
        */
        return view('backEnd.humanResource.deleteStaffView', ['id' => $id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteStaff($id)
    {

        /*
        try {
        */
        $tables = \App\tableList::getTableList('staff_id', $id);
        $tables1 = \App\tableList::getTableList('driver_id', $id);

        if ($tables == null) {
            if (checkAdmin() == true) {
                $staffs = SmStaff::withOutGlobalScope(ActiveStatusSchoolScope::class)->find($id);
            } else {
                $staffs = SmStaff::withOutGlobalScope(ActiveStatusSchoolScope::class)->where('id', $id)->where('school_id', Auth::user()->school_id)->first();
            }

            $user_id = $staffs->user_id;
            $result = $staffs->delete();
            User::destroy($user_id);
            Toastr::success('Operation successful', 'Success');

            return redirect('staff-directory');
        }

        $msg = 'This data already used in  : '.$tables.$tables1.' Please remove those data first';
        Toastr::error($msg, 'Failed');

        return redirect()->back();

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function delete_staff(Request $request)
    {
        /*
        try {
        */
        $id = $request->id;
        $expertStaff = SmExpertTeacher::where('staff_id', $id)->where('school_id', auth()->user()->school_id)->first();
        if ($expertStaff != null) {
            $expertStaff->delete();
        }

        $tables = \App\tableList::getTableList('staff_id', $id);
        $tables1 = \App\tableList::getTableList('driver_id', $id);

        if ($tables == null) {
            if (checkAdmin() == true) {
                $staffs = SmStaff::withOutGlobalScope(ActiveStatusSchoolScope::class)->find($id);
            } else {
                $staffs = SmStaff::withOutGlobalScope(ActiveStatusSchoolScope::class)->where('id', $id)->where('school_id', Auth::user()->school_id)->first();
            }

            $user_id = $staffs->user_id;
            $result = $staffs->delete();
            User::destroy($user_id);
            Toastr::success('Operation successful', 'Success');

            return redirect('staff-directory');
        }

        $msg = 'This data already used in  : '.$tables.$tables1.' Please remove those data first';
        Toastr::error($msg, 'Failed');

        return redirect()->back();

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function staffDisableEnable(Request $request)
    {
        /*
        try {
        */
        $status = $request->status == 'on' ? 1 : 0;
        $canUpdate = true;
        // for saas subscriptions
        if ($status == 1 && isSubscriptionEnabled() && auth()->user()->school_id != 1) {
            $active_staff = SmStaff::withOutGlobalScope(ActiveStatusSchoolScope::class)->where('role_id', '!=', 1)->where('school_id', Auth::user()->school_id)->where('active_status', 1)->where('is_saas', 0)->count();
            if (\Modules\Saas\Entities\SmPackagePlan::staff_limit() <= $active_staff) {
                $canUpdate = false;

                return response()->json(['message' => 'Your staff limit has been crossed.', 'status' => false]);
            }
        }

        $staff = SmStaff::withOutGlobalScope(ActiveStatusSchoolScope::class)
            ->when(checkAdmin(), function ($q): void {
                $q->where('school_id', Auth::user()->school_id);
            })->where('id', $request->id)->first();

        $staff->active_status = $status;
        $staff->save();

        $user = User::find($staff->user_id);
        $user->active_status = $status;
        $user->save();

        return response()->json(['status' => true]);

        /*
        } catch (\Exception $e) {
            throw ValidationException::withMessages(['message' => 'Operation Failed']);
        }
        */
    }

    public function deleteStaffDoc(Request $request)
    {

        /*
        try {
        */
        $staff_detail = SmStaff::withOutGlobalScope(ActiveStatusSchoolScope::class)->where('id', $request->staff_id)->first();

        if ($request->doc_id == 1) {
            if ($staff_detail->joining_letter != '') {
                unlink($staff_detail->joining_letter);
            }

            $staff_detail->joining_letter = null;
        } elseif ($request->doc_id == 2) {
            if ($staff_detail->resume != '') {
                unlink($staff_detail->resume);
            }

            $staff_detail->resume = null;
        } elseif ($request->doc_id == 3) {
            if ($staff_detail->other_document != '') {
                unlink($staff_detail->other_document);
            }

            $staff_detail->other_document = null;
        }

        $staff_detail->save();
        Toastr::success('Operation successful', 'Success');

        return redirect()->back()->with(['staffDocuments' => 'active']);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back()->with(['staffDocuments' => 'active']);
        }
        */
    }

    public function settings()
    {
        /*
        try {
        */
        $staff_settings = SmStaffRegistrationField::where('school_id', auth()->user()->school_id)->get()->filter(function ($field): bool {
            return $field->field_name != 'custom_fields' || isMenuAllowToShow('custom_field');
        });

        return view('backEnd.humanResource.staff_settings', ['staff_settings' => $staff_settings]);
        /*
        } catch (Throwable $throwable) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function statusUpdate(Request $request)
    {
        $field = SmStaffRegistrationField::where('school_id', auth()->user()->school_id)
            ->where('id', $request->filed_id)->firstOrFail();

        if ($request->filed_value == 'phone_number') {
            $emailField = SmStaffRegistrationField::where('school_id', auth()->user()->school_id)
                ->where('field_name', 'email_address')->firstOrFail();

            if ($emailField->is_required == 0 && $request->field_status == 0) {
                $emailField->is_required = 1;
            }

            $emailField->save();
        } elseif ($request->filed_value == 'email_address') {
            $phoneNumberField = SmStaffRegistrationField::where('school_id', auth()->user()->school_id)->where('field_name', 'phone_number')
                ->firstOrFail();

            if ($phoneNumberField->is_required == 0 && $request->field_status == 0) {
                $phoneNumberField->is_required = 1;
            }

            $phoneNumberField->save();
        }

        if ($field) {
            if ($request->type == 'required') {

                $field->is_required = $request->field_status;
            }

            if ($request->type == 'staff') {
                $field->staff_edit = $request->field_status;
            }

            $field->save();

            return response()->json(['message' => 'Operation Success']);
        }

        return response()->json(['error' => 'Operation Failed']);

    }

    public function teacherFieldView(Request $request)
    {

        $field = $request->filed_value;
        $status = $request->field_status;
        $gs = SmGeneralSettings::where('school_id', Auth::user()->school_id)->first();
        if ($gs) {
            if ($field == 'email') {
                $gs->teacher_email_view = $status;
            } elseif ($field == 'phone') {
                $gs->teacher_phone_view = $status;
            }

            $gs->save();
            session()->forget('generalSetting');
            session()->put('generalSetting', $gs);

            return response()->json(['message' => 'Operation Success']);
        }

        return null;
    }

    private function assignChatGroup($user): void
    {
        $groups = \Modules\Chat\Entities\Group::where('school_id', auth()->user()->school_id)->get();
        foreach ($groups as $group) {
            createGroupUser($group, $user->id);
        }
    }

    private function removeChatGroup($user): void
    {
        $groups = \Modules\Chat\Entities\Group::where('school_id', auth()->user()->school_id)->get();
        foreach ($groups as $group) {
            removeGroupUser($group, $user->id);
        }
    }
}
