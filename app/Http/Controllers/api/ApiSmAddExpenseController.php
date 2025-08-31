<?php

namespace App\Http\Controllers\api;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\SmAddExpense;
use App\SmBankAccount;
use App\SmChartOfAccount;
use App\SmGeneralSettings;
use App\SmPaymentMethhod;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class ApiSmAddExpenseController extends Controller
{

    public function index(Request $request)
    {
        try {
            $add_expenses = SmAddExpense::where('active_status', 1)->get();
            $expense_heads = SmChartOfAccount::where('type', 'E')->where('active_status', 1)->get();
            $bank_accounts = SmBankAccount::where('active_status', '=', 1)->get();
            $payment_methods = SmPaymentMethhod::where('active_status', '=', 1)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['add_expenses'] = $add_expenses->toArray();
                $data['expense_heads'] = $expense_heads->toArray();
                $data['bank_accounts'] = $bank_accounts->toArray();
                $data['payment_methods'] = $payment_methods->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.accounts.add_expense', ['add_expenses' => $add_expenses, 'expense_heads' => $expense_heads, 'bank_accounts' => $bank_accounts, 'payment_methods' => $payment_methods]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): void
    {
        //
    }

    public function store(Request $request)
    {
        $input = $request->all();
        if ($request->payment_method == '3') {
            $validator = Validator::make($input, [
                'expense_head' => 'required',
                'name' => 'required',
                'date' => 'required',
                'accounts' => 'required',
                'payment_method' => 'required',
                'amount' => 'required',
                'file' => 'sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt',
            ]);
        } else {
            $validator = Validator::make($input, [
                'expense_head' => 'required',
                'name' => 'required',
                'date' => 'required',
                'payment_method' => 'required',
                'amount' => 'required',
                'file' => 'sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt',
            ]);
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
            $maxFileSize = SmGeneralSettings::first('file_size')->file_size;
            $file = $request->file('file');
            $fileSize = filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if ($fileSizeKb >= $maxFileSize) {
                Toastr::error('Max upload file size '.$maxFileSize.' Mb is set in system', 'Failed');

                return redirect()->back();
            }

            $fileName = '';
            if ($request->file('file') !== '') {
                $file = $request->file('file');
                $fileName = md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();
                $file->move('public/uploads/addExpense/', $fileName);
                $fileName = 'public/uploads/addExpense/'.$fileName;
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $smAddExpense = new SmAddExpense();
            $smAddExpense->name = $request->name;
            $smAddExpense->expense_head_id = $request->expense_head;
            $smAddExpense->date = date('Y-m-d', strtotime($request->date));
            $smAddExpense->payment_method_id = $request->payment_method;
            if ($request->payment_method == '3') {
                $smAddExpense->account_id = $request->accounts;
            }

            $smAddExpense->amount = $request->amount;
            $smAddExpense->file = $fileName;
            $smAddExpense->description = $request->description;
            $result = $smAddExpense->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Expense has been created successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            if ($result) {
                return redirect()->back()->with('message-success', 'Expense has been created successfully');
            }

            return redirect()->back()->with('message-danger', 'Something went wrong, please try again');

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
            $add_expense = SmAddExpense::find($id);
            $add_expenses = SmAddExpense::where('active_status', '=', 1)->get();
            $expense_heads = SmChartOfAccount::where('active_status', '=', 1)->get();
            $bank_accounts = SmBankAccount::where('active_status', '=', 1)->get();
            $payment_methods = SmPaymentMethhod::where('active_status', '=', 1)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['add_expenses'] = $add_expenses->toArray();
                $data['add_expense'] = $add_expense->toArray();
                $data['expense_heads'] = $expense_heads->toArray();
                $data['bank_accounts'] = $bank_accounts->toArray();
                $data['payment_methods'] = $payment_methods->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.accounts.add_expense', ['add_expenses' => $add_expenses, 'add_expense' => $add_expense, 'expense_heads' => $expense_heads, 'bank_accounts' => $bank_accounts, 'payment_methods' => $payment_methods]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     */
    public function edit($id): void
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $input = $request->all();
        if ($request->payment_method == '3') {
            $validator = Validator::make($input, [
                'expense_head' => 'required',
                'name' => 'required',
                'date' => 'required',
                'accounts' => 'required',
                'payment_method' => 'required',
                'amount' => 'required',
                'file' => 'sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt',
            ]);
        } else {
            $validator = Validator::make($input, [
                'expense_head' => 'required',
                'name' => 'required',
                'date' => 'required',
                'payment_method' => 'required',
                'amount' => 'required',
                'file' => 'sometimes|nullable|mimes:pdf,doc,docx,jpg,jpeg,png,txt',
            ]);
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
            $maxFileSize = SmGeneralSettings::first('file_size')->file_size;
            $file = $request->file('file');
            $fileSize = filesize($file);
            $fileSizeKb = ($fileSize / 1000000);
            if ($fileSizeKb >= $maxFileSize) {
                Toastr::error('Max upload file size '.$maxFileSize.' Mb is set in system', 'Failed');

                return redirect()->back();
            }

            $fileName = '';
            if ($request->file('file') !== '') {
                $add_expense = SmAddExpense::find($request->id);
                unlink($add_expense->file);

                $file = $request->file('file');
                $fileName = md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();
                $file->move('public/uploads/addExpense/', $fileName);
                $fileName = 'public/uploads/addExpense/'.$fileName;
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $add_expense = SmAddExpense::find($request->id);
            $add_expense->name = $request->name;
            $add_expense->expense_head_id = $request->expense_head;
            $add_expense->date = date('Y-m-d', strtotime($request->date));
            $add_expense->payment_method_id = $request->payment_method;
            if ($request->payment_method == '3') {
                $add_expense->account_id = $request->accounts;
            }

            $add_expense->amount = $request->amount;

            $add_expense->file = $fileName;

            $add_expense->description = $request->description;
            $result = $add_expense->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Expense has been updated successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            if ($result) {
                return redirect('add-expense')->with('message-success', 'Expense has been updated successfully');
            }

            return redirect()->back()->with('message-danger', 'Something went wrong, please try again');

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
            $add_expense = SmAddExpense::find($id);
            if ($add_expense->file !== '') {
                unlink($add_expense->file);
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $result = $add_expense->delete();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Expense has been deleted successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            if ($result) {
                return redirect('add-expense')->with('message-success-delete', 'Expense has been deleted successfully');
            }

            return redirect()->back()->with('message-danger-delete', 'Something went wrong, please try again');

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

    }
}
