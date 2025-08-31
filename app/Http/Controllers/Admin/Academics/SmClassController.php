<?php

namespace App\Http\Controllers\Admin\Academics;

use Exception;
use App\SmClass;
use App\SmSection;
use App\tableList;
use App\YearCheck;
use App\ApiBaseMethod;
use App\SmClassSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\Academics\ClassRequest;

class SmClassController extends Controller
{
    public $date;

    public function index(Request $request)
    {
        /*
        try {
        */
            $sections = SmSection::query();
            if (moduleStatusCheck('University')) {
                $data = $sections->where('un_academic_id', getAcademicId());
            } else {
                $data = $sections->where('academic_id', getAcademicId());
            }

            $sections = $data->where('school_id', auth()->user()->school_id)->get();
            $classes = SmClass::with('groupclassSections')->withCount('records')->get();

            return view('backEnd.academics.class', ['classes' => $classes, 'sections' => $sections]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
        */
    }

    public function store(ClassRequest $request)
    {
       // DB::beginTransaction();
            try {
                $class = new SmClass();
                $class->class_name = $request->name;
                // $class->shift_id = $request->shift;
                $class->pass_mark = $request->pass_mark;
                $class->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                $class->created_by=auth()->user()->id;
                $class->school_id = Auth::user()->school_id;
                $class->academic_id = getAcademicId();
                $class->save();
                $class->toArray();
                if(shiftEnable()){
                    foreach($request->shift as $shift){
                        foreach ($request->section as $section) {
                            $smClassSection = new SmClassSection();
                            $smClassSection->class_id = $class->id;
                            $smClassSection->shift_id = $shift;
                            $smClassSection->section_id = $section;
                            $smClassSection->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                            $smClassSection->school_id = Auth::user()->school_id;
                            $smClassSection->academic_id = getAcademicId();
                            $smClassSection->save();
                        }
                    }
                }else{
                    foreach ($request->section as $section) {
                        $smClassSection = new SmClassSection();
                        $smClassSection->class_id = $class->id;
                        $smClassSection->section_id = $section;
                        $smClassSection->created_at = YearCheck::getYear().'-'.date('m-d h:i:s');
                        $smClassSection->school_id = Auth::user()->school_id;
                        $smClassSection->academic_id = getAcademicId();
                        $smClassSection->save();
                    }
                }
                   // DB::commit();
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
           
            } catch (\Exception $e) {
              
               // DB::rollBack();                
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }

    }

    public function edit(Request $request, $id)
    {
        try {
            $classById = SmCLass::find($id);
            $sectionByNames = SmClassSection::select('section_id','shift_id')->where('class_id', '=', $classById->id)->get();
            $sectionId = array();
            $shiftId = array();
            foreach ($sectionByNames as $sectionByName) {
                $sectionId[] = $sectionByName->section_id;
                if(shiftEnable()){
                    $shiftId[] = $sectionByName->shift_id;
                }
            }
            //shiftId make unique
            $shiftId = array_unique($shiftId);
            $sections = SmSection::where('active_status', '=', 1)->where('created_at', 'LIKE', '%' . $this->date . '%')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();

            $classes = SmClass::where('active_status', '=', 1)->orderBy('id', 'desc')->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->withCount('records')->get();
            return view('backEnd.academics.class', compact('classById', 'classes', 'sections', 'sectionId','shiftId'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function update(ClassRequest $classRequest)
    {
        SmClassSection::where('class_id', $classRequest->id)->delete();
        DB::beginTransaction();
        try {
            $class = SmClass::find($classRequest->id);
            $class->class_name = $classRequest->name;
            $class->pass_mark = $classRequest->pass_mark;
            $class->save();
            $class->toArray();
            
            try {
                if(shiftEnable()){
                    foreach($classRequest->shift as $shift){
                        foreach ($classRequest->section as $section) {
                            $smClassSection = new SmClassSection();
                            $smClassSection->class_id = $class->id;
                            $smClassSection->shift_id = $shift;
                            $smClassSection->section_id = $section;
                            $smClassSection->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                            $smClassSection->school_id = Auth::user()->school_id;
                            $smClassSection->academic_id = getAcademicId();
                            $smClassSection->save();
                        }
                    }
                }else{
                    foreach ($classRequest->section as $section) {
                        $smClassSection = new SmClassSection();
                        $smClassSection->class_id = $class->id;
                        $smClassSection->section_id = $section;
                        $smClassSection->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                        $smClassSection->school_id = Auth::user()->school_id;
                        $smClassSection->academic_id = getAcademicId();
                        $smClassSection->save();
                    }
                }
                DB::commit();
                if (ApiBaseMethod::checkUrl($classRequest->fullUrl())) {
                    return ApiBaseMethod::sendResponse(null, 'Class has been updated successfully');
                }
                Toastr::success('Operation successful', 'Success');
                return redirect('class');
            } catch (Exception $e) {
                DB::rollBack();
            }
        } catch (Exception $exception) {
            DB::rollBack();
        }
        if (ApiBaseMethod::checkUrl($classRequest->fullUrl())) {
            return ApiBaseMethod::sendError('Something went wrong, please try again.');
        }
        Toastr::error('Operation Failed', 'Failed');
        return redirect()->back();
    }

    public function delete(Request $request, $id)
    {
        try {
            $tables = tableList::getTableList('class_id', $id);
            if (empty($tables) || $tables == 'Chat groups, Class sections, ') {
                DB::beginTransaction();
                // $class_sections = SmClassSection::where('class_id', $id)->get();
                $class_sections = SmClassSection::where('class_id', $id)->get();
                foreach ($class_sections as $class_section) {
                    SmClassSection::destroy($class_section->id);
                    DB::table('chat_groups')->where('class_id',$class_section->class_id)->delete();
                }
                $section = SmClass::destroy($id);
                DB::commit();
                if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                    if ($section) {
                        return ApiBaseMethod::sendResponse(null, 'Class has been deleted successfully');
                    }
                    return ApiBaseMethod::sendError('Something went wrong, please try again.');
                }
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
