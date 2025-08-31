<?php

namespace App\Http\Controllers;

use App\Envato\Envato;
use App\SmGeneralSettings;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use HP;
use Illuminate\Http\Request;

class VerifyController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $o = Envato::verifyPurchase(HP::set()->purchasecode);
            if (isset($o['item']) && $o['item']['id'] == '23876323' && $o['buyer'] == HP::set()->envatouser) {
                return redirect('/');
            }

            return view('verifycode');

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function storePurchasecode(Request $request, $id)
    {
        try {
            $settings = SmGeneralSettings::find($id);
            $settings->envato_user = $request->envatouser;
            $settings->system_purchase_code = $request->purchasecode;
            $settings->save();

            return redirect('/dashboard/')->with('success', 'Purchase Code Verified');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

    }
}
