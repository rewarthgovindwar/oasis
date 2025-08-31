<?php

namespace App\Http\Controllers;

use App\Models\DirectFeesInstallmentAssign;
use App\Models\DireFeesInstallmentChildPayment;
use App\SmFeesAssignDiscount;
use App\SmFeesPayment;
use App\SmGeneralSettings;
use App\SmPaymentGatewaySetting;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Modules\University\Entities\UnFeesInstallmentAssign;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use Stripe;

class SmCollectFeesByPaymentGateway extends Controller
{
    public $_api_context;

    public function collectFeesByGateway($amount, $student_id, $type)
    {
            $amount = $amount;
            $fees_type_id = $type;
            $discounts = SmFeesAssignDiscount::where('student_id', $student_id)->get();

            $applied_discount = [];
            foreach ($discounts as $discount) {
                $fees_payment = SmFeesPayment::select('fees_discount_id')->where('fees_discount_id', $discount->id)->first();
                if (isset($fees_payment->fees_discount_id)) {
                    $applied_discount[] = $fees_payment->fees_discount_id;
                }
            }
            return view('backEnd.feesCollection.collectFeesByGateway', compact('amount', 'discounts', 'fees_type_id', 'student_id', 'applied_discount'));
    }

    public function payByPaypal(Request $request)
    {

            $real_amount = $request->real_amount/100;

            if(moduleStatusCheck('University')){
                $installment = UnFeesInstallmentAssign::find($request->installment_id);
                $description = $installment->installment->title ?? 'Fees Payment';
            } elseif (directFees()) {
                $installment = DirectFeesInstallmentAssign::find($request->installment_id);
                $description = $installment->installment->title ?? 'Fees Payment';
            }

            $user = Auth::user();
            $smFeesPayment = new SmFeesPayment();
            $smFeesPayment->student_id = $request->student_id;
            $smFeesPayment->amount = $real_amount;
            $smFeesPayment->assign_id = $request->assign_id;
            $smFeesPayment->payment_date = date('Y-m-d');
            $smFeesPayment->payment_mode = 'PayPal';
            $smFeesPayment->created_by = $user->id;
            $smFeesPayment->record_id = $request->record_id;
            $smFeesPayment->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smFeesPayment->un_academic_id = getAcademicId();
                $smFeesPayment->un_fees_installment_id = $request->installment_id;
                $smFeesPayment->un_semester_label_id = $request->un_semester_label_id;
            } elseif (directFees()) {
                $smFeesPayment->direct_fees_installment_assign_id = $installment->id;
                $smFeesPayment->academic_id = getAcademicId();
            } else {
                $smFeesPayment->fees_type_id = $request->fees_type_id;
                $smFeesPayment->academic_id = getAcademicId();
            }

            $smFeesPayment->active_status = 0;
            $smFeesPayment->save();

