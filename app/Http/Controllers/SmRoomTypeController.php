<?php

namespace App\Http\Controllers;

use App\ApiBaseMethod;
use App\SmRoomType;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SmRoomTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        try {
            $room_types = SmRoomType::where('school_id', Auth::user()->school_id)->orderby('id', 'DESC')->get();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($room_types, null);
            }

            return view('backEnd.dormitory.room_type', ['room_types' => $room_types]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'type' => 'required',
        ]);

        // school wise uquine validation
        $is_duplicate = SmRoomType::where('school_id', Auth::user()->school_id)->where('type', $request->type)->first();
        if ($is_duplicate) {
            Toastr::error('Duplicate room type found!', 'Failed');

            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $smRoomType = new SmRoomType();
            $smRoomType->type = $request->type;
            $smRoomType->description = $request->description;
            $smRoomType->school_id = Auth::user()->school_id;
            $smRoomType->academic_id = getAcademicId();
            $result = $smRoomType->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Assign vehicle has been updated successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again');

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
            // $room_type = SmRoomType::find($id);
            if (checkAdmin() == true) {
                $room_type = SmRoomType::find($id);
            } else {
                $room_type = SmRoomType::where('id', $id)->where('school_id', Auth::user()->school_id)->first();
            }

            $room_types = SmRoomType::where('school_id', Auth::user()->school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['room_type'] = $room_type;
                $data['room_types'] = $room_types->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.dormitory.room_type', ['room_types' => $room_types, 'room_type' => $room_type]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'type' => 'required| max:200',
        ]);

        // school wise uquine validation
        $is_duplicate = SmRoomType::where('school_id', Auth::user()->school_id)->where('type', $request->type)->where('id', '!=', $request->id)->first();
        if ($is_duplicate) {
            Toastr::error('Duplicate room type found!', 'Failed');

            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // $room_type = SmRoomType::find($request->id);
            if (checkAdmin() == true) {
                $room_type = SmRoomType::find($request->id);
            } else {
                $room_type = SmRoomType::where('id', $request->id)->where('school_id', Auth::user()->school_id)->first();
            }

            $room_type->type = $request->type;
            $room_type->description = $request->description;
            $result = $room_type->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Assign vehicle has been updated successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again');

            }

            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect('room-type');
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
            $tables = \App\tableList::getTableList('room_type_id', $id);
            try {
                if ($tables == null) {
                    if (checkAdmin() == true) {
                        $room_type = SmRoomType::destroy($id);
                    } else {
                        $room_type = SmRoomType::where('id', $id)->where('school_id', Auth::user()->school_id)->delete();
                    }

                    if ($room_type) {
                        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                            if ($room_type) {
                                return ApiBaseMethod::sendResponse(null, 'Room type has been deleted successfully');
                            }

                            return ApiBaseMethod::sendError('Something went wrong, please try again');

                        }

                        if ($room_type) {
                            Toastr::success('Operation successful', 'Success');

                            return redirect('room-type');
                        }

                        Toastr::error('Operation Failed', 'Failed');

                        return redirect()->back();

                    }

                    Toastr::error('Operation Failed', 'Failed');

                    return redirect()->back();

                }

                $msg = 'This data already used in  : '.$tables.' Please remove those data first';
                Toastr::error($msg, 'Failed');

                return redirect()->back();

            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : '.$tables.' Please remove those data first';
                Toastr::error($msg, 'Failed');

                return redirect()->back();
            } catch (Exception $e) {
                Toastr::error('Operation Failed', 'Failed');

                return redirect()->back();
            }
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }
}
