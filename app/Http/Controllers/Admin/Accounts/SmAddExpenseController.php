<?php

namespace App\Http\Controllers\Admin\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Accounts\SmExpenseRequest;
use App\SmAddExpense;
use App\SmBankAccount;
use App\SmBankStatement;
use App\SmChartOfAccount;
use App\SmPaymentMethhod;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SmAddExpenseController extends Controller
{

    public function index(Request $request)
    {
        /*
        try {
        */
            $add_expenses = SmAddExpense::with('expenseHead:id,name', 'ACHead:id,head,type', 'paymentMethod:id,method,type', 'account:id,bank_name,account_name,account_number,account_type,opening_balance,current_balance')->get();
            $expense_heads = SmChartOfAccount::where('type', 'E')->get(['head', 'id']);
            $bank_accounts = SmBankAccount::where('school_id', Auth::user()->school_id)->get();
            $payment_methods = SmPaymentMethhod::get(['method', 'id']);

            return view('backEnd.accounts.add_expense', ['add_expenses' => $add_expenses, 'expense_heads' => $expense_heads, 'bank_accounts' => $bank_accounts, 'payment_methods' => $payment_methods]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmExpenseRequest $smExpenseRequest)
    {
        /*
        try {
        */
            $destination = 'public/uploads/addExpense/';
            // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $user = Auth::user();
            $smAddExpense = new SmAddExpense();
            $smAddExpense->name = $smExpenseRequest->name;
            $smAddExpense->expense_head_id = $smExpenseRequest->expense_head;
            $smAddExpense->date = date('Y-m-d', strtotime($smExpenseRequest->date));
            $smAddExpense->payment_method_id = $smExpenseRequest->payment_method;
            if (paymentMethodName($smExpenseRequest->payment_method)) {
                $smAddExpense->account_id = $smExpenseRequest->accounts;
            }

            $smAddExpense->amount = $smExpenseRequest->amount;
            $smAddExpense->file = fileUpload($smExpenseRequest->file, $destination);
            $smAddExpense->description = $smExpenseRequest->description;
            $smAddExpense->school_id = $user->school_id;
            if (moduleStatusCheck('University')) {
                $smAddExpense->un_academic_id = getAcademicId();
            } else {
                $smAddExpense->academic_id = getAcademicId();
            }

            $result = $smAddExpense->save();

            if (paymentMethodName($smExpenseRequest->payment_method)) {
                $bank = SmBankAccount::where('id', $smExpenseRequest->accounts)
                    ->where('school_id', $user->school_id)
                    ->first();
                $after_balance = $bank->current_balance - $smExpenseRequest->amount;

                $smBankStatement = new SmBankStatement();
                $smBankStatement->amount = $smExpenseRequest->amount;
                $smBankStatement->after_balance = $after_balance;
                $smBankStatement->type = 0;
                $smBankStatement->details = $smExpenseRequest->name;
                $smBankStatement->item_receive_id = $smAddExpense->id;
                $smBankStatement->payment_date = date('Y-m-d', strtotime($smExpenseRequest->date));
                $smBankStatement->bank_id = $smExpenseRequest->accounts;
                $smBankStatement->school_id = $user->school_id;
                $smBankStatement->payment_method = $smExpenseRequest->payment_method;
                $smBankStatement->save();

                $current_balance = SmBankAccount::find($smExpenseRequest->accounts);
                $current_balance->current_balance = $after_balance;
                $current_balance->update();
            }

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
            $add_expense = SmAddExpense::find($id);
            $add_expenses = SmAddExpense::with('expenseHead:id,name', 'ACHead:id,head,type', 'paymentMethod:id,method,type', 'account:id,bank_name,account_name,account_number,account_type,opening_balance,current_balance')->get();
            $expense_heads = SmChartOfAccount::where('type', 'E')->get(['head', 'id']);
            $bank_accounts = SmBankAccount::where('school_id', Auth::user()->school_id)->get();
            $payment_methods = SmPaymentMethhod::get(['method', 'id']);

            return view('backEnd.accounts.add_expense', ['add_expenses' => $add_expenses, 'add_expense' => $add_expense, 'expense_heads' => $expense_heads, 'bank_accounts' => $bank_accounts, 'payment_methods' => $payment_methods]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmExpenseRequest $smExpenseRequest, $id)
    {
        /*
        try {
        */
            $user = Auth::user();
            $destination = 'public/uploads/addExpense/';
            // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            if (checkAdmin() == true) {
                $add_expense = SmAddExpense::find($smExpenseRequest->id);
            } else {
                $add_expense = SmAddExpense::where('id', $smExpenseRequest->id)->where('school_id', Auth::user()->school_id)->first();
            }

            $add_expense->name = $smExpenseRequest->name;
            $add_expense->expense_head_id = $smExpenseRequest->expense_head;
            $add_expense->date = date('Y-m-d', strtotime($smExpenseRequest->date));
            $add_expense->payment_method_id = $smExpenseRequest->payment_method;
            if (paymentMethodName($smExpenseRequest->payment_method)) {
                $add_expense->account_id = $smExpenseRequest->accounts;
            }

            $add_expense->amount = $smExpenseRequest->amount;
            $add_expense->file = fileUpdate($add_expense->file, $smExpenseRequest->file, $destination);
            $add_expense->school_id = $user->school_id;
            $add_expense->description = $smExpenseRequest->description;
            if (moduleStatusCheck('University')) {
                $add_expense->un_academic_id = getAcademicId();
            } else {
                $add_expense->academic_id = getAcademicId();
            }

            $result = $add_expense->save();

            if (paymentMethodName($smExpenseRequest->payment_method)) {
                SmBankStatement::where('item_receive_id', $smExpenseRequest->id)
                    ->where('school_id', $user->school_id)
                    ->delete();
                $bank = SmBankAccount::where('id', $smExpenseRequest->accounts)
                    ->where('school_id', $user->school_id)
                    ->first();
                $after_balance = $bank->current_balance - $smExpenseRequest->amount;

                $smBankStatement = new SmBankStatement();
                $smBankStatement->amount = $smExpenseRequest->amount;
                $smBankStatement->after_balance = $after_balance;
                $smBankStatement->type = 0;
                $smBankStatement->details = $smExpenseRequest->name;
                $smBankStatement->item_receive_id = $add_expense->id;
                $smBankStatement->payment_date = date('Y-m-d', strtotime($smExpenseRequest->date));
                $smBankStatement->bank_id = $smExpenseRequest->accounts;
                $smBankStatement->school_id = $user->school_id;
                $smBankStatement->payment_method = $smExpenseRequest->payment_method;
                $smBankStatement->save();

                $current_balance = SmBankAccount::find($smExpenseRequest->accounts);
                $current_balance->current_balance = $after_balance;
                $current_balance->update();
            }

            Toastr::success('Operation successful', 'Success');

            return redirect('add-expense');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function destroy(Request $request)
    {
        /*
        try {
        */
            $id = $request->id;
            $add_expense = SmAddExpense::find($id);
            if ($add_expense->file !== '') {
                unlink($add_expense->file);
            }

            $user = Auth::user();
            if (paymentMethodName($add_expense->payment_method_id)) {
                $reset_balance = SmBankStatement::where('item_receive_id', $add_expense->account_id)
                    ->where('school_id', $user->school_id)
                    ->sum('amount');

                $bank = SmBankAccount::where('id', $add_expense->account_id)
                    ->where('school_id', $user->school_id)
                    ->first();
                $after_balance = $bank->current_balance + $add_expense->amount;

                $current_balance = SmBankAccount::find($add_expense->account_id);
                $current_balance->current_balance = $after_balance;
                $current_balance->update();

                SmBankStatement::where('item_receive_id', $id)
                    ->where('school_id', $user->school_id)
                    ->delete();
            }

            $add_expense->delete();

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
