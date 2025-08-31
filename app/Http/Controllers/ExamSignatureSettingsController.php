<?php

namespace App\Http\Controllers;

use App\Models\SmExamSignature;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExamSignatureSettingsController extends Controller
{
    public function index()
    {
        $allSignature = SmExamSignature::get();

        return view('backEnd.examination.examSignatureSettings', ['allSignature' => $allSignature]);
    }

    public function store(Request $request)
    {
        foreach (gv($request, 'exam_signature') as $signature) {
            $validator = Validator::make($signature, [
                'title' => 'required',
            ]);
            if ($validator->fails()) {
                Toastr::error('Empty Submission', 'Failed');

                return redirect()->back()->withErrors($validator)->withInput();
            }
        }


            foreach (gv($request, 'exam_signature') as $signature) {
                $this->formatData($signature);
            }

            Toastr::success('Operation Successfully', 'Success');

            return redirect()->route('exam-signature-settings');
    }

    public function update(Request $request)
    {
            $allDataDeletes = SmExamSignature::get();
            foreach ($allDataDeletes as $allDataDelete) {
                $allDataDelete->delete();
            }

            foreach (gv($request, 'exam_signature') as $signature) {
                $this->formatData($signature);
            }

            Toastr::success('Update Successfully', 'Success');

            return redirect()->route('exam-signature-settings');
    }

    private function formatData($request): void
    {
        $destination = 'public/uploads/upload_contents/';
        $smExamSignature = new SmExamSignature();
        $smExamSignature->title = gv($request, 'title');
        if (gv($request, 'image_path')) {
            if (gv($request, 'signature')) {
                if (file_exists(gv($request, 'image_path'))) {
                    unlink(gv($request, 'image_path'));
                }

                $smExamSignature->signature = fileUpload(gv($request, 'signature'), $destination);
            } else {
                $smExamSignature->signature = gv($request, 'image_path');
            }
        } else {
            $smExamSignature->signature = fileUpload(gv($request, 'signature'), $destination);
        }

        $smExamSignature->active_status = (gv($request, 'active_status') ? 1 : 0);
        $smExamSignature->school_id = auth()->user()->school_id;
        $smExamSignature->academic_id = getAcademicId();
        $smExamSignature->save();
    }
}
