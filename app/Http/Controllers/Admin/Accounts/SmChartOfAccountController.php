<?php

namespace App\Http\Controllers\Admin\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Accounts\SmChartOfAccountRequest;
use App\SmChartOfAccount;
use App\tableList;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmChartOfAccountController extends Controller
{
    public function index()
    {
        /*
        try {
        */
            $chart_of_accounts = SmChartOfAccount::get();

            return view('backEnd.accounts.chart_of_account', ['chart_of_accounts' => $chart_of_accounts]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmChartOfAccountRequest $smChartOfAccountRequest)
    {
        /*
        try {
        */
            $smChartOfAccount = new SmChartOfAccount();
            $smChartOfAccount->head = $smChartOfAccountRequest->head;
            $smChartOfAccount->type = $smChartOfAccountRequest->type;
            $smChartOfAccount->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smChartOfAccount->un_academic_id = getAcademicId();
            } else {
                $smChartOfAccount->academic_id = getAcademicId();
            }

            $smChartOfAccount->save();

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
            $chart_of_account = SmChartOfAccount::find($id);
            $chart_of_accounts = SmChartOfAccount::get();

            return view('backEnd.accounts.chart_of_account', ['chart_of_account' => $chart_of_account, 'chart_of_accounts' => $chart_of_accounts]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmChartOfAccountRequest $smChartOfAccountRequest, $id)
    {
        /*
        try {
        */
            $chart_of_account = SmChartOfAccount::find($smChartOfAccountRequest->id);
            $chart_of_account->head = $smChartOfAccountRequest->head;
            $chart_of_account->type = $smChartOfAccountRequest->type;
            if (moduleStatusCheck('University')) {
                $chart_of_account->un_academic_id = getAcademicId();
            }

            $chart_of_account->save();

            Toastr::success('Operation successful', 'Success');

            return redirect()->route('chart-of-account');
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
            $tables1 = tableList::getTableList('income_head_id', $id);
            $tables2 = tableList::getTableList('expense_head_id', $id);
            /*
            try {
            */
                if ($tables1 == null && $tables2 == null) {
                    $chart_of_account = SmChartOfAccount::destroy($id);

                    Toastr::success('Operation successful', 'Success');

                    return redirect()->back();
                }else{
                    $msg = 'This data already used in  : ' . $tables1 .' '. $tables2 .' Please remove those data first';
                    Toastr::error($msg, 'Failed');
                    return redirect()->back();
                }
            /*
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : '.$tables1.' '.$tables2.' Please remove those data first';
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
