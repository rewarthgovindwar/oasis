<?php

namespace App\Http\Controllers;

use App\ApiBaseMethod;
use App\SmPaymentGatewaySetting;
use App\SmPaymentMethhod;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SmPaymentMethodController extends Controller
{

    public function index(Request $request)
    {

        try {
            $payment_methods = SmPaymentMethhod::where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.accounts.payment_method', ['payment_methods' => $payment_methods]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'method' => 'required',
        ]);

        $is_duplicate = SmPaymentMethhod::where('method', $request->method)->first();
        if ($is_duplicate) {
            Toastr::error('Duplicate name found!', 'Failed');

            return redirect()->back()->withErrors($validator)->withInput();
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
            $smPaymentMethhod = new SmPaymentMethhod();
            $smPaymentMethhod->method = $request->method;
            $smPaymentMethhod->school_id = Auth::user()->school_id;
            $result = $smPaymentMethhod->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {

                    return ApiBaseMethod::sendResponse(null, 'Method has been created successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect()->back();
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function edit(Request $request, $id)
    {

        try {

            $statement = 'SELECT P.id as PID, D.id as DID, P.active_status as IsActive, P.method, D.* FROM sm_payment_methhods as P, sm_payment_gateway_settings D WHERE P.gateway_id=D.id';

            $PaymentMethods = DB::select($statement);
            $paymeny_gateway = SmPaymentMethhod::where('school_id', Auth::user()->school_id)->get();
            $paymeny_gateway_settings = SmPaymentGatewaySetting::where('school_id', Auth::user()->school_id)->get();
            $payment_methods = SmPaymentMethhod::where('school_id', Auth::user()->school_id)->get();
            $payment_method = SmPaymentMethhod::find($id);
            $payment_methods = SmPaymentMethhod::all();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['payment_method'] = $payment_method->toArray();
                $data['payment_methods'] = $payment_methods->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.systemSettings.paymentMethodSettings', ['payment_method' => $payment_method, 'payment_methods' => $payment_methods, 'PaymentMethods' => $PaymentMethods, 'paymeny_gateway' => $paymeny_gateway, 'paymeny_gateway_settings' => $paymeny_gateway_settings]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function update(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'method' => 'required',
        ]);

        $is_duplicate = SmPaymentMethhod::where('id', '!=', $request->id)->where('method', $request->method)->first();
        if ($is_duplicate) {
            Toastr::error('Duplicate name found!', 'Failed');

            return redirect()->back()->withErrors($validator)->withInput();
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
            $payment_method = SmPaymentMethhod::find($request->id);
            $payment_method->method = $request->method;
            $result = $payment_method->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Method has been updated successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect('payment-method-settings');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function delete1(Request $request, $id)
    {

        try {
            $student_group = SmPaymentMethhod::destroy($id);

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($student_group) {
                    return ApiBaseMethod::sendResponse(null, 'Method has been deleted successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

            if ($student_group) {
                Toastr::success('Operation successful', 'Success');

                return redirect()->back();
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $tables = \App\tableList::getTableList('payment_method_id', $id);
            try {
                $payment_method = SmPaymentMethhod::destroy($id);
                if ($payment_method) {
                    if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                        if ($payment_method) {
                            return ApiBaseMethod::sendResponse(null, 'Method has been deleted successfully');
                        }

                        return ApiBaseMethod::sendError('Something went wrong, please try again');

                    }

                    if ($payment_method) {
                        Toastr::success('Operation successful', 'Success');

                        return redirect()->route('payment-method-settings');
                    }

                    Toastr::error('Operation Failed', 'Failed');

                    return redirect()->back();

                }

                Toastr::error('Operation Failed', 'Failed');

                return redirect()->back();

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
    }
}
