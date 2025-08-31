<?php

namespace App\Http\Controllers\Admin\Academics;

use Exception;
use App\SmSection; 
use App\YearCheck;
use App\ApiBaseMethod;
use App\SmClassSection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Modules\University\Entities\UnAcademicYear;
use App\Http\Requests\Admin\Academics\SectionRequest;

class SmSectionController extends Controller
{
    public function index(Request $request)
    {
        $sections = SmSection::query();
        $academic_id = getAcademicId();
        $user = auth()->user();
        if (moduleStatusCheck('University')) {
            $data = $sections->where('un_academic_id', $academic_id);
        } else {
            $data = $sections->where('academic_id', $academic_id);
        }

        $sections = $data->where('school_id', $user->school_id)
                         ->where('active_status',1)
                         ->select(['id','section_name'])
                         ->get();

        $unAcademics = null;
        if (moduleStatusCheck('University')) {
            $unAcademics = UnAcademicYear::where('school_id', $user->school_id)->get()
                ->pluck('name', 'id')
                ->prepend(__('university::un.select_academic'), ' *')
                ->toArray();
        }           
        return view('backEnd.academics.section', ['sections' => $sections, 'unAcademics' => $unAcademics]);
    }

    public function store(SectionRequest $sectionRequest)
    {
        $academic_year = academicYears();
        if ($academic_year == null) {
            Toastr::warning('Create academic year first', 'Warning');
            return redirect()->back();
        }
        $user = Auth::user();
        $smSection = new SmSection();
        $smSection->section_name = $sectionRequest->name;
        $smSection->created_at = YearCheck::getYear().'-'.date('m-d h:i:s');
        $smSection->school_id = $user->school_id;
        $smSection->created_at = auth()->user()->id;
        $smSection->academic_id = moduleStatusCheck('University') ? null : getAcademicId();
        if (moduleStatusCheck('University')) {
            $smSection->un_academic_id = getAcademicId();
        }

        $result = $smSection->save();
        if (ApiBaseMethod::checkUrl($sectionRequest->fullUrl())) {
            if ($result) {
                return ApiBaseMethod::sendResponse(null, 'Section has been created successfully');
            }
            return ApiBaseMethod::sendError('Something went wrong, please try again.');
        }

        Toastr::success('Operation successful', 'Success');

        return redirect()->back();
    }

    public function edit(Request $request, $id)
    {

        /*
        try {
        */
        $section = SmSection::where('id', $id)->where('school_id', auth()->user()->school_id)->first();
        if (is_null($section)) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

        $sections = SmSection::query();
        if (moduleStatusCheck('University')) {
            $data = $sections->where('un_academic_id', getAcademicId());
        } else {
            $data = $sections->whereNull('un_academic_id')->where('academic_id', getAcademicId());
        }

        $sections = $data->where('school_id', auth()->user()->school_id)->get();
        $unAcademics = null;
        if (moduleStatusCheck('University')) {
            $unAcademics = UnAcademicYear::where('school_id', auth()->user()->school_id)->get()
                ->pluck('name', 'id')
                ->prepend(__('university::un.select_academic'), ' *')
                ->toArray();
        }

        return view('backEnd.academics.section', ['section' => $section, 'sections' => $sections, 'unAcademics' => $unAcademics]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
        */
    }

    public function update(SectionRequest $sectionRequest)
    {
        /*
        try {
        */

        $section = SmSection::find($sectionRequest->id);
        $section->section_name = $sectionRequest->name;
        $result = $section->save();

        if (ApiBaseMethod::checkUrl($sectionRequest->fullUrl())) {
            if ($result) {
                return ApiBaseMethod::sendResponse(null, 'Section has been updated successfully');
            }

            return ApiBaseMethod::sendError('Something went wrong, please try again.');

        }

        Toastr::success('Operation successful', 'Success');

        return redirect('section');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
        */
    }

    public function delete(Request $request, $id)
    {
        /*
        try {
        */
        $tables = SmClassSection::where('section_id', $id)->first();
        if ($tables == null) {
            SmSection::destroy($request->id);
            Toastr::success('Operation successful', 'Success');

            return redirect('section');
        }

        $msg = 'This section already assigned with class.';
        Toastr::warning($msg, 'Warning');

        return redirect()->back();

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
