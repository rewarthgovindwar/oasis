<?php

namespace App\Http\Controllers\Admin\OnlineExam;

use Exception;
use App\SmClass;
use App\SmStaff;
use App\SmSection;
use App\tableList;
use App\SmQuestionBank;
use App\SmQuestionGroup;
use App\SmGeneralSettings;
use App\SmQuestionBankMuOption;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Scopes\StatusAcademicSchoolScope;
use App\Http\Requests\Admin\OnlineExam\SmQuestionBankRequest;

class SmQuestionBankController extends Controller
{


    public function index()
    {
        /*
        try {
        */
            $groups = SmQuestionGroup::get();

            if (moduleStatusCheck('University')) {
                $banks = SmQuestionBank::whereNull('class_id')
                                        ->whereNull('section_id')
                                        ->whereNotNull('un_faculty_id')
                                        ->withOutGlobalScope(StatusAcademicSchoolScope::class)
                                        ->with('class', 'section', 'questionMu', 'questionGroup')
                                        ->get();
            }else{
                $banks = SmQuestionBank::where([['class_id', '!=', null], ['section_id', '!=', null]])
                ->withOutGlobalScope(StatusAcademicSchoolScope::class)
                ->with('class', 'section', 'questionMu', 'questionGroup')
                ->get();
            }
            
            if (teacherAccess()) {
                $classes = SmStaff::where('user_id', Auth::user()->id)->firstOrFail()->classes;
            } else {
                $classes = SmClass::select('id', 'class_name')->get();
            }

            $sections = SmSection::select('id', 'section_name')->get();
            
            return view('backEnd.examination.question_bank', ['banks' => $banks, 'groups' => $groups, 'classes' => $classes, 'sections' => $sections]);
        /*
        } catch (Exception $exception) {
            toastrError();

            return redirect()->back();
        }
        */
    }

