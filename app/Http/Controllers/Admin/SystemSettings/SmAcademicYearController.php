<?php

namespace App\Http\Controllers\Admin\SystemSettings;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GeneralSettings\SmAcademicYearRequest;
use App\Scopes\AcademicSchoolScope;
use App\Scopes\ActiveStatusSchoolScope;
use App\Scopes\StatusAcademicSchoolScope;
use App\SmAcademicYear;
use App\SmClass;
use App\SmClassSection;
use App\SmGeneralSettings;
use App\SmSection;
use App\tableList;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SmAcademicYearController extends Controller
{
    

    public function index(Request $request)
    {
        /*
        try {
        */
            $academic_years = SmAcademicYear::where('active_status', 1)->orderBy('year', 'ASC')->where('school_id', Auth::user()->school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($academic_years, null);
            }

            return view('backEnd.systemSettings.academic_year', ['academic_years' => $academic_years]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmAcademicYearRequest $smAcademicYearRequest)
    {
        $yr = SmAcademicYear::orderBy('id', 'desc')->where('school_id', Auth::user()->school_id)->first();
        $created_year = $smAcademicYearRequest->starting_date;

        DB::beginTransaction();
        $smAcademicYear = new SmAcademicYear();
        $smAcademicYear->year = $smAcademicYearRequest->year;
        $smAcademicYear->title = $smAcademicYearRequest->title;
        $smAcademicYear->starting_date = date('Y-m-d', strtotime($smAcademicYearRequest->starting_date));
        $smAcademicYear->ending_date = date('Y-m-d', strtotime($smAcademicYearRequest->ending_date));
        if ($smAcademicYearRequest->copy_with_academic_year !== null) {
            $smAcademicYear->copy_with_academic_year = implode(',', $smAcademicYearRequest->copy_with_academic_year);
        }

        $smAcademicYear->created_at = $created_year;
        $smAcademicYear->school_id = Auth::user()->school_id;

        $result = $smAcademicYear->save();
        if ($result) {
            session()->forget('academic_years');
            $academic_years = SmAcademicYear::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            session()->put('academic_years', $academic_years);
        }

        $sm_Gs = SmGeneralSettings::where('school_id', Auth::user()->school_id)->first();
        $sm_Gs->session_id = $smAcademicYear->id;
        $sm_Gs->academic_id = $smAcademicYear->id;
        $sm_Gs->session_year = $smAcademicYear->year;
        $sm_Gs->save();

        session()->forget('sessionId');
        session()->put('sessionId', $sm_Gs->session_id);
        session()->forget('generalSetting');

        $generalSetting = SmGeneralSettings::where('school_id', Auth::user()->school_id)->first();
        session()->put('generalSetting', $generalSetting);

        $data = \App\SmMarksGrade::where('academic_id', $yr->id)->where('school_id', Auth::user()->school_id)->get();

        if (! empty($data)) {
            foreach ($data as $value) {
                $newClient = $value->replicate();
                $newClient->created_at = $created_year;
                $newClient->updated_at = $created_year;
                $newClient->academic_id = $smAcademicYear->id;
                $newClient->save();
            }
        }

        if ($smAcademicYearRequest->copy_with_academic_year !== null) {
            $tables = $smAcademicYearRequest->copy_with_academic_year;
            $tables = array_filter($tables);
            if ($tables !== []) {
                if ($yr) {
                    foreach ($tables as $table) {
                        $data = $table::where('academic_id', $yr->id)->where('school_id', Auth::user()->school_id)->withoutGlobalScopes([
                            StatusAcademicSchoolScope::class,
                            AcademicSchoolScope::class,
                            ActiveStatusSchoolScope::class,
                        ])->get();

                        if (! empty($data)) {
                            foreach ($data as $value) {
                                $newClient = $value->replicate();
                                $newClient->created_at = $created_year;
                                $newClient->updated_at = $created_year;
                                $newClient->academic_id = $smAcademicYear->id;
                                $newClient->save();
                            }
                        }
                    }
                }

                $classes = SmClass::where('academic_id', $smAcademicYear->id)->where('school_id', Auth::user()->school_id)->withoutGlobalScope(StatusAcademicSchoolScope::class)->get();
                $sections = SmSection::where('academic_id', $smAcademicYear->id)->where('school_id', Auth::user()->school_id)->withoutGlobalScope(StatusAcademicSchoolScope::class)->get();
                foreach ($classes as $class) {
                    foreach ($sections as $section) {
                        $class_section = new SmClassSection();
                        $class_section->class_id = $class->id;
                        $class_section->section_id = $section->id;
                        $class_section->created_at = $created_year;
                        $class_section->school_id = Auth::user()->school_id;
                        $class_section->academic_id = $smAcademicYear->id;
                        $class_section->save();
                    }
                }
            }
        }

        DB::commit();
        Toastr::success('Operation successful', 'Success');

        return redirect()->back();
    }

    public function show(Request $request, $id)
    {
        /*
        try {
        */    if (checkAdmin() == true) {
                $academic_year = SmAcademicYear::find($id);
            } else {
                $academic_year = SmAcademicYear::where('id', $id)->where('school_id', Auth::user()->school_id)->first();
            }

            $academic_years = SmAcademicYear::where('school_id', Auth::user()->school_id)->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['academic_year'] = $academic_year->toArray();
                $data['academic_years'] = $academic_years->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.systemSettings.academic_year', ['academic_year' => $academic_year, 'academic_years' => $academic_years]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function edit($id)
    {
        Toastr::error('Operation Failed', 'Failed');

        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'year' => 'required|numeric|digits:4',
            'title' => 'required|max:150',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        /*
        try {
        */
            $yr = SmAcademicYear::where('id', getAcademicId())->where('school_id', Auth::user()->school_id)->first();
            // dd($request->year);
            $created_year = $request->starting_date;
            // if ($yr->year == $request->year) {
            //     Toastr::warning('You cannot copy current academic year info.', 'Warning');

            //     return redirect('academic-year');
            // }

            if (checkAdmin() == true) {
                $academic_year = SmAcademicYear::find($request->id);
            } else {
                $academic_year = SmAcademicYear::where('id', $request->id)->where('school_id', Auth::user()->school_id)->first();
            }

            $academic_year->year = $request->year;
            $academic_year->title = $request->title;
            $academic_year->starting_date = date('Y-m-d', strtotime($request->starting_date));
            $academic_year->ending_date = date('Y-m-d', strtotime($request->ending_date));
            $academic_year->created_at = $created_year;
            if ($yr->year != $request->year && $request->copy_with_academic_year != null) {
                $academic_year->copy_with_academic_year = implode(',', $request->copy_with_academic_year);
            }

            $result = $academic_year->save();
            if ($result) {
                session()->forget('academic_years');
                $academic_years = SmAcademicYear::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
                session()->put('academic_years', $academic_years);
            }

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Year has been updated successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again');

            }

            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect('academic-year');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
/*
        } catch (Exception $exception) {
                } else {
                    return ApiBaseMethod::sendError('Something went wrong, please try again');
                }
            } else {
                if ($result) {
                    Toastr::success('Operation successful', 'Success');
                    return redirect('academic-year');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                }
            }
        /*
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function destroy(Request $request, $id)
    {
        /*
        try {
        */
            // $session_id = 'academic_id';
            // $tables = tableList::getTableList($session_id, $id);
            /*
            try {
            */

                if (getAcademicId() !== $id) {
                    if (checkAdmin() == true) {
                        $delete_query = SmAcademicYear::find($id);
                    } else {
                        $delete_query = SmAcademicYear::where('id', $id)->where('school_id', Auth::user()->school_id)->first();
                    }

                    $del_tables = explode(',', @$delete_query->copy_with_academic_year);

                    if (! is_null($del_tables)) {
                        foreach ($del_tables as $del_table) {
                            if ($del_table !== '' && $del_table !== '0') {
                                $del_data = new $del_table();
                                $del_data = $del_data->where('academic_id', $id)->delete();
                            }
                        }
                    }

                    SmClassSection::where('academic_id', $request->id)->where('school_id', Auth::user()->school_id)->delete();

                    $delete_query->delete();

                    if ($delete_query) {
                        session()->forget('academic_years');
                        $academic_years = SmAcademicYear::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
                        session()->put('academic_years', $academic_years);

                        Toastr::success('Operation successful', 'Success');

                        return redirect()->back();
                    }

                    Toastr::error('Operation Failed', 'Failed');

                    return redirect()->back();

                }

                Toastr::warning('You cannot delete current academic year.', 'Warning');

                return redirect()->back();
/*
            } catch (\Illuminate\Database\QueryException $e) {
                Toastr::error('This item already used', 'Failed');

                return redirect()->back();
            }
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
