<?php

namespace App\Http\Controllers\Admin\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Accounts\SmBankAccountRequest;
use App\SmAddIncome;
use App\SmBankAccount;
use App\SmBankStatement;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use DataTables;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class SmBankAccountController extends Controller
{
    public function index()
    {
        /*
        try {
        */
            return view('backEnd.accounts.bank_account');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function create() {}

    public function store(SmBankAccountRequest $smBankAccountRequest)
    {
        /*try {*/

            $smBankAccount = new SmBankAccount();
            $smBankAccount->bank_name = $smBankAccountRequest->bank_name;
            $smBankAccount->account_name = $smBankAccountRequest->account_name;
            $smBankAccount->account_number = $smBankAccountRequest->account_number;
            $smBankAccount->account_type = $smBankAccountRequest->account_type;
            $smBankAccount->opening_balance = $smBankAccountRequest->opening_balance;
            $smBankAccount->current_balance = $smBankAccountRequest->opening_balance;
            $smBankAccount->note = $smBankAccountRequest->note;
            $smBankAccount->active_status = 1;
            $smBankAccount->created_by = auth()->user()->id;
            if (moduleStatusCheck('University')) {
                $smBankAccount->un_academic_id = getAcademicId();
            } else {
                $smBankAccount->academic_id = getAcademicId();
            }

            $smBankAccount->school_id = Auth::user()->school_id;
            $smBankAccount->save();

            $smAddIncome = new SmAddIncome();
            $smAddIncome->name = 'Opening Balance';
            $smAddIncome->date = Carbon::now();
            $smAddIncome->amount = $smBankAccountRequest->opening_balance;
            $smAddIncome->item_sell_id = $smBankAccount->id;
            $smAddIncome->active_status = 1;
            $smAddIncome->created_by = Auth()->user()->id;
            $smAddIncome->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smAddIncome->un_academic_id = getAcademicId();
            } else {
                $smAddIncome->academic_id = getAcademicId();
            }

            $smAddIncome->save();

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
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
            $bank_account = SmBankAccount::find($id);
            $bank_accounts = SmBankAccount::where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.accounts.bank_account', ['bank_accounts' => $bank_accounts, 'bank_account' => $bank_account]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function edit($id)
    {
        Toastr::error('Operation Failed', 'Failed');

        return redirect()->back();
    }

    public function update(SmBankAccountRequest $smBankAccountRequest, $id)
    {
        /*
        try {
        */
            $bank_account = SmBankAccount::find($smBankAccountRequest->id);
            $bank_account->bank_name = $smBankAccountRequest->bank_name;
            $bank_account->account_name = $smBankAccountRequest->account_name;
            $bank_account->account_number = $smBankAccountRequest->account_number;
            $bank_account->account_type = $smBankAccountRequest->account_type;
            $bank_account->opening_balance = $smBankAccountRequest->opening_balance;
            $bank_account->note = $smBankAccountRequest->note;
            if (moduleStatusCheck('University')) {
                $bank_account->un_academic_id = getAcademicId();
            } else {
                $bank_account->academic_id = getAcademicId();
            }

            $bank_account->save();

            Toastr::success('Operation successful', 'Success');

            return redirect('bank-account');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function bankTransaction($id)
    {
        $bank_name = SmBankAccount::where('id', $id)->firstOrFail();
        $bank_transactions = SmBankStatement::where('bank_id', $id)->get();

        return view('backEnd.accounts.bank_transaction', ['bank_transactions' => $bank_transactions, 'bank_name' => $bank_name]);
    }

    public function destroy(Request $request)
    {
        /*
        try {
        */
            $tables = \App\tableList::getTableList('bank_id', $request->id);
            /*
            try {
            */
                if ($tables == null) {
                    $bank_account = SmBankAccount::destroy($request->id);

                    Toastr::success('Operation successful', 'Success');

                    return redirect()->back();
                } else {
                    $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
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

    public function bankAccountDatatable()
    {
        /*
        try {
        */
            $bank_accounts = SmBankAccount::query()->select(['id', 'opening_balance', 'current_balance', 'account_name', 'bank_name', 'note']);
            return DataTables::of($bank_accounts)
                ->addIndexColumn()
                ->addColumn('opening_balance', function ($row) {
                    return currency_format(@$row->opening_balance);
                })
                ->addColumn('current_balance', function ($row) {
                    return currency_format(@$row->current_balance);
                })
                ->addColumn('action', function ($row): string {
                    return '<div class="dropdown CRM_dropdown">
                                <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">'.app('translator')->get('common.select').'</button>
                                        
                                <div class="dropdown-menu dropdown-menu-right">'.
                            (userPermission('bank-transaction') ? '<a class="dropdown-item" href="'.route('bank-transaction', [$row->id]).'">'.__('accounts.transaction').'</a>' : '').

                            (userPermission('bank-account-delete') ? (Config::get('app.app_sync') ? '<span data-toggle="tooltip" title="Disabled For Demo"><a class="dropdown-item" href="#" >'.app('translator')->get('common.disable').'</a></span>' :
                            '<a onclick="deleteBankModal('.$row->id.');"  class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteBankAccountModal" data-id="'.$row->id.'"  >'.app('translator')->get('common.delete').'</a>') : '').
                        '</div>
                            </div>';
                })
                ->rawColumns(['action', 'date'])
                ->make(true);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