    public function universityQuestionBankStore($request)
    {       
     
        if ($request->question_type !== 'M' && $request->question_type !== 'MI') {
            foreach ($request->un_section_ids as $section) {
                $online_question = new SmQuestionBank();
                $online_question->type = $request->question_type;
                $online_question->q_group_id = $request->group;
                $online_question->un_section_id = $section;
                $online_question->un_session_id = $request->un_session_id;
                $online_question->un_faculty_id = $request->un_faculty_id;
                $online_question->un_department_id = $request->un_department_id;
                $online_question->un_semester_label_id = $request->un_semester_label_id;
                $online_question->shift_id = shiftEnable() ? $request->shift : '';

                $online_question->marks = $request->marks;
                $online_question->question = $request->question;
                $online_question->school_id = Auth::user()->school_id;
                $online_question->un_academic_id = getAcademicId();

                if ($request->question_type == 'F') {
                    $online_question->suitable_words = $request->suitable_words;
                } elseif ($request->question_type == 'T') {
                    $online_question->trueFalse = $request->trueOrFalse;
                }

                $result = $online_question->save();
            }

            if ($result) {
                toastrSuccess();

                return redirect()->back();
            }

            toastrError();

            return redirect()->back();
        }
        
        if ($request->question_type == 'MI') {
            DB::beginTransaction();
            if (! Schema::hasColumn('sm_question_banks', 'question_image')) {
                Schema::table('sm_question_banks', function ($table): void {
                    $table->string('question_image')->nullable();
                });
            }

            if (! Schema::hasColumn('sm_question_banks', 'answer_type')) {
                Schema::table('sm_question_banks', function ($table): void {
                    $table->string('answer_type')->nullable();
                });
            }

            try {

                $fileName = '';
                $imagemimes = [
                    'image/png',
                    'image/jpg',
                    'image/jpeg',
                ];

                $maxFileSize = SmGeneralSettings::first('file_size')->file_size;
                $file = $request->file('question_image');
                $fileSize = filesize($file);
                $fileSizeKb = ($fileSize / 1000000);
                if ($fileSizeKb >= $maxFileSize) {
                    toastrError('Max upload file size '.$maxFileSize.' Mb is set in system');

                    return redirect()->back();
                }

                if (($request->file('question_image') !== '') && (in_array($file->getMimeType(), $imagemimes))) {
                    $image_info = getimagesize($request->file('question_image'));
                    if ($image_info[0] <= 650 && $image_info[1] <= 450) {
                        $fileName = md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();

                        $file->move('public/uploads/upload_contents/', $fileName);
                        $fileName = 'public/uploads/upload_contents/'.$fileName;
                    } else {
                        toastrError('Question Image should be 650x450');

                        return redirect()->to(url()->previous())->withInput($request->input());
                    }
                }

                foreach ($request->section as $section) {
                    $online_question = new SmQuestionBank();
                    $online_question->type = $request->question_type;
                    $online_question->q_group_id = $request->group;
                    $online_question->un_section_id = $section;
                    $online_question->un_session_id = $request->un_session_id;
                    $online_question->un_faculty_id = $request->un_faculty_id;
                    $online_question->un_department_id = $request->un_department_id;
                    $online_question->un_semester_label_id = $request->un_semester_label_id;
                    $online_question->shift_id = shiftEnable() ? $request->shift : '';
                    $online_question->marks = $request->marks;
                    $online_question->question = $request->question;
                    $online_question->answer_type = $request->answer_type;
                    $online_question->question_image = $fileName;

                    if ($request->question_type == 'MI') {
                        $online_question->number_of_option = $request->number_of_optionImg;
                    } else {
                        $online_question->number_of_option = $request->number_of_option;
                    }

                    $online_question->school_id = Auth::user()->school_id;
                    $online_question->un_academic_id = getAcademicId();
                    $online_question->save();
                    $online_question->toArray();
                }

                $i = 0;
                if (isset($request->images)) {
                    foreach ($request->images as $key => $image) {
                        $i++;
                        $option_check = 'option_check_'.$i;
                        $online_question_option = new SmQuestionBankMuOption();
                        $online_question_option->question_bank_id = $online_question->id;

                        $file = $request->file('images');
                        $fileName = '';
                        if (($file[$key] !== '') && (in_array($file[$key]->getMimeType(), $imagemimes))) {
                            $fileName = md5($file[$key]->getClientOriginalName().time()).'.'.$file[$key]->getClientOriginalExtension();
                            $file[$key]->move('public/uploads/upload_contents/', $fileName);
                            $fileName = 'public/uploads/upload_contents/'.$fileName;
                        }

                        $online_question_option->title = $fileName;

                        $online_question_option->school_id = Auth::user()->school_id;
                        $online_question_option->un_academic_id = getAcademicId();

                        $online_question_option->status = isset($request->$option_check) ? 1 : 0;
                        $online_question_option->save();
                    }
                }

                DB::commit();
                toastrSuccess();

                return redirect()->back();
            } catch (Exception $e) {
                DB::rollBack();
            }

            toastrError();

            return redirect()->back();
        }

        DB::beginTransaction();        
        foreach ($request->section as $section) {

            $online_question = new SmQuestionBank();
            $online_question->type = $request->question_type;
            $online_question->q_group_id = $request->group;
            $online_question->un_semester_label_id = $request->un_semester_label_id;
            $online_question->un_section_id = $section;
            $online_question->shift_id = shiftEnable() ? $request->shift : '';
            $online_question->un_session_id = $request->un_session_id;
            $online_question->un_faculty_id = $request->un_faculty_id;
            $online_question->un_department_id = $request->un_department_id;
            $online_question->marks = $request->marks;
            $online_question->question = $request->question;
            $online_question->number_of_option = $request->number_of_option;
            $online_question->school_id = Auth::user()->school_id;
            $online_question->un_academic_id = getAcademicId();
            $online_question->save();
            $online_question->toArray();

            $i = 0;
            if (isset($request->option)) {
                $sel = 0;
                foreach ($request->option as $option) {
                    $i++;
                    $option_check = 'option_check_'.$i;
                    if ($request->$option_check) {
                        $sel = $request->$option_check;
                        break;
                    }
                }

                if ($sel == 0) {
                    toastrWarning('Please select correct answer');

                    return redirect()->back();
                }

                foreach ($request->option as $option) {
                    $i++;
                    $option_check = 'option_check_'.$i;
                    $online_question_option = new SmQuestionBankMuOption();
                    $online_question_option->question_bank_id = $online_question->id;
                    $online_question_option->title = $option;
                    $online_question_option->school_id = Auth::user()->school_id;
                    $online_question_option->un_academic_id = getAcademicId();
                    $online_question_option->status = isset($request->$option_check) ? 1 : 0;
                    $online_question_option->save();
                }
            }
        }
        DB::commit();
        toastrSuccess();
        return redirect()->back();

        toastrError();
        return redirect()->back();
      
   }

