<?php

namespace App\Http\Controllers\Admin\Academics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Academics\ClassRequest;
use App\Scopes\GlobalAcademicScope;
use App\Scopes\StatusAcademicSchoolScope;
use App\SmClass;
use App\SmClassSection;
use App\SmSection;
use App\tableList;
use App\YearCheck;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GlobalClassController extends Controller
{

    public function index(Request $request)
    {
        $sections = SmSection::withoutGlobalScope(GlobalAcademicScope::class)->where('school_id',auth()->user()->school_id)->whereNULL('parent_id')->get();
        $classes = SmClass::withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope( StatusAcademicSchoolScope::class)->where('school_id', Auth::user()->school_id)->with('groupclassSections')->whereNULL('parent_id')->get();
        return view('backEnd.global.global_class', compact('classes', 'sections'));
        /*
        try {
            $sections = SmSection::withoutGlobalScope(GlobalAcademicScope::class)->where('school_id', auth()->user()->school_id)->whereNULL('parent_id')->get();
            $classes = SmClass::withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class)->where('school_id', Auth::user()->school_id)->with('groupclassSections')->whereNULL('parent_id')->get();

            return view('backEnd.global.global_class', ['classes' => $classes, 'sections' => $sections]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(ClassRequest $classRequest)
    {
        /* $isCommitted = false;
        DB::transaction(function()use($request, &$isCommitted){
            $class = new SmClass();
            $class->class_name = $request->name;
            $class->parent_id = null;
            $class->pass_mark = $request->pass_mark;
            $class->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
            $class->created_by=auth()->user()->id;
            $class->school_id = Auth::user()->school_id;
            $class->academic_id = getAcademicId();
            $class->save();
            $class->toArray();

            foreach ($request->section as $section) {
                $smClassSection = new SmClassSection();
                $smClassSection->class_id = $class->id;
                $smClassSection->section_id = $section;
                $smClassSection->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                $smClassSection->school_id = Auth::user()->school_id;
                $smClassSection->academic_id = getAcademicId();
                $smClassSection->save();
            }
            $isCommitted = true;
        });

        if($isCommitted){
            Toastr::success('Operation successful', 'Success');
        }else{
            Toastr::error('Operation Failed', 'Failed');
        }
        return redirect()->back(); */

        DB::beginTransaction();
        try {
            $smClass = new SmClass();
            $smClass->class_name = $classRequest->name;
            $smClass->parent_id = null;
            $smClass->pass_mark = $classRequest->pass_mark;
            $smClass->created_at = YearCheck::getYear().'-'.date('m-d h:i:s');
            $smClass->created_by = auth()->user()->id;
            $smClass->school_id = Auth::user()->school_id;
            $smClass->academic_id = getAcademicId();
            $smClass->save();
            $smClass->toArray();

            foreach ($classRequest->section as $section) {
                $smClassSection = new SmClassSection();
                $smClassSection->class_id = $smClass->id;
                $smClassSection->section_id = $section;
                $smClassSection->created_at = YearCheck::getYear().'-'.date('m-d h:i:s');
                $smClassSection->school_id = Auth::user()->school_id;
                $smClassSection->academic_id = getAcademicId();
                $smClassSection->save();
            }

            DB::commit();
            Toastr::success('Operation successful', 'Success');

            return redirect()->back();

        } catch (Exception $exception) {
            DB::rollBack();
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

    }

    public function edit(Request $request, $id)
    {
        $classById = SmCLass::withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class)->where('id', $id)->firstOrFail();
        $sectionByNames = SmClassSection::withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class)->select('section_id')->where('class_id', '=', $classById->id)->get();
        $sectionId = array();
        foreach ($sectionByNames as $sectionByName) {
            $sectionId[] = $sectionByName->section_id;
        }
        
        $sections = SmSection::withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class)->where('active_status', '=', 1)->where('school_id', Auth::user()->school_id)->get();
        $classes = SmClass::withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class)->with('groupclassSections')->where('school_id', Auth::user()->school_id)->withCount('records')->get();
        return view('backEnd.global.global_class', compact('classById', 'classes', 'sections', 'sectionId'));
        /*
        try {
            $classById = SmClass::withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class)->where('id', $id)->firstOrFail();
            $sectionByNames = SmClassSection::withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class)->select('section_id')->where('class_id', '=', $classById->id)->get();
            $sectionId = [];
            foreach ($sectionByNames as $section) {
                $sectionId[] = $section->section_id;
            }

            $sections = SmSection::withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class)->where('active_status', '=', 1)->where('school_id', Auth::user()->school_id)->get();
            $classes = SmClass::withoutGlobalScope(GlobalAcademicScope::class)->withoutGlobalScope(StatusAcademicSchoolScope::class)->with('groupclassSections')->where('school_id', Auth::user()->school_id)->withCount('records')->get();

            return view('backEnd.global.global_class', ['classById' => $classById, 'classes' => $classes, 'sections' => $sections, 'sectionId' => $sectionId]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(ClassRequest $classRequest)
    {

        SmClassSection::withoutGlobalScope(GlobalAcademicScope::class, StatusAcademicSchoolScope::class)->where('class_id', $classRequest->id)->delete();
        DB::beginTransaction();

        try {
            $class = SmClass::withoutGlobalScope(GlobalAcademicScope::class, StatusAcademicSchoolScope::class)->where('id', $classRequest->id)->firstOrFail();
            $class->class_name = $classRequest->name;
            $class->pass_mark = $classRequest->pass_mark;
            $class->save();
            $class->toArray();
            try {
                foreach ($classRequest->section as $section) {
                    $smClassSection = new SmClassSection();
                    $smClassSection->class_id = $class->id;
                    $smClassSection->section_id = $section;
                    $smClassSection->created_at = YearCheck::getYear().'-'.date('m-d h:i:s');
                    $smClassSection->school_id = Auth::user()->school_id;
                    $smClassSection->academic_id = getAcademicId();
                    $smClassSection->save();
                }

                DB::commit();
                Toastr::success('Operation successful', 'Success');

                return redirect('global-class');
            } catch (Exception $e) {
                DB::rollBack();
            }
        } catch (Exception $exception) {
            DB::rollBack();
        }

        Toastr::error('Operation Failed', 'Failed');

        return redirect()->back();
    }

    public function delete(Request $request, $id)
    {
        try {
            $tables = tableList::getTableList('class_id', $id);
            if ($tables == null || $tables == 'Class sections, ') {

                DB::beginTransaction();

                // $class_sections = SmClassSection::where('class_id', $id)->get();
                $class_sections = SmClassSection::withoutGlobalScope(GlobalAcademicScope::class, StatusAcademicSchoolScope::class)->where('class_id', $id)->get();
                foreach ($class_sections as $class_section) {
                    SmClassSection::destroy($class_section->id);
                }

                $section = SmClass::destroy($id);
                DB::commit();

                Toastr::success('Operation successful', 'Success');

                return redirect('class');
            }

            DB::rollback();
            $msg = 'This data already used in  : '.$tables.' Please remove those data first';
            Toastr::error($msg, 'Failed');

            return redirect()->back();

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }
}
