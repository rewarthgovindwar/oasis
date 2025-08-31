<?php

namespace Modules\DownloadCenter\Http\Controllers;

use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\DownloadCenter\Entities\ContentType;

class ContentTypeController extends Controller
{
    public function contentType()
    {
        try {
            $types = ContentType::get();

            return view('downloadcenter::contentType.contentType', ['types' => $types]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function contentTypeSave(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $contentType = new ContentType();
            $contentType->name = $request->name;
            $contentType->description = $request->description;
            $contentType->save();

            Toastr::success('Operation Successful', 'Success');

            return redirect()->route('download-center.content-type');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function contentTypeEdit(Request $request, $id)
    {
        try {
            $type = ContentType::find($id);
            $types = ContentType::get();

            return view('downloadcenter::contentType.contentType', ['types' => $types, 'type' => $type]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function contentTypeUpdate(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $type = ContentType::find($request->type_id);
            $type->name = $request->name;
            $type->description = $request->description;
            $type->save();

            Toastr::success('Operation Successful', 'Success');

            return redirect()->route('download-center.content-type');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function contentTypeDelete($id)
    {
        try {
            $type = ContentType::where('id', $id)->first();
            $type->delete();
            Toastr::success('Deleted successfully', 'Success');

            return redirect()->route('download-center.content-type');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }
}
