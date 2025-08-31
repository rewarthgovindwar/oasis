<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\SmAcademicYear;
use App\SmAddIncome;
use App\SmFeesPayment;
use App\SmGeneralSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmPaymentGatewayController extends Controller
{
    public function dataSave(Request $request)
    {

        $smFeesPayment = new SmFeesPayment();
        $smFeesPayment->student_id = $request->student_id;
        $smFeesPayment->fees_type_id = $request->fees_type_id;
        $smFeesPayment->discount_amount = 0;
        $smFeesPayment->fine = 0;
        $smFeesPayment->amount = $request->amount;
        $smFeesPayment->assign_id = $request->assign_id;
        $smFeesPayment->payment_date = date('Y-m-d', strtotime(date('Y-m-d')));
        $smFeesPayment->payment_mode = $request->method;
        $smFeesPayment->school_id = $request->school_id;
        $smFeesPayment->academic_id = SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();

        $result = $smFeesPayment->save();
        if ($result) {
            return response()->json(['payment_ref' => $smFeesPayment->id], 200);
        }

        return null;
    }

    public function successCallback(Request $request)
    {

        if ($request->payment_ref && $request->status) {
            $fees_payment = SmFeesPayment::find($request->payment_ref);
            if ($fees_payment) {
                $fees_payment->active_status = 1;
                $fees_payment->save();

                $gs = SmGeneralSettings::first('income_head_id');

                $smAddIncome = new SmAddIncome();
                $smAddIncome->name = 'Fees Collect';
                $smAddIncome->date = date('Y-m-d', strtotime(date('Y-m-d')));
                $smAddIncome->amount = $fees_payment->amount;
                $smAddIncome->fees_collection_id = $fees_payment->id;
                $smAddIncome->active_status = 1;
                $smAddIncome->income_head_id = $gs->income_head_id;
                $smAddIncome->payment_method_id = 4;
                $smAddIncome->created_by = Auth()->user()->id;
                $smAddIncome->school_id = Auth::user()->school_id;
                $smAddIncome->academic_id = SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
                $smAddIncome->save();

                return response()->json(['message' => 'Payment successfully completed'], 200);
            }
        }

        return null;

    }
}