    public function store(SmQuestionBankRequest $smQuestionBankRequest)
    {
        if (moduleStatusCheck('University')) {
            return $this->universityQuestionBankStore($smQuestionBankRequest);
        }

        /*
            try {
            */
            if ($smQuestionBankRequest->question_type !== 'M' && $smQuestionBankRequest->question_type !== 'MI') {
                foreach ($smQuestionBankRequest->section as $section) {
                    $online_question = new SmQuestionBank();
                    $online_question->type = $smQuestionBankRequest->question_type;
                    $online_question->q_group_id = $smQuestionBankRequest->group;
                    $online_question->class_id = $smQuestionBankRequest->class;
                    $online_question->shift_id = shiftEnable() ? $smQuestionBankRequest->shift : '';
                    $online_question->section_id = $section;
                    $online_question->marks = $smQuestionBankRequest->marks;
                    $online_question->question = $smQuestionBankRequest->question;
                    $online_question->school_id = Auth::user()->school_id;
                    $online_question->academic_id = getAcademicId();
                    if ($smQuestionBankRequest->question_type == 'F') {
                        $online_question->suitable_words = $smQuestionBankRequest->suitable_words;
                    } elseif ($smQuestionBankRequest->question_type == 'T') {
                        $online_question->trueFalse = $smQuestionBankRequest->trueOrFalse;
                    }

                    $result = $online_question->save();
                }

                if ($result) {
                    toastrSuccess();

                    return redirect()->back();
                }

                toastrError();

                return redirect()->back();
            }

            if ($smQuestionBankRequest->question_type == 'MI') {
                DB::beginTransaction();
                if (! Schema::hasColumn('sm_question_banks', 'question_image')) {
                    Schema::table('sm_question_banks', function ($table): void {
                        $table->string('question_image')->nullable();
                    });
                }

                if (! Schema::hasColumn('sm_question_banks', 'answer_type')) {
                    Schema::table('sm_question_banks', function ($table): void {
                        $table->string('answer_type')->nullable();
                    });
                }

                try {

                    $fileName = '';
                    $imagemimes = [
                        'image/png',
                        'image/jpg',
                        'image/jpeg',
                    ];

                    $maxFileSize = SmGeneralSettings::first('file_size')->file_size;
                    $file = $smQuestionBankRequest->file('question_image');
                    $fileSize = filesize($file);
                    $fileSizeKb = ($fileSize / 1000000);
                    if ($fileSizeKb >= $maxFileSize) {
                        toastrError('Max upload file size '.$maxFileSize.' Mb is set in system');

                        return redirect()->back();
                    }

                    if (($smQuestionBankRequest->file('question_image') !== '') && (in_array($file->getMimeType(), $imagemimes))) {
                        $image_info = getimagesize($smQuestionBankRequest->file('question_image'));
                        if ($image_info[0] <= 650 && $image_info[1] <= 450) {
                            $fileName = md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();
                            $file->move('public/uploads/upload_contents/', $fileName);
                            $fileName = 'public/uploads/upload_contents/'.$fileName;
                        } else {
                            toastrError('Question Image should be 650x450');

                            return redirect()->to(url()->previous())->withInput($smQuestionBankRequest->input());
                        }
                    }

                    foreach ($smQuestionBankRequest->section as $section) {
                        $online_question = new SmQuestionBank();
                        $online_question->type = $smQuestionBankRequest->question_type;
                        $online_question->q_group_id = $smQuestionBankRequest->group;
                        $online_question->class_id = $smQuestionBankRequest->class;
                        $online_question->section_id = $section;
                        $online_question->shift_id = shiftEnable() ? $smQuestionBankRequest->shift : '';
                        $online_question->marks = $smQuestionBankRequest->marks;
                        $online_question->question = $smQuestionBankRequest->question;
                        $online_question->answer_type = $smQuestionBankRequest->answer_type;
                        $online_question->question_image = $fileName;
                        if ($smQuestionBankRequest->question_type == 'MI') {
                            $online_question->number_of_option = $smQuestionBankRequest->number_of_optionImg;
                        } else {

                            $online_question->number_of_option = $smQuestionBankRequest->number_of_option;
                        }

                        $online_question->school_id = Auth::user()->school_id;
                        $online_question->academic_id = getAcademicId();
                        $online_question->save();
                        $online_question->toArray();
                    }

                    $i = 0;
                    if (property_exists($smQuestionBankRequest, 'images') && $smQuestionBankRequest->images !== null) {
                        foreach ($smQuestionBankRequest->images as $key => $image) {
                            $i++;
                            $option_check = 'option_check_'.$i;
                            $online_question_option = new SmQuestionBankMuOption();
                            $online_question_option->question_bank_id = $online_question->id;

                            $file = $smQuestionBankRequest->file('images');
                            $fileName = '';
                            if (($file[$key] !== '') && (in_array($file[$key]->getMimeType(), $imagemimes))) {
                                $fileName = md5($file[$key]->getClientOriginalName().time()).'.'.$file[$key]->getClientOriginalExtension();
                                $file[$key]->move('public/uploads/upload_contents/', $fileName);
                                $fileName = 'public/uploads/upload_contents/'.$fileName;
                            }

                            $online_question_option->title = $fileName;

                            $online_question_option->school_id = Auth::user()->school_id;
                            $online_question_option->academic_id = getAcademicId();

                                if (isset($request->$option_check)) {
                                    $online_question_option->status     = 1;
                                } else {
                                    $online_question_option->status     = 0;
                                }
                                $online_question_option->save();
                            }
                        }
                        DB::commit();
                        toastrSuccess();
                        return redirect()->back();
                    } catch (\Exception $e) {
                        DB::rollBack();
                    }
                    toastrError();
                    return redirect()->back();
                } else {
                    DB::beginTransaction();
                
                    try {
                        foreach ($smQuestionBankRequest->section as $section) {
                            
                            $online_question                    = new SmQuestionBank();
                            $online_question->type              = $smQuestionBankRequest->question_type;
                            $online_question->q_group_id        = $smQuestionBankRequest->group;
                            $online_question->class_id          = $smQuestionBankRequest->class;
                            $online_question->section_id        = $section;
                            $online_question->shift_id          = shiftEnable() ? $smQuestionBankRequest->shift : '';
                            $online_question->marks             = $smQuestionBankRequest->marks;
                            $online_question->question          = $smQuestionBankRequest->question;
                            $online_question->number_of_option  = $smQuestionBankRequest->number_of_option;
                            $online_question->school_id         = Auth::user()->school_id;
                            $online_question->academic_id       = getAcademicId();
                            $online_question->save();
                            
                            if (!empty($smQuestionBankRequest->option)) {
                                
                                $i = 0;
                                foreach ($smQuestionBankRequest->option as $option) {
                                    $i++;
                                    $option_check                               = 'option_check_' . $i;
                                    $online_question_option                     = new SmQuestionBankMuOption();
                                    $online_question_option->question_bank_id   = $online_question->id;
                                    $online_question_option->title              = $option;
                                    $online_question_option->school_id          = Auth::user()->school_id;
                                    $online_question_option->academic_id        = getAcademicId();
                
                                    if (isset($smQuestionBankRequest->$option_check)) {
                                        $online_question_option->status         = 1;
                                    } else {
                                        $online_question_option->status         = 0;
                                    }
                                    $online_question_option->save();
                                }
                            }
                        }
                       
                        DB::commit();
                        toastrSuccess();
                        return redirect()->back();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        toastrError();
                        return redirect()->back();
                    }
                } 
            /*
            } catch (\Exception $e) {
                toastrError();
                return redirect()->back();
            }
            */


    }

