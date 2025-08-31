<?php

namespace App\Http\Controllers\Admin\Academics;

use Exception;
use App\SmClassRoom;
use App\ApiBaseMethod;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\Academics\SmClassRoomRequest;

class SmClassRoomController extends Controller
{
    public function index(Request $request)
    {

        /*
        try {
        */
        $class_rooms = SmClassRoom::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendResponse($class_rooms, null);
        }

        return view('backEnd.academics.class_room', ['class_rooms' => $class_rooms]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
        */
    }

    public function create(): void
    {
        //
    }

    public function store(SmClassRoomRequest $smClassRoomRequest)
    {
        /*
        try {
        */
        $smClassRoom = new SmClassRoom();
        $smClassRoom->room_no = $smClassRoomRequest->room_no;
        $smClassRoom->capacity = $smClassRoomRequest->capacity;
        $smClassRoom->school_id = Auth::user()->school_id;
        if (moduleStatusCheck('University')) {
            $smClassRoom->un_academic_id = getAcademicId();
        } else {
            $smClassRoom->academic_id = getAcademicId();
        }

        $result = $smClassRoom->save();

        Toastr::success('Operation successful', 'Success');

        return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
        */
    }

    public function show($id): void
    {
        //
    }

    public function edit(Request $request, $id)
    {
        /*
        try {
        */
        $class_room = SmClassRoom::find($id);
        $class_rooms = SmClassRoom::where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $data = [];
            $data['class_room'] = $class_room->toArray();
            $data['class_rooms'] = $class_rooms->toArray();

            return ApiBaseMethod::sendResponse($data, null);
        }

        return view('backEnd.academics.class_room', ['class_room' => $class_room, 'class_rooms' => $class_rooms]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmClassRoomRequest $smClassRoomRequest, $id)
    {
        /*
        try {
        */
        $class_room = SmClassRoom::find($smClassRoomRequest->id);
        $class_room->room_no = $smClassRoomRequest->room_no;
        $class_room->capacity = $smClassRoomRequest->capacity;
        $result = $class_room->save();
        Toastr::success('Operation successful', 'Success');

        return redirect('class-room');
        /*
                } catch (Exception $exception) {
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
        $id_key = 'room_id';
        $tables = \App\tableList::getTableList($id_key, $id);
        /*
        try {
        */
        if ($tables == null) {
            $delete_query = SmClassRoom::destroy($id);
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($delete_query) {
                    return ApiBaseMethod::sendResponse(null, 'Class Room has been deleted successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        }

        $msg = 'This data already used in  : '.$tables.' Please remove those data first';
        Toastr::error($msg, 'Failed');

        return redirect()->back();
        /*
        } catch (\Illuminate\Database\QueryException $e) {
            $msg = 'This data already used in  : '.$tables.' Please remove those data first';
            Toastr::error($msg, 'Failed');

            return redirect()->back();
        }
    } catch (Exception $exception) {
        Toastr::error('Operation Failed', 'Failed');

        return redirect()->back();
    }
    */
    }
}
