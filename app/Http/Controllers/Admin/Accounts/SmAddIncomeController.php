<?php

namespace App\Http\Controllers\Admin\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Accounts\SmAddIncomeRequest;
use App\SmAddIncome;
use App\SmBankAccount;
use App\SmBankStatement;
use App\SmChartOfAccount;
use App\SmPaymentMethhod;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SmAddIncomeController extends Controller
{
    public function index(Request $request)
    {
        /*
        try {
        */
            $add_incomes = SmAddIncome::with('paymentMethod:method,id', 'ACHead:head,type')->select(['name', 'id', 'date', 'payment_method_id', 'income_head_id', 'amount'])->get();
            $income_heads = SmChartOfAccount::where('type', 'I')->select(['head', 'type', 'id'])->get();
            $bank_accounts = SmBankAccount::where('school_id', Auth::user()->school_id)->select(['bank_name', 'account_name', 'opening_balance', 'account_number', 'current_balance'])->get();
            $payment_methods = SmPaymentMethhod::select(['method', 'id', 'type'])->get();

            return view('backEnd.accounts.add_income', ['add_incomes' => $add_incomes, 'income_heads' => $income_heads, 'bank_accounts' => $bank_accounts, 'payment_methods' => $payment_methods]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmAddIncomeRequest $smAddIncomeRequest)
    {
        /*
        try {
        */
            $destination = 'public/uploads/add_income/';
            // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $smAddIncome = new SmAddIncome();
            $smAddIncome->name = $smAddIncomeRequest->name;
            $smAddIncome->income_head_id = $smAddIncomeRequest->income_head;
            $smAddIncome->date = date('Y-m-d', strtotime($smAddIncomeRequest->date));
            $smAddIncome->payment_method_id = $smAddIncomeRequest->payment_method;
            if (paymentMethodName($smAddIncomeRequest->payment_method)) {
                $smAddIncome->account_id = $smAddIncomeRequest->accounts;
            }

            $smAddIncome->amount = $smAddIncomeRequest->amount;
            $smAddIncome->file = fileUpload($smAddIncomeRequest->file, $destination);
            $smAddIncome->description = $smAddIncomeRequest->description;
            $smAddIncome->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smAddIncome->un_academic_id = getAcademicId();
            } else {
                $smAddIncome->academic_id = getAcademicId();
            }

            $smAddIncome->save();

            if (paymentMethodName($smAddIncomeRequest->payment_method)) {
                $bank = SmBankAccount::where('id', $smAddIncomeRequest->accounts)->first();
                $after_balance = $bank->current_balance + $smAddIncomeRequest->amount;

                $smBankStatement = new SmBankStatement();
                $smBankStatement->amount = $smAddIncomeRequest->amount;
                $smBankStatement->after_balance = $after_balance;
                $smBankStatement->type = 1;
                $smBankStatement->details = $smAddIncomeRequest->name;
                $smBankStatement->item_sell_id = $smAddIncome->id;
                $smBankStatement->payment_date = date('Y-m-d', strtotime($smAddIncomeRequest->date));
                $smBankStatement->bank_id = $smAddIncomeRequest->accounts;
                $smBankStatement->school_id = Auth::user()->school_id;
                $smBankStatement->payment_method = $smAddIncomeRequest->payment_method;
                $smBankStatement->save();

                $current_balance = SmBankAccount::find($smAddIncomeRequest->accounts);
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

    public function edit(Request $request, $id)
    {
        /*
        try {
        */
            $add_income = SmAddIncome::find($id);
            $add_incomes = SmAddIncome::get();
            $income_heads = SmChartOfAccount::get();
            $bank_accounts = SmBankAccount::where('school_id', Auth::user()->school_id)->get();
            $payment_methods = SmPaymentMethhod::get();

            return view('backEnd.accounts.add_income', ['add_income' => $add_income, 'add_incomes' => $add_incomes, 'income_heads' => $income_heads, 'bank_accounts' => $bank_accounts, 'payment_methods' => $payment_methods]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmAddIncomeRequest $smAddIncomeRequest)
    {
        /*
        try {
        */
            $destination = 'public/uploads/add_income/';
            // DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $add_income = SmAddIncome::find($smAddIncomeRequest->id);
            $add_income->name = $smAddIncomeRequest->name;
            $add_income->income_head_id = $smAddIncomeRequest->income_head;
            $add_income->date = date('Y-m-d', strtotime($smAddIncomeRequest->date));
            $add_income->payment_method_id = $smAddIncomeRequest->payment_method;
            if (paymentMethodName($smAddIncomeRequest->payment_method)) {
                $add_income->account_id = $smAddIncomeRequest->accounts;
            }

            $add_income->amount = $smAddIncomeRequest->amount;
            $add_income->file = fileUpdate($add_income->file, $smAddIncomeRequest->file, $destination);
            $add_income->description = $smAddIncomeRequest->description;
            $add_income->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $add_income->un_academic_id = getAcademicId();
            } else {
                $add_income->academic_id = getAcademicId();
            }

            $add_income->save();

            if (paymentMethodName($smAddIncomeRequest->payment_method)) {
                SmBankStatement::where('item_sell_id', $smAddIncomeRequest->id)->delete();
                $bank = SmBankAccount::where('id', $smAddIncomeRequest->accounts)->first();
                $after_balance = $bank->current_balance + $smAddIncomeRequest->amount;

                $smBankStatement = new SmBankStatement();
                $smBankStatement->amount = $smAddIncomeRequest->amount;
                $smBankStatement->after_balance = $after_balance;
                $smBankStatement->type = 1;
                $smBankStatement->details = $smAddIncomeRequest->name;
                $smBankStatement->item_sell_id = $add_income->id;
                $smBankStatement->payment_date = date('Y-m-d', strtotime($smAddIncomeRequest->date));
                $smBankStatement->bank_id = $smAddIncomeRequest->accounts;
                $smBankStatement->school_id = Auth::user()->school_id;
                $smBankStatement->payment_method = $smAddIncomeRequest->payment_method;
                $smBankStatement->save();

                $current_balance = SmBankAccount::find($smAddIncomeRequest->accounts);
                $current_balance->current_balance = $after_balance;
                $current_balance->update();
            }

            Toastr::success('Operation successful', 'Success');

            return redirect()->route('add_income');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function delete(Request $request)
    {
        /*
        try {
*/

            $add_income = SmAddIncome::find($request->id);
            if ($add_income->file !== '') {
                $path = $add_income->file;
                if (file_exists($path)) {
                    unlink($path);
                }
            }

            // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            if (paymentMethodName($add_income->payment_method_id) && $add_income->account_id) {
                $reset_balance = SmBankStatement::where('item_sell_id', $request->id)->sum('amount');
                $bank = SmBankAccount::where('id', $add_income->account_id)->first();
                $after_balance = $bank->current_balance - $reset_balance;

                $current_balance = SmBankAccount::find($add_income->account_id);
                $current_balance->current_balance = $after_balance;
                $current_balance->update();
                SmBankStatement::where('item_sell_id', $request->id)->delete();
            }

            $add_income->delete();

            Toastr::success('Operation successful', 'Success');

            return redirect()->route('add_income');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
