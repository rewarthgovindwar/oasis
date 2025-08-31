<?php

namespace App\Http\Controllers\Admin\Academics;

use Exception;
use App\SmSection;
use App\YearCheck;
use App\SmClassSection;
use Illuminate\Http\Request;
use App\Scopes\GlobalAcademicScope;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Scopes\StatusAcademicSchoolScope;
use Modules\University\Entities\UnAcademicYear;
use App\Http\Requests\Admin\Academics\SectionRequest;

class GlobalSectionController extends Controller
{
    public function index(Request $request)
    {
        $sections = SmSection::query();
        $parentSection = true;
        $sections = $sections->withoutGlobalScope(StatusAcademicSchoolScope::class)->withoutGlobalScope(GlobalAcademicScope::class)->whereNull('parent_id')->where('school_id', auth()->user()->school_id)->get();
        $unAcademics = null;
        if (moduleStatusCheck('University')) {
            $unAcademics = UnAcademicYear::where('school_id', auth()->user()->school_id)->get()
                ->pluck('name', 'id')
                ->prepend(__('university::un.select_academic'), ' *')
                ->toArray();
        }

        return view('backEnd.global.global_section', compact('sections', 'unAcademics', 'parentSection'));

        /*
        try {
            $sections = SmSection::query();
            $parentSection = true;
            $sections = $sections->withoutGlobalScope(StatusAcademicSchoolScope::class)->withoutGlobalScope(GlobalAcademicScope::class)->whereNull('parent_id')->where('school_id', auth()->user()->school_id)->get();
            $unAcademics = null;
            if (moduleStatusCheck('University')) {
                $unAcademics = UnAcademicYear::where('school_id', auth()->user()->school_id)->get()
                    ->pluck('name', 'id')
                    ->prepend(__('university::un.select_academic'), ' *')
                    ->toArray();
            }

            return view('backEnd.global.global_section', ['sections' => $sections, 'unAcademics' => $unAcademics, 'parentSection' => $parentSection]);
        } catch (Exception $exception) {

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SectionRequest $request)
    {

        $academic_year = academicYears();
        if ($academic_year == null) {
            Toastr::warning('Create academic year first', 'Warning');

            return redirect()->back();
        }

        $section = new SmSection();
        $section->section_name = $request->name;
        $section->created_at = YearCheck::getYear().'-'.date('m-d h:i:s');
        $section->school_id = Auth::user()->school_id;
        $section->created_at = auth()->user()->id;
        $section->academic_id = ! moduleStatusCheck('University') ? getAcademicId() : null;
        if (moduleStatusCheck('University')) {
            $section->un_academic_id = getAcademicId();
        }
        $result = $section->save();

        Toastr::success('Operation successful', 'Success');

        return redirect()->back();

        /*
        try {
            $user  = Auth::user();
            $smSection = new SmSection();
            $smSection->section_name = $sectionRequest->name;
            $smSection->created_at = YearCheck::getYear().'-'.date('m-d h:i:s');
            $smSection->school_id = $user->school_id;
            $smSection->created_at = $user->id;
            $smSection->academic_id = moduleStatusCheck('University') ? null : getAcademicId();
            if (moduleStatusCheck('University')) {
                $smSection->un_academic_id = getAcademicId();
            }

            $result = $smSection->save();

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        } catch (Exception $exception) {

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */

    }

    public function edit(Request $request, $id)
    {
        $section = SmSection::withoutGlobalScope(StatusAcademicSchoolScope::class)->withoutGlobalScope(GlobalAcademicScope::class)->where('id', $id)->where('school_id', auth()->user()->school_id)->first();
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
        $sections = $data->withoutGlobalScope(StatusAcademicSchoolScope::class)->withoutGlobalScope(GlobalAcademicScope::class)->where('school_id', auth()->user()->school_id)->get();
        $unAcademics = null;
        if (moduleStatusCheck('University')) {
            $unAcademics = UnAcademicYear::where('school_id', auth()->user()->school_id)->get()
                ->pluck('name', 'id')
                ->prepend(__('university::un.select_academic'), ' *')
                ->toArray();
        }

        return view('backEnd.global.global_section', compact('section', 'sections', 'unAcademics'));

        /*
        try {
            $user  = Auth::user();
            $section = SmSection::withoutGlobalScope(StatusAcademicSchoolScope::class)->withoutGlobalScope(GlobalAcademicScope::class)->where('id', $id)->where('school_id', auth()->user()->school_id)->first();
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

            $sections = $data->withoutGlobalScope(StatusAcademicSchoolScope::class)->withoutGlobalScope(GlobalAcademicScope::class)->where('school_id', $user->school_id)->get();
            $unAcademics = null;
            if (moduleStatusCheck('University')) {
                $unAcademics = UnAcademicYear::where('school_id', $user->school_id)->get()
                    ->pluck('name', 'id')
                    ->prepend(__('university::un.select_academic'), ' *')
                    ->toArray();
            }

            return view('backEnd.global.global_section', ['section' => $section, 'sections' => $sections, 'unAcademics' => $unAcademics]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SectionRequest $request)
    {
        $section = SmSection::find($request->id);
        $section->section_name = $request->name;
        $result = $section->save();

        Toastr::success('Operation successful', 'Success');

        return redirect('section');

        /*
        try {
            $section = SmSection::find($sectionRequest->id);
            $section->section_name = $sectionRequest->name;
            $result = $section->save();

            Toastr::success('Operation successful', 'Success');

            return redirect('section');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function delete(Request $request, $id)
    {
        $tables = SmClassSection::where('section_id', $id)->first();
        if ($tables == null) {
            SmSection::destroy($request->id);
            Toastr::success('Operation successful', 'Success');

            return redirect('section');
        }
        $msg = 'This section already assigned with class .';
        Toastr::warning($msg, 'Warning');

        return redirect()->back();

        /*
        try {

            $tables = SmClassSection::where('section_id', $id)->first();
            if ($tables == null) {
                SmSection::destroy($request->id);
                Toastr::success('Operation successful', 'Success');

                return redirect('section');
            }

            $msg = 'This section already assigned with class .';
            Toastr::warning($msg, 'Warning');

            return redirect()->back();

        } catch (Exception $exception) {

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
