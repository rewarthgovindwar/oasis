<?php

namespace App\Http\Controllers\Admin\Accounts;

use App\Http\Controllers\Controller;
use App\SmExpenseHead;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmExpenseHeadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        /*
        try {
        */
            $expense_heads = SmExpenseHead::where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.accounts.expense_head', ['expense_heads' => $expense_heads]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:sm_expense_heads,name',
        ]);

        /*
        try {
        */
            $smExpenseHead = new SmExpenseHead();
            $smExpenseHead->name = $request->name;
            $smExpenseHead->description = $request->description;
            $smExpenseHead->school_id = Auth::user()->school_id;
            $smExpenseHead->academic_id = getAcademicId();
            $result = $smExpenseHead->save();
            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect()->back();

                // return redirect()->back()->with('message-success', 'Expense Head has been created successfully');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
            // return redirect()->back()->with('message-danger', 'Something went wrong, please try again');

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function show($id)
    {

        /*
        try {
        */
            $expense_head = SmExpenseHead::find($id);
            $expense_heads = SmExpenseHead::where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.accounts.expense_head', ['expense_heads' => $expense_heads, 'expense_head' => $expense_head]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:sm_expense_heads,name,'.$request->id,
        ]);
        /*
        try {
        */
            $expense_head = SmExpenseHead::find($request->id);
            $expense_head->name = $request->name;
            $expense_head->description = $request->description;
            $result = $expense_head->save();
            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect('expense-head');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function destroy($id)
    {

        /*
        try {
        */
            $expense_head = SmExpenseHead::destroy($id);
            if ($expense_head) {
                Toastr::success('Operation successful', 'Success');

                return redirect('expense-head');
                // return redirect('expense-head')->with('message-success-delete', 'Expense Head has been deleted successfully');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
            // return redirect()->back()->with('message-danger-delete', 'Something went wrong, please try again');

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