    public function show($id)
    {
        /*
        try {
        */
            $groups = SmQuestionGroup::get();
            $banks = SmQuestionBank::whereNotNull('class_id')
                ->whereNotNull('section_id')
                ->get();

            $bank = $banks->find($id);

            if (teacherAccess()) {
                $classes = SmStaff::where('user_id', Auth::user()->id)->firstOrFail()->classes;
            } else {
                $classes = SmClass::select('id', 'class_name')->get();
            }

            $sections = SmSection::select('id', 'section_name')->get();

            $editData = $bank;

            return view('backEnd.examination.question_bank', ['groups' => $groups, 'banks' => $banks, 'bank' => $bank, 'classes' => $classes, 'sections' => $sections, 'editData' => $editData]);
        /*
        } catch (Exception $exception) {
            toastrError();

            return redirect()->back();
        }
        */
    }

    public function universityBankUpdate($request, $id)
    {
        /*
        try {
        */
            if ($request->question_type !== 'M' && $request->question_type !== 'MI') {
                $online_question = SmQuestionBank::find($id);
                $online_question->type = $request->question_type;
                $online_question->q_group_id = $request->group;
                $online_question->un_semester_label_id = $request->un_semester_label_id;
                $online_question->un_section_id = $request->un_section_id;
                $online_question->un_session_id = $request->un_session_id;
                $online_question->un_faculty_id = $request->un_faculty_id;
                $online_question->un_department_id = $request->un_department_id;
                $online_question->marks = $request->marks;
                $online_question->question = $request->question;
                if ($request->question_type == 'F') {
                    $online_question->suitable_words = $request->suitable_words;
                } elseif ($request->question_type == 'T') {
                    $online_question->trueFalse = $request->trueOrFalse;
                }

                $result = $online_question->save();
                if ($result) {
                    Toastr::success('Operation successful', 'Success');

                    return redirect('question-bank');
                }

                Toastr::error('Operation Failed', 'Failed');

                return redirect()->back();
            }

            if ($request->question_type == 'MI') {
                DB::beginTransaction();
                if (! Schema::hasColumn('sm_question_banks', 'question_image')) {
                    Schema::table('sm_question_banks', function ($table): void {
                        $table->string('question_image')->nullable();
                    });
                }

                try {

                    $online_question = SmQuestionBank::find($id);
                    $online_question->type = $request->question_type;
                    $online_question->q_group_id = $request->group;
                    $online_question->un_semester_label_id = $request->un_semester_label_id;
                    $online_question->un_section_id = $request->un_section_id;
                    $online_question->un_session_id = $request->un_session_id;
                    $online_question->un_faculty_id = $request->un_faculty_id;
                    $online_question->un_department_id = $request->un_department_id;
                    $online_question->marks = $request->marks;
                    $online_question->question = $request->question;
                    $online_question->answer_type = $request->answer_type;
                    if ($request->question_type == 'MI') {
                        $online_question->number_of_option = $request->number_of_optionImg;
                    } else {
                        $online_question->number_of_option = $request->number_of_option;
                    }

                    $fileName = $online_question->question_image;
                    $imagemimes = [
                        'image/png',
                        'image/jpg',
                        'image/jpeg',
                    ];

                    $maxFileSize = SmGeneralSettings::first('file_size')->file_size;
                    $file = $request->file('question_image');
                    $fileSize = filesize($file);
                    $fileSizeKb = ($fileSize / 1000000);
                    if ($fileSizeKb >= $maxFileSize) {
                        Toastr::error('Max upload file size '.$maxFileSize.' Mb is set in system', 'Failed');

                        return redirect()->back();
                    }

                    if (($request->file('question_image') !== '') && (in_array($file->getMimeType(), $imagemimes))) {
                        $image_info = getimagesize($request->file('question_image'));
                        if ($image_info[0] <= 650 && $image_info[1] <= 450) {
                            $fileName = md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();
                            $file->move('public/uploads/upload_contents/', $fileName);
                            $fileName = 'public/uploads/upload_contents/'.$fileName;
                        } else {
                            Toastr::error('Question Image should be 650x450', 'Failed');

                            return redirect()->to(url()->previous())
                                ->withInput($request->input());
                        }
                    }

                    $online_question->question_image = $fileName;

                    $online_question->number_of_option = $request->number_of_option;
                    $online_question->school_id = Auth::user()->school_id;
                    $online_question->un_academic_id = getAcademicId();
                    $online_question->save();
                    $online_question->toArray();
                    $i = 0;

                    if (isset($request->images_old)) {
                        SmQuestionBankMuOption::where('question_bank_id', $online_question->id)->delete();
                        foreach ($request->images_old as $key => $image) {
                            $i++;
                            $option_check = 'option_check_'.$i;
                            $online_question_option = new SmQuestionBankMuOption();
                            $online_question_option->question_bank_id = $online_question->id;

                            if (isset($request->images[$key])) {

                                $file = $request->file('images');
                                $fileName = '';
                                if (($file[$key] !== '') && (in_array($file[$key]->getMimeType(), $imagemimes))) {
                                    $fileName = md5($file[$key]->getClientOriginalName().time()).'.'.$file[$key]->getClientOriginalExtension();
                                    $file[$key]->move('public/uploads/upload_contents/', $fileName);
                                    $fileName = 'public/uploads/upload_contents/'.$fileName;
                                }
                            } else {
                                $fileName = $request->images_old[$key];
                            }

                            $online_question_option->title = $fileName;

                            $online_question_option->school_id = Auth::user()->school_id;
                            $online_question_option->academic_id = getAcademicId();
                            $online_question_option->status = isset($request->$option_check) ? 1 : 0;
                            $online_question_option->save();
                        }
                    }

                    DB::commit();
                    Toastr::success('Operation successful', 'Success');

                    return redirect('question-bank');
                } catch (Exception $e) {
                    DB::rollBack();
                }

                Toastr::error('Operation Failed', 'Failed');

                return redirect()->back();
            }

            DB::beginTransaction();
            try {
                $online_question = SmQuestionBank::find($id);
                $online_question->type = $request->question_type;
                $online_question->q_group_id = $request->group;
                $online_question->un_semester_label_id = $request->un_semester_label_id;
                $online_question->un_section_id = $request->un_section_id;
                $online_question->un_session_id = $request->un_session_id;
                $online_question->un_faculty_id = $request->un_faculty_id;
                $online_question->un_department_id = $request->un_department_id;
                $online_question->marks = $request->marks;
                $online_question->question = $request->question;
                $online_question->number_of_option = $request->number_of_option;
                $online_question->save();
                $online_question->toArray();
                $i = 0;
                if (isset($request->option)) {
                    SmQuestionBankMuOption::where('question_bank_id', $online_question->id)->delete();
                    foreach ($request->option as $option) {
                        $i++;
                        $option_check = 'option_check_'.$i;
                        $online_question_option = new SmQuestionBankMuOption();
                        $online_question_option->question_bank_id = $online_question->id;
                        $online_question_option->title = $option;
                        $online_question_option->school_id = Auth::user()->school_id;
                        $online_question_option->un_academic_id = getAcademicId();
                        $online_question_option->status = isset($request->$option_check) ? 1 : 0;
                        $online_question_option->save();
                    }
                }

                DB::commit();
                Toastr::success('Operation successful', 'Success');

                return redirect('question-bank');
            } catch (Exception $e) {
                DB::rollBack();
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmQuestionBankRequest $smQuestionBankRequest, $id)
    {
        if (moduleStatusCheck('University')) {
            return $this->universityBankUpdate($smQuestionBankRequest, $id);
        }

        /*
            try {
            */
            if ($smQuestionBankRequest->question_type != 'M' && $smQuestionBankRequest->question_type != 'MI') {
                $online_question = SmQuestionBank::find($id);
                $online_question->type = $smQuestionBankRequest->question_type;
                $online_question->q_group_id = $smQuestionBankRequest->group;
                $online_question->class_id = $smQuestionBankRequest->class;
                $online_question->shift_id = shiftEnable() ? $smQuestionBankRequest->shift : '';
                $online_question->section_id = $smQuestionBankRequest->section;
                $online_question->marks = $smQuestionBankRequest->marks;
                $online_question->question = $smQuestionBankRequest->question;
                if ($smQuestionBankRequest->question_type == 'F') {
                    $online_question->suitable_words = $smQuestionBankRequest->suitable_words;
                } elseif ($smQuestionBankRequest->question_type == 'T') {
                    $online_question->trueFalse = $smQuestionBankRequest->trueOrFalse;
                }

                $result = $online_question->save();
                if ($result) {
                    toastrSuccess();

                    return redirect('question-bank');
                }

                toastrError();

                return redirect()->back();
            }

            if ($smQuestionBankRequest->question_type == 'MI') {
                DB::beginTransaction();
                if (! Schema::hasColumn('sm_question_banks', 'question_image')) {
                    Schema::table('sm_question_banks', function ($table): void {
                        $table->string('question_image')->nullable();
                    });
                }

                try {
                    $online_question = SmQuestionBank::find($id);
                    $online_question->type = $smQuestionBankRequest->question_type;
                    $online_question->q_group_id = $smQuestionBankRequest->group;
                    $online_question->class_id = $smQuestionBankRequest->class;
                    $online_question->shift_id = shiftEnable() ? $smQuestionBankRequest->shift : '';
                    $online_question->section_id = $smQuestionBankRequest->section;
                    $online_question->marks = $smQuestionBankRequest->marks;
                    $online_question->question = $smQuestionBankRequest->question;
                    $online_question->answer_type = $smQuestionBankRequest->answer_type;

                    if ($smQuestionBankRequest->question_type == 'MI') {
                        $online_question->number_of_option = $smQuestionBankRequest->number_of_optionImg;
                    } else {
                        $online_question->number_of_option = $smQuestionBankRequest->number_of_option;
                    }

                    $fileName = $online_question->question_image;
                    $imagemimes = [
                        'image/png',
                        'image/jpg',
                        'image/jpeg',
                    ];

                    $maxFileSize = SmGeneralSettings::first('file_size')->file_size;
                    $file = $smQuestionBankRequest->file('question_image');
                    $fileSize = filesize($file);
                    $fileSizeKb = ($fileSize / 1000000);

                    if ($fileSizeKb >= $maxFileSize) {
                        toastrError('Max upload file size '.$maxFileSize.' Mb is set in system');

                        return redirect()->back();
                    }

                    if (($smQuestionBankRequest->file('question_image') !== '') && (in_array($file->getMimeType(), $imagemimes))) {
                        $image_info = getimagesize($smQuestionBankRequest->file('question_image'));

                        if ($image_info[0] <= 650 && $image_info[1] <= 450) {
                            $fileName = md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();
                            $file->move('public/uploads/upload_contents/', $fileName);
                            $fileName = 'public/uploads/upload_contents/'.$fileName;
                        } else {
                            toastrError('Question Image should be 650x450');

                            return redirect()->to(url()->previous())->withInput($smQuestionBankRequest->input());
                        }
                    }

                    $online_question->question_image = $fileName;
                    $online_question->number_of_option = $smQuestionBankRequest->number_of_option;
                    $online_question->school_id = Auth::user()->school_id;
                    $online_question->academic_id = getAcademicId();
                    $online_question->save();
                    $online_question->toArray();

                    $i = 0;
                    if (property_exists($smQuestionBankRequest, 'images_old') && $smQuestionBankRequest->images_old !== null) {
                        SmQuestionBankMuOption::where('question_bank_id', $online_question->id)->delete();
                        foreach ($smQuestionBankRequest->images_old as $key => $image) {
                            $i++;
                            $option_check = 'option_check_'.$i;
                            $online_question_option = new SmQuestionBankMuOption();
                            $online_question_option->question_bank_id = $online_question->id;

                            if (isset($smQuestionBankRequest->images[$key])) {
                                $file = $smQuestionBankRequest->file('images');
                                $fileName = '';

                                if (($file[$key] !== '') && (in_array($file[$key]->getMimeType(), $imagemimes))) {
                                    $fileName = md5($file[$key]->getClientOriginalName().time()).'.'.$file[$key]->getClientOriginalExtension();
                                    $file[$key]->move('public/uploads/upload_contents/', $fileName);
                                    $fileName = 'public/uploads/upload_contents/'.$fileName;
                                }
                            } else {
                                $fileName = $smQuestionBankRequest->images_old[$key];
                            }

                            $online_question_option->title = $fileName;
                            $online_question_option->school_id = Auth::user()->school_id;
                            $online_question_option->academic_id = getAcademicId();
                            $online_question_option->status = isset($smQuestionBankRequest->$option_check) ? 1 : 0;
                            $online_question_option->save();
                        }
                    }

                    DB::commit();
                    toastrSuccess();

                    return redirect('question-bank');
                } catch (Exception $e) {
                    DB::rollBack();
                }

                toastrError();

                return redirect()->back();
            }

            DB::beginTransaction();
            try {
               
                $online_question = SmQuestionBank::find($id);
                $online_question->type = $smQuestionBankRequest->question_type;
                $online_question->q_group_id = $smQuestionBankRequest->group;
                $online_question->class_id = $smQuestionBankRequest->class;
                $online_question->shift_id = shiftEnable() ? $smQuestionBankRequest->shift : '';
                $online_question->section_id = $smQuestionBankRequest->section;
                $online_question->marks = $smQuestionBankRequest->marks;
                $online_question->question = $smQuestionBankRequest->question;
                $online_question->number_of_option = $smQuestionBankRequest->number_of_option;
                $online_question->save();
                $online_question->toArray();
                
                
                $i = 0;
                if (!empty($smQuestionBankRequest->option)) {
                    SmQuestionBankMuOption::where('question_bank_id', $online_question->id)->delete();
                    foreach ($smQuestionBankRequest->option as $option) {
                        $i++;
                        $option_check = 'option_check_'.$i;
                        $online_question_option = new SmQuestionBankMuOption();
                        $online_question_option->question_bank_id = $online_question->id;
                        $online_question_option->title = $option;
                        $online_question_option->school_id = Auth::user()->school_id;
                        $online_question_option->academic_id = getAcademicId();
                        if (isset($request->$option_check)) {
                            $online_question_option->status         = 1;
                        } else {
                            $online_question_option->status         = 0;
                        }
                            $online_question_option->save();
                    }
                }
    
                DB::commit();
                toastrSuccess();
                return redirect('question-bank');
            } catch (Exception $e) {
                DB::rollBack();
                toastrError();
                return redirect()->back();
            }
              

            /*
            } catch (\Exception $e) {
                toastrError();
                return redirect()->back();
            }
            */


    }

    public function destroy($id)
    {
        $tables = tableList::getTableList('question_bank_id', $id);

        $online_question = SmQuestionBank::find($id);

        $tables = $online_question->type !== 'M' ? tableList::getTableList('question_bank_id', $id) : null;

        /*
        try {
        */
            if ($tables == null) {
                if ($online_question->type == 'M') {
                    SmQuestionBankMuOption::where('question_bank_id', $online_question->id)->delete();
                }

                $online_question->delete();
                toastrSuccess();

                return redirect('question-bank');
            }

            $msg = 'This data already used in  : '.$tables.' Please remove those data first';
            toastrError($msg, 'Failed');

            return redirect()->back();

        /*
        } catch (Exception $exception) {
            $msg = 'This data already used in  : '.$tables.' Please remove those data first';
            toastrError($msg, 'Failed');

            return redirect()->back();
        }
        */
    }
}
