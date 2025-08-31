<?php

namespace App\Http\Controllers\Admin\FrontSettings;

use Exception;
use App\Models\FrontResult;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Validator;

class SmFrontResultController extends Controller
{


    public function index()
    {
        /*
        try {
        */
            $frontResults = FrontResult::where('school_id', app('school')->id)->get();

            return view('backEnd.frontSettings.front_result.front_result', ['frontResults' => $frontResults]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $maxFileSize = generalSetting()->file_size * 1024;
        $rules = [
            'title' => 'required',
            'publish_date' => 'required',
        ];
        
        if ($request->content_type == 'file') {
            $rules['file'] = $request->link == null
                ? 'required|mimes:jpg,png,jpeg,pdf|max:' . $maxFileSize
                : 'nullable';
        } else {
            $rules['link'] = $request->file == null ? 'required' : 'nullable';
        }
        
        $validator = Validator::make($input, $rules);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        /*
        try {
        */
            $destination = 'public/uploads/front_result/';
            $frontResult = new FrontResult();
            $frontResult->title = $request->title;
            $frontResult->publish_date = date('Y-m-d', strtotime($request->publish_date));
            if ($request->link) {
                $frontResult->link = $request->link;
            }

            if ($request->file) {
                $frontResult->result_file = fileUpload($request->file, $destination);
            }

            $frontResult->school_id = app('school')->id;
            $result = $frontResult->save();
            Toastr::success('Operation successful', 'Success');

            return redirect()->route('front-result');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function edit($id)
    {
        /*
        try {
        */
            $frontResults = FrontResult::where('school_id', app('school')->id)->get();
            $add_front_result = FrontResult::find($id);

            return view('backEnd.frontSettings.front_result.front_result', ['frontResults' => $frontResults, 'add_front_result' => $add_front_result]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(Request $request)
    {
        $frontResult = FrontResult::find($request->id);
        $input = $request->all();
        $maxFileSize = generalSetting()->file_size * 1024;
        $rules = [
            'title' => 'required',
            'publish_date' => 'required',
        ];
        
        if ($request->content_type == 'file') {
            if (!$frontResult->result_file) {
                $rules['file'] = $request->link == null
                    ? 'required|mimes:jpg,png,jpeg,pdf|max:' . $maxFileSize
                    : 'sometimes|nullable';
            }
        } else {
            $rules['link'] = $request->file == null ? 'required' : 'nullable';
        }
        
        $validator = Validator::make($input, $rules);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        /*
        try {
        */
            $destination = 'public/uploads/front_result/';
            $frontResult->title = $request->title;
            $frontResult->publish_date = date('Y-m-d', strtotime($request->publish_date));
            if ($request->link) {
                $frontResult->link = $request->link;
            }

            if ($request->file) {
                $frontResult->result_file = fileUpload($request->file, $destination);
            }

            $frontResult->school_id = app('school')->id;
            $result = $frontResult->save();
            Toastr::success('Operation successful', 'Success');

            return redirect()->route('front-result');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteModal($id)
    {
        /*
        try {
        */
            $frontResult = FrontResult::find($id);

            return view('backEnd.frontSettings.front_result.front_result_delete_modal', ['frontResult' => $frontResult]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function delete($id)
    {
        /*
        try {
        */
            $frontResult = FrontResult::where('id', $id)->first();
            $frontResult->delete();
            Toastr::success('Deleted successfully', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
