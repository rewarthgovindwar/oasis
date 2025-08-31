<?php

namespace App\Http\Controllers\api;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\SmMarksGrade;
use App\YearCheck;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Validator;

class ApiSmMarksGradeController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $marks_grades = SmMarksGrade::orderBy('gpa', 'desc')->where('academic_id', getAcademicId())->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($marks_grades, null);
            }

            return view('backEnd.examination.marks_grade', ['marks_grades' => $marks_grades]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): void
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'grade_name' => 'required|max:50',
            'gpa' => 'required|max:4',
            'percent_from' => 'required|integer||min:0',
            'percent_upto' => 'required|integer|min:'.@$request->percent_from,
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $smMarksGrade = new SmMarksGrade();
            $smMarksGrade->grade_name = $request->grade_name;
            $smMarksGrade->gpa = $request->gpa;
            $smMarksGrade->percent_from = $request->percent_from;
            $smMarksGrade->percent_upto = $request->percent_upto;
            $smMarksGrade->description = $request->description;
            $smMarksGrade->created_at = YearCheck::getYear().'-'.date('m-d h:i:s');

            $result = $smMarksGrade->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Grade has been created successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect()->back();
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try {
            $marks_grade = SmMarksGrade::find($id);
            $marks_grades = SmMarksGrade::where('academic_id', getAcademicId())->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['marks_grade'] = $marks_grade->toArray();
                $data['marks_grades'] = $marks_grades->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.examination.marks_grade', ['marks_grade' => $marks_grade, 'marks_grades' => $marks_grades]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     */
    public function edit($id): void
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'grade_name' => 'required|max:50',
            'gpa' => 'required|max:4',
            'percent_from' => 'required|integer||min:0',
            'percent_upto' => 'required|integer|min:'.@$request->percent_from,
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $marks_grade = SmMarksGrade::find($request->id);
            $marks_grade->grade_name = $request->grade_name;
            $marks_grade->gpa = $request->gpa;
            $marks_grade->percent_from = $request->percent_from;
            $marks_grade->percent_upto = $request->percent_upto;
            $marks_grade->description = $request->description;
            $result = $marks_grade->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Grade has been updated successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect('marks-grade');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {

        try {
            $marks_grade = SmMarksGrade::destroy($id);

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($marks_grade) {
                    return ApiBaseMethod::sendResponse(null, 'Grdae has been deleted successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            if ($marks_grade) {
                Toastr::success('Operation successful', 'Success');

                return redirect('marks-grade');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }
}
