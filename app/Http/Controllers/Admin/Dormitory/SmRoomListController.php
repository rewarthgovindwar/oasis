<?php

namespace App\Http\Controllers\Admin\Dormitory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Dormitory\SmDormitoryRoomRequest;
use App\SmDormitoryList;
use App\SmRoomList;
use App\SmRoomType;
use App\SmStudent;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmRoomListController extends Controller
{
    public function index(Request $request)
    {
        /*
        try {
        */
            $room_lists = SmRoomList::with('dormitory', 'roomType')->get();
            $room_types = SmRoomType::get();
            $dormitory_lists = SmDormitoryList::orderby('id', 'DESC')->get();

            return view('backEnd.dormitory.room_list', ['room_lists' => $room_lists, 'room_types' => $room_types, 'dormitory_lists' => $dormitory_lists]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmDormitoryRoomRequest $smDormitoryRoomRequest)
    {
        /*
        try {
        */
            $smRoomList = new SmRoomList();
            $smRoomList->name = $smDormitoryRoomRequest->name;
            $smRoomList->dormitory_id = $smDormitoryRoomRequest->dormitory;
            $smRoomList->room_type_id = $smDormitoryRoomRequest->room_type;
            $smRoomList->number_of_bed = $smDormitoryRoomRequest->number_of_bed;
            $smRoomList->cost_per_bed = $smDormitoryRoomRequest->cost_per_bed;
            $smRoomList->description = $smDormitoryRoomRequest->description;
            $smRoomList->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smRoomList->un_academic_id = getAcademicId();
            } else {
                $smRoomList->academic_id = getAcademicId();
            }

            $smRoomList->save();

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function show(Request $request, $id)
    {
        /*
        try {
        */
            $room_list = SmRoomList::find($id);
            $room_lists = SmRoomList::with('dormitory', 'roomType')->get();
            $room_types = SmRoomType::get();
            $dormitory_lists = SmDormitoryList::where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.dormitory.room_list', ['room_lists' => $room_lists, 'room_list' => $room_list, 'room_types' => $room_types, 'dormitory_lists' => $dormitory_lists]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmDormitoryRoomRequest $smDormitoryRoomRequest, $id)
    {
        /*
        try {
        */
            $room_list = SmRoomList::find($smDormitoryRoomRequest->id);
            $room_list->name = $smDormitoryRoomRequest->name;
            $room_list->dormitory_id = $smDormitoryRoomRequest->dormitory;
            $room_list->room_type_id = $smDormitoryRoomRequest->room_type;
            $room_list->number_of_bed = $smDormitoryRoomRequest->number_of_bed;
            $room_list->cost_per_bed = $smDormitoryRoomRequest->cost_per_bed;
            $room_list->description = $smDormitoryRoomRequest->description;
            if (moduleStatusCheck('University')) {
                $room_list->un_academic_id = getAcademicId();
            }

            $room_list->save();

            Toastr::success('Operation successful', 'Success');

            return redirect('room-list');
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
            $key_id = 'room_id';
            $tables = SmStudent::where('dormitory_id', $id)->first();
            /*
            try {
            */
                if ($tables == null) {
                    SmRoomList::destroy($id);

                    Toastr::success('Operation successful', 'Success');

                    return redirect()->back();
                } else {
                    $msg = 'This data already used in Student Please remove those data first';
                    Toastr::error($msg, 'Failed');
                    return redirect()->back();
                }
            /*
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
        */
    }
}
