<?php

namespace App\Http\Controllers\Admin\Dormitory;

use Exception;
use App\SmDormitoryList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\Dormitory\SmDormitoryRequest;

class SmDormitoryListController extends Controller
{
    public function index(Request $request)
    {
        /*
        try {
        */
        $dormitory_lists = SmDormitoryList::get();

        return view('backEnd.dormitory.dormitory_list', ['dormitory_lists' => $dormitory_lists]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmDormitoryRequest $smDormitoryRequest)
    {
        // school wise uquine validation
        /*
        try {
        */
        $smDormitoryList = new SmDormitoryList();
        $smDormitoryList->dormitory_name = $smDormitoryRequest->dormitory_name;
        $smDormitoryList->type = $smDormitoryRequest->type;
        $smDormitoryList->address = $smDormitoryRequest->address;
        $smDormitoryList->intake = $smDormitoryRequest->intake;
        $smDormitoryList->description = $smDormitoryRequest->description;
        $smDormitoryList->school_id = Auth::user()->school_id;
        if (moduleStatusCheck('University')) {
            $smDormitoryList->un_academic_id = getAcademicId();
        } else {
            $smDormitoryList->academic_id = getAcademicId();
        }

        $smDormitoryList->save();

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
        $dormitory_list = SmDormitoryList::find($id);
        $dormitory_lists = SmDormitoryList::get();

        return view('backEnd.dormitory.dormitory_list', ['dormitory_lists' => $dormitory_lists, 'dormitory_list' => $dormitory_list]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmDormitoryRequest $smDormitoryRequest, $id)
    {
        /*
        try {
        */
        $dormitory_list = SmDormitoryList::find($smDormitoryRequest->id);
        $dormitory_list->dormitory_name = $smDormitoryRequest->dormitory_name;
        $dormitory_list->type = $smDormitoryRequest->type;
        $dormitory_list->address = $smDormitoryRequest->address;
        $dormitory_list->intake = $smDormitoryRequest->intake;
        $dormitory_list->description = $smDormitoryRequest->description;
        if (moduleStatusCheck('University')) {
            $dormitory_list->un_academic_id = getAcademicId();
        }

        $dormitory_list->save();

        Toastr::success('Operation successful', 'Success');

        return redirect('dormitory-list');
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
        $tables = \App\tableList::getTableList('dormitory_id', $id);
        /*
        try {
        */
        if ($tables == null) {
            SmDormitoryList::destroy($id);
            Toastr::success('Operation successful', 'Success');

            return redirect('dormitory-list');
        }

        $msg = 'This data already used in  : '.$tables.' Please remove those data first';
        Toastr::error($msg, 'Failed');

        return redirect()->back();
        /*
                } catch (Exception $exception) {
                    Toastr::error('Operation Failed', 'Failed');

                    return redirect()->back();
                }
                */
    }
}
