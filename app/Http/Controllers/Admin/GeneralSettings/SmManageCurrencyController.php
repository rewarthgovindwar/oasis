<?php

namespace App\Http\Controllers\Admin\GeneralSettings;

use Exception;
use App\SmCurrency;
use App\SmGeneralSettings;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\GeneralSettings\SmCurrencyRequest;

class SmManageCurrencyController extends Controller
{
    // manage currency
    public function manageCurrency()
    {
        /* try { */
        $currencies = SmCurrency::with('active')->whereIn('school_id', [1, Auth::user()->school_id])->get();

        return view('backEnd.systemSettings.manageCurrency', ['currencies' => $currencies]);
        /*
        } catch (Exception $exception) {
          Toastr::error('Operation Failed', 'Failed');

          return redirect()->back();
        }
    */
    }

    public function create()
    {
        return view('backEnd.systemSettings.create_update_currency');
    }

    public function storeCurrency(SmCurrencyRequest $smCurrencyRequest)
    {
        /*try {
          */
        $smCurrency = new SmCurrency();
        $smCurrency->name = $smCurrencyRequest->name;
        $smCurrency->code = $smCurrencyRequest->code;
        $smCurrency->symbol = $smCurrencyRequest->symbol;
        $smCurrency->currency_type = $smCurrencyRequest->currency_type;
        $smCurrency->currency_position = $smCurrencyRequest->currency_position;
        $smCurrency->space = $smCurrencyRequest->space;
        $smCurrency->decimal_digit = $smCurrencyRequest->decimal_digit;
        $smCurrency->decimal_separator = $smCurrencyRequest->decimal_separator;
        $smCurrency->thousand_separator = $smCurrencyRequest->thousand_separator;
        $smCurrency->school_id = Auth::user()->school_id;
        $smCurrency->save();
        Toastr::success('Operation successful', 'Success');

        return redirect('manage-currency'); /*
        } catch (Exception $exception) {
            return $exception->getMessage();
          }
        */
    }

    public function storeCurrencyUpdate(SmCurrencyRequest $smCurrencyRequest)
    {
        /*
          try {
          */
        $s = SmCurrency::findOrFail($smCurrencyRequest->id);
        $s->name = $smCurrencyRequest->name;
        $s->code = $smCurrencyRequest->code;
        $s->symbol = $smCurrencyRequest->symbol;
        $s->currency_type = $smCurrencyRequest->currency_type;
        $s->currency_position = $smCurrencyRequest->currency_position;
        $s->space = $smCurrencyRequest->space;
        $s->decimal_digit = $smCurrencyRequest->decimal_digit;
        $s->decimal_separator = $smCurrencyRequest->decimal_separator;
        $s->thousand_separator = $smCurrencyRequest->thousand_separator;
        $s->school_id = Auth::user()->school_id;
        $s->update();

        Toastr::success('Operation successful', 'Success');

        return redirect('manage-currency');

        /*
          } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect('manage-currency');}
        */
    }

    public function manageCurrencyEdit($id)
    {
        // if (config('app.app_sync') == true) {
        //     Toastr::error('Disabled for demo mode', 'Failed');
        //     return redirect()->route('manage-currency');
        // }
        /*
        try {
        */
        $currencies = SmCurrency::whereOr(['school_id', Auth::user()->school_id], ['school_id', 1])->get();
        $editData = SmCurrency::where('id', $id)->first();

        return view('backEnd.systemSettings.create_update_currency', ['editData' => $editData, 'currencies' => $currencies]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect('manage-currency');
        }
    */
    }

    public function manageCurrencyDelete($id)
    {
        // if (config('app.app_sync') == true) {
        //     Toastr::error('Disabled for demo mode', 'Failed');
        //     return redirect()->route('manage-currency');
        // }
        /*
        try {
        */
        $current_currency = SmGeneralSettings::where('school_id', Auth::user()->school_id)->where('currency', @schoolConfig()->currency)->where('currency_symbol', @schoolConfig()->currency_symbol)->first();
        $del_currency = SmCurrency::findOrfail($id);

        if (! empty($current_currency) && $current_currency->currency == $del_currency->code && $current_currency->currency_symbol == $del_currency->symbol) {
            Toastr::warning('You cannot delete current currency', 'Warning');

            return redirect()->back();
        }

        $currency = SmCurrency::findOrfail($id);
        $currency->delete();
        Toastr::success('Operation successful', 'Success');

        return redirect()->back();

        /*
        } catch (Exception $exception) {

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    */
    }

    public function manageCurrencyActive(int $id)
    {
        if (config('app.app_sync') == true) {
            Toastr::error('Disabled for demo mode', 'Failed');

            return redirect()->route('manage-currency');
        }
        /*
        try {
        */
        $currency = SmCurrency::findOrFail($id);

        $systemSettings = generalSetting();
        $systemSettings->currency = $currency->code;
        $systemSettings->currency_symbol = $currency->symbol;
        $systemSettings->save();

        if ($systemSettings) {
            session()->forget('generalSetting');
            session()->put('generalSetting', $systemSettings);
        }

        Toastr::success('Operation successful', 'Success');

        return redirect('manage-currency');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect('manage-currency');
        }
    */
    }

    public function systemDestroyedByAuthorized()
    {
        /*try {
          */
        return view('backEnd.systemSettings.manageCurrency', ['editData' => $editData, 'currencies' => $currencies]);
        /*
          } catch (Exception $exception) {

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    */
    }
}