            $data = [];
            $data['payment_method'] = 'PayPal';
            $data['amount'] = $real_amount;
            $data['service_charge'] = chargeAmount('PayPal', $real_amount);
            $data['fees_payment_id'] = $smFeesPayment->id;
            $data['type'] = 'old_fees';
            $classMap = config('paymentGateway.'.$data['payment_method']);
            $make_payment = new $classMap();
            $url = $make_payment->handle($data);
    }

    public function getPaymentStatus(Request $request)
    {

        $paypal_fees_paymentId = Session::get('paypal_fees_paymentId');
        $fees_payment = null;
        $url = route('login');

        if (! is_null($paypal_fees_paymentId)) {
            $fees_payment = SmFeesPayment::find($paypal_fees_paymentId);
        }

        if (auth()->check()) {
            $role_id = auth()->user()->role_id;
            if ($role_id == 3 && $fees_payment) {
                $url = route('parent_fees', $fees_payment->student_id);
            } elseif ($role_id == 2) {
                $url = route('student_fees');
            } else {
                $url = route('dashboard');
            }
        }

            $payment_id = Session::get('paypal_payment_id');
            Session::forget('paypal_payment_id');
            if (empty($request->input('PayerID')) || empty($request->input('token'))) {
                \Session::put('error', 'Payment failed');

                return redirect($url);
            }

            $payment = Payment::get($payment_id, $this->_api_context);

            $paymentExecution = new PaymentExecution();
            $paymentExecution->setPayerId($request->input('PayerID'));
            $result = $payment->execute($paymentExecution, $this->_api_context);

            if ($result->getState() == 'approved' && $fees_payment) {
                $fees_payment->active_status = 1;
                $fees_payment->save();
                if (moduleStatusCheck('University')) {
                    $installment = UnFeesInstallmentAssign::find($fees_payment->un_fees_installment_id);
                    $installment->paid_amount = discountFeesAmount($installment->id);
                    $installment->active_status = 1;
                    $installment->payment_mode = 'Paypal';
                    $installment->payment_date = $fees_payment->payment_date;
                    $installment->save();
                    Session::put('success', 'Payment success');
                    Toastr::success('Operation successful', 'Success');
                }
            } elseif (directFees()) {

                DirectFeesInstallmentAssign::find(Session::get('installment_id'));
                $installment = DirectFeesInstallmentAssign::find(Session::get('installment_id'));

                $installment->paid_amount = discountFees($installment->id);
                $installment->active_status = 1;
                $installment->payment_mode = $fees_payment->payment_mode;
                $installment->payment_date = $fees_payment->payment_date;
                $installment->save();

                $payable_amount = discountFees($installment->id);
                $sub_payment = $installment->payments->sum('paid_amount');
                $direct_payment = $installment->paid_amount;
                $total_paid = $sub_payment + $direct_payment;

                $last_inovoice = DireFeesInstallmentChildPayment::where('school_id', auth()->user()->school_id)->max('invoice_no');
                $direFeesInstallmentChildPayment = new DireFeesInstallmentChildPayment();
                $direFeesInstallmentChildPayment->direct_fees_installment_assign_id = $installment->id;
                $direFeesInstallmentChildPayment->invoice_no = ($last_inovoice + 1) ?? 1;
                $direFeesInstallmentChildPayment->direct_fees_installment_assign_id = $installment->id;
                $direFeesInstallmentChildPayment->amount = $installment->paid_amount;
                $direFeesInstallmentChildPayment->paid_amount = $installment->paid_amount;
                $direFeesInstallmentChildPayment->payment_date = $fees_payment->payment_date;
                $direFeesInstallmentChildPayment->payment_mode = $fees_payment->payment_mode;
                $direFeesInstallmentChildPayment->note = $fees_payment->note;
                $direFeesInstallmentChildPayment->slip = $fees_payment->slip;
                $direFeesInstallmentChildPayment->active_status = 1;
                $direFeesInstallmentChildPayment->discount_amount = 0;
                $direFeesInstallmentChildPayment->fees_type_id = $installment->fees_type_id;
                $direFeesInstallmentChildPayment->student_id = $fees_payment->student_id;
                $direFeesInstallmentChildPayment->record_id = $fees_payment->record_id;

                $direFeesInstallmentChildPayment->created_by = Auth::user()->id;
                $direFeesInstallmentChildPayment->updated_by = Auth::user()->id;
                $direFeesInstallmentChildPayment->school_id = Auth::user()->school_id;
                $direFeesInstallmentChildPayment->balance_amount = ($payable_amount - ($sub_payment + $request->amount));
                $direFeesInstallmentChildPayment->save();

                Session::put('success', 'Payment success');
                Toastr::success('Operation successful', 'Success');
            } else {
                Toastr::error('Operation Failed', 'Failed');
            }

            return redirect($url);
    }

    public function collectFeesStripe($amount, $student_id, $type)
    {
            $amount = $amount;
            $fees_type_id = $type;
            $discounts = SmFeesAssignDiscount::where('student_id', $student_id)->get();
            $stripe_publisher_key = SmPaymentGatewaySetting::where('gateway_name', '=', 'Stripe')->first()->stripe_publisher_key;

            $applied_discount = SmFeesPayment::select('fees_discount_id')->whereIn('fees_discount_id', $discounts->pluck('id')->toArray())->pluck('fees_discount_id')->toArray();

            return view('backEnd.feesCollection.collectFeesStripeView', compact('amount', 'discounts', 'fees_type_id', 'student_id', 'applied_discount', 'stripe_publisher_key'));
    }

    public function stripeStore(Request $request)
    {
            $system_currency = '';
            $currency_details = SmGeneralSettings::select('currency')->where('id', 1)->first();
            if (isset($currency_details)) {
                $system_currency = $currency_details->currency;
            }

            $stripeDetails = SmPaymentGatewaySetting::select('stripe_api_secret_key', 'stripe_publisher_key')->where('gateway_name', '=', 'Stripe')->first();

            Stripe\Stripe::setApiKey($stripeDetails->stripe_api_secret_key);
            $charge = Stripe\Charge::create([
                'amount' => $real_amount * 100,
                'currency' => $system_currency,
                'source' => $request->stripeToken,
                'description' => 'Student Fees payment',
            ]);
            if ($charge) {
                $user = Auth::user();
                $smFeesPayment = new SmFeesPayment();
                $smFeesPayment->student_id = $request->student_id;
                $smFeesPayment->fees_type_id = $request->fees_type_id;
                $smFeesPayment->amount = $real_amount;
                $smFeesPayment->payment_date = date('Y-m-d');
                $smFeesPayment->payment_mode = 'Stripe';
                $smFeesPayment->created_by = $user->id;
                $smFeesPayment->school_id = Auth::user()->school_id;
                $smFeesPayment->save();

                Toastr::success('Operation successful', 'Success');

                return redirect('student-fees');

            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect('student-fees');


    }
}
