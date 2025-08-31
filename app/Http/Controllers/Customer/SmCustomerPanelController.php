<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\SmProductPurchase;
use App\SmStaff;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Support\Facades\Auth;

class SmCustomerPanelController extends Controller
{


    public function customerDashboard()
    {
        $id = Auth::user()->id;
        $staffDetails = SmStaff::where('user_id', $id)->get();
        /*
        try {
        */
            return view('backEnd.customerPanel.customer_dashboard', ['staffDetails' => $staffDetails]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function customerPurchases()
    {
        /*
        try {
        */
            $id = Auth::user()->id;
            $customerDetails = SmStaff::where('user_id', $id)->get();
            $ProductPurchase = SmProductPurchase::where('user_id', $id)->get();

            return view('backEnd.customerPanel.customer_purchase', ['customerDetails' => $customerDetails, 'ProductPurchase' => $ProductPurchase]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
