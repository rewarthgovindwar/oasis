<?php

namespace App\Http\Controllers\Admin\Accounts;

use App\Http\Controllers\Controller;
use App\SmIncomeHead;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmIncomeHeadController extends Controller
{

    public function index()
    {

        /*try {*/
            $income_heads = SmIncomeHead::where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.accounts.income_head', ['income_heads' => $income_heads]);
		/*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }*/

    }

    public function store(Request $request)
    {
        $request->validate([
            'income_head' => 'required|unique:sm_income_heads,name',
        ]);
        /*try {*/
            $smIncomeHead = new SmIncomeHead();
            $smIncomeHead->name = $request->income_head;
            $smIncomeHead->description = $request->description;
            $smIncomeHead->school_id = Auth::user()->school_id;
            $smIncomeHead->academic_id = getAcademicId();
            $smIncomeHead->save();

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();

        /*} catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }*/
    }

    public function edit($id)
    {

       /* try {*/
            $auth = Auth::user();
            // $income_head = SmIncomeHead::find($id);
            if (checkAdmin() == true) {
                $income_head = SmIncomeHead::find($id);
            } else {
                $income_head = SmIncomeHead::where('id', $id)->where('school_id', $auth->school_id)->first();
            }

            $income_heads = SmIncomeHead::where('school_id', $auth->school_id)->get();

            return view('backEnd.accounts.income_head', ['income_head' => $income_head, 'income_heads' => $income_heads]);
		/*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }*/
    }

    public function update(Request $request)
    {
        $request->validate([
            'income_head' => 'required|unique:sm_income_heads,name,'.$request->id,
        ]);
        /*try {*/
            $fees_discount = SmIncomeHead::find($request->id);
            $fees_discount->name = $request->income_head;
            $fees_discount->description = $request->description;
            $result = $fees_discount->save();
            if ($result) {
                Toastr::success('Operation successful', 'Success');
                return redirect('income-head');
                // return redirect('income-head')->with('message-success', 'Income Head has been updated successfully');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
            // return redirect()->back()->with('message-danger', 'Something went wrong, please try again');

        /*} catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }*/
    }

    public function delete($id)
    {

		/*
		try{
		*/
			$fees_discount = SmIncomeHead::destroy($id);
			if($fees_discount){
				Toastr::success('Operation successful', 'Success');
				return redirect()->back();
				// return redirect()->back()->with('message-success-delete', 'Income Head has been deleted successfully');
			}else{
				Toastr::error('Operation Failed', 'Failed');
            	return redirect()->back();
				// return redirect()->back()->with('message-danger-delete', 'Something went wrong, please try again');
			}
		/*
		}catch (\Exception $e) {
		   Toastr::error('Operation Failed', 'Failed');
		   return redirect()->back();
		}
		*/
    }
}
