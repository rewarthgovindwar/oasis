<?php

namespace App\PaymentGateway;

use App\Models\DirectFeesInstallmentAssign;
use App\Models\DireFeesInstallmentChildPayment;
use App\Models\StudentRecord;
use App\SmAddIncome;
use App\SmFeesPayment;
use App\SmPaymentGatewaySetting;
use App\SmPaymentMethhod;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ixudra\Curl\Facades\Curl;
use Modules\Fees\Entities\FmFeesTransaction;
use Modules\Fees\Http\Controllers\FeesExtendedController;
use Modules\Wallet\Entities\WalletTransaction;

class PhonePay
{
    private $phonePe;

    private $submit_url;

    private $check_url;

    public function __construct()
    {
        $this->phonePe = SmPaymentGatewaySetting::where('gateway_name', 'PhonePe')->where('school_id', app('school')->id)->first(['phone_pay_merchant_id', 'phone_pay_salt_key', 'phone_pay_salt_index', 'gateway_mode']);
        if ($this->phonePe->gateway_mode === 'live') {
            $this->submit_url = 'https://api.phonepe.com/apis/hermes/pg/v1/pay';
            $this->check_url = 'https://api.phonepe.com/apis/hermes/pg/v1/status/';
        } else {
            $this->submit_url = 'https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1/pay';
            $this->check_url = 'https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1/status/';
        }
    }

    public function handle(array $data)
    {
        try {
            $phonePe = $this->phonePe;
            if ($phonePe) {
                $amoutnWithService = $data['amount'];
                if (array_key_exists('service_charge', $data)) {
                    $amoutnWithService = $data['amount'] + $data['service_charge'];
                }

                $role_id = auth()->user()->role_id;
                $user = User::find($data['user_id']);
                if ($data['type'] === 'Wallet') {
                    $redirect_url = route('wallet.my-wallet');
                    $walletTransaction = new WalletTransaction();
                    $walletTransaction->amount = $data['amount'];
                    $walletTransaction->payment_method = 'PhonePe';
                    $walletTransaction->user_id = $data['user_id'];
                    $walletTransaction->type = $data['wallet_type'];
                    $walletTransaction->school_id = auth()->user()->school_id;
                    $walletTransaction->academic_id = getAcademicId();
                    $walletTransaction->save();
                    $merchantTransactionId = 'Wallet-'.$walletTransaction->id;
                } elseif ($data['type'] === 'Fees') {
                    $merchantTransactionId = 'Fees-'.$data['transcationId'];
                } elseif ($data['type'] === 'direct_fees_total') {
                    $merchantTransactionId = 'direct_fees_total-'.$data['record_id'].'-'.$data['request_amount'].'-'.$role_id;
                } elseif ($data['type'] === 'direct_fees') {
                    $merchantTransactionId = 'direct_fees-'.$data['sub_payment_id'].'-'.$data['installment_id'].'-'.$role_id;
                } elseif ($data['type'] === 'old_fees') {
                    $source = array_key_exists('source', $data) ? $data['source'] : 'Web';
                    $merchantTransactionId = 'old_fees-'.$data['payment_id'].'-'.$data['assign_id'].'-'.$role_id.'-'.$source;
                }

                $payload = [
                    'merchantId' => $phonePe->phone_pay_merchant_id,
                    'merchantTransactionId' => $merchantTransactionId,
                    'merchantUserId' => $data['user_id'],
                    'amount' => $amoutnWithService * 100,
                    'redirectUrl' => route('payment.success', 'PhonePe'),
                    'redirectMode' => 'POST',
                    'callbackUrl' => route('payment.cancel', 'PhonePe'),
                    'mobileNumber' => @$user->phone_number,
                    'paymentInstrument' => [
                        'type' => 'PAY_PAGE',
                    ],
                ];

                $encode = base64_encode(json_encode($payload));
                $saltKey = $phonePe->phone_pay_salt_key;
                $saltIndex = $phonePe->phone_pay_salt_index;
                $string = $encode.'/pg/v1/pay'.$saltKey;
                $sha256 = hash('sha256', $string);
                $xHeader = $sha256.'###'.$saltIndex;
                $response = Curl::to($this->submit_url)
                    ->withHeader('Content-Type:application/json')
                    ->withHeader('X-VERIFY:'.$xHeader)
                    ->withData(json_encode(['request' => $encode]))
                    ->post();

                $res = json_decode($response);

                $redirect_url = $res->data->instrumentResponse->redirectInfo->url;

                if (request()->wantsJson()) {
                    return $redirect_url;
                }

                return redirect()->to($redirect_url)->send();

            }
        } catch (Exception $exception) {
            //  Log::info($e->getMessage());
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->send()->back();
        }

        return null;
    }

    public function successCallBack()
    {

        try {
            DB::beginTransaction();
            $phonePe = $this->phonePe;
            $input = request()->all();

            $saltKey = $phonePe->phone_pay_salt_key;
            $saltIndex = $phonePe->phone_pay_salt_index;
            $finalXHeader = hash('sha256', '/pg/v1/status/'.$input['merchantId'].'/'.$input['transactionId'].$saltKey).'###'.$saltIndex;
            $response = Curl::to($this->check_url.$input['merchantId'].'/'.$input['transactionId'])
                ->withHeader('Content-Type:application/json')
                ->withHeader('accept:application/json')
                ->withHeader('X-VERIFY:'.$finalXHeader)
                ->withHeader('X-MERCHANT-ID:'.$input['merchantId'])
                ->get();

            $output = json_decode($response);

            $payment_method = SmPaymentMethhod::where('method', 'PhonePe')->where('school_id', app('school')->id)->first();

            if ($output && $output->code === 'PAYMENT_SUCCESS') {
                $trx = $output->data->merchantTransactionId;
                $data = explode('-', $trx);
                $type = $data[0];
                $payment_id = $data[1];

                if ($type === 'Wallet') {
                    $payment = WalletTransaction::find($payment_id);
                    $user = User::find($payment->user_id);
                    $currentBalance = $user->wallet_balance;
                    $user->wallet_balance = $currentBalance + $payment->amount;
                    $user->update();
                    $payment->status = 'approve';
                    $payment->update();
                    $gs = generalSetting();
                    $compact['full_name'] = $user->full_name;
                    $compact['method'] = 'PhonePe';
                    $compact['create_date'] = date('Y-m-d');
                    $compact['school_name'] = $gs->school_name;
                    $compact['current_balance'] = $user->wallet_balance;
                    $compact['add_balance'] = $payment->amount;
                    DB::commit();
                    @send_mail($user->email, $user->full_name, 'wallet_approve', $compact);

                    return redirect()->route('wallet.my-wallet');
                }

                if ($type === 'Fees') {
                    $transcation = FmFeesTransaction::find($payment_id);
                    $feesExtendedController = new FeesExtendedController();
                    $feesExtendedController->addFeesAmount($payment_id, null);
                    DB::commit();
                    Cache::forget('have_due_fees_'.@$transcation->feeStudentInfo->user_id);
                    Toastr::success('Operation successful', 'Success');
                    if (auth()->user()->role_id === 2) {
                        return redirect()->to(url('fees/student-fees-list'))->send();
                    }

                    return redirect()->to(url('fees/student-fees-list', $transcation->student_id))->send();
                }

                if ($type === 'direct_fees_total') {
                    $record_id = $data[1];
                    $request_amount = $data[2];
                    $role_id = $data[3];
                    $record = StudentRecord::find($record_id);
                    $student_id = $record->student_id;
                    $after_paid = $request_amount;
                    $installments = DirectFeesInstallmentAssign::where('record_id', $record_id)->get();
                    $total_paid = $installments->sum('paid_amount');
                    $total_amount = $installments->sum('amount');
                    $total_discount = $installments->sum('discount_amount');
                    $balace_amount = $total_amount - ($total_discount + $total_paid);
                    if ($balace_amount < $request_amount) {
                        Toastr::error('Amount is greater than due', 'Failed');
                        if ($role_id === 2) {
                            return redirect()->to(url('student-fees'))->send();
                        }

                        if ($role_id === 3) {
                            return redirect()->to(url('parent-fees/'.$student_id))->send();
                        }

                    }

                    $newformat = date('Y-m-d');
                    foreach ($installments as $installment) {
                        if ($after_paid <= 0) {
                            break;
                        }

                        $installment_due = $installment->amount - ($installment->discount_amount + $installment->paid_amount);
                        if ($installment_due && $after_paid > 0) {
                            $paid_amount = $installment_due >= $after_paid ? $after_paid : $installment_due;

                            $fees_payment = new SmFeesPayment();
                            $fees_payment->student_id = $installment->student_id;
                            $fees_payment->fees_discount_id = null;
                            $fees_payment->discount_amount = 0;
                            $fees_payment->amount = $paid_amount;
                            $fees_payment->payment_date = date('Y-m-d');
                            $fees_payment->payment_mode = 'PhonePe';
                            $fees_payment->created_by = Auth::id();
                            $fees_payment->school_id = app('school')->id;
                            $fees_payment->record_id = $installment->record_id;
                            $fees_payment->academic_id = getAcademicid();
                            $fees_payment->direct_fees_installment_assign_id = $installment->id;

                            $payment_mode_name = 'PhonePe';
                            $payment_method = SmPaymentMethhod::where('method', 'PhonePe')->where('school_id', app('school')->id)->first();
                            $installment = DirectFeesInstallmentAssign::find($installment->id);
                            $installment->payment_date = $newformat;
                            $installment->payment_mode = 'PhonePe';

                            $payable_amount = discountFees($installment->id);
                            $sub_payment = $installment->payments->sum('paid_amount');
                            $last_inovoice = DireFeesInstallmentChildPayment::where('school_id', app('school')->id)->max('invoice_no');

                            $new_subPayment = new DireFeesInstallmentChildPayment();
                            $new_subPayment->direct_fees_installment_assign_id = $installment->id;
                            $new_subPayment->invoice_no = ($last_inovoice + 1) ?? 1;
                            $new_subPayment->amount = $paid_amount;
                            $new_subPayment->paid_amount = $paid_amount;
                            $new_subPayment->payment_date = $newformat;
                            $new_subPayment->payment_mode = 'PhonePe';
                            $new_subPayment->active_status = 1;
                            $new_subPayment->discount_amount = 0;
                            $new_subPayment->fees_type_id = $installment->fees_type_id;
                            $new_subPayment->student_id = $installment->student_id;
                            $new_subPayment->record_id = $installment->record_id;
                            $new_subPayment->school_id = app('school')->id;
                            $new_subPayment->balance_amount = ($payable_amount - ($sub_payment + $paid_amount));
                            $new_subPayment->save();
                            $fees_payment->installment_payment_id = $new_subPayment->id;

                            $installment->active_status = ($sub_payment + $paid_amount) === $payable_amount ? 1 : 2;
                            $installment->paid_amount = $sub_payment + $paid_amount;
                            $installment->save();

                            $income_head = generalSetting();
                            $add_income = new SmAddIncome();
                            $add_income->name = 'Fees Collect';
                            $add_income->date = date('Y-m-d');
                            $add_income->amount = $fees_payment->amount;
                            $add_income->fees_collection_id = $fees_payment->id;
                            $add_income->active_status = 1;
                            $add_income->income_head_id = $income_head->income_head_id;
                            $add_income->payment_method_id = @$payment_method->id;
                            $add_income->school_id = app('school')->id;
                            $add_income->academic_id = getAcademicId();
                            $add_income->save();
                            $after_paid -= ($paid_amount);
                            Cache::forget('have_due_fees_'.@$fees_payment->studentInfo->user_id);
                        }
                    }

                    DB::commit();
                    if ($role_id === 2) {
                        return redirect()->to(url('student-fees'))->send();
                    }

                    return redirect()->to(url('parent-fees/'.$student_id))->send();
                }

                if ($type === 'direct_fees') {
                    $sub_payment_id = $data[1];
                    $installment_id = $data[2];
                    $role_id = $data[3];
                    $sub_payment = DireFeesInstallmentChildPayment::find($sub_payment_id);
                    $installment = DirectFeesInstallmentAssign::find($installment_id);
                    $payable_amount = discountFees($installment->id);
                    $all_sub_payment = $installment->payments->sum('paid_amount');
                    $direct_payment = $installment->paid_amount;
                    $total_paid = $all_sub_payment + $direct_payment;
                    $sub_payment->active_status = 1;
                    $sub_payment->balance_amount = ($payable_amount - ($all_sub_payment + $sub_payment->amount));
                    $result = $sub_payment->save();
                    if ($result && $installment) {
                        $fees_payment = new SmFeesPayment();
                        $fees_payment->student_id = $installment->student_id;
                        $fees_payment->amount = $sub_payment->amount;
                        $fees_payment->payment_date = date('Y-m-d', strtotime($sub_payment->payment_date));
                        $fees_payment->payment_mode = $sub_payment->payment_mode;
                        $fees_payment->school_id = app('school')->id;
                        $fees_payment->record_id = $sub_payment->record_id;
                        $fees_payment->academic_id = getAcademicid();
                        $fees_payment->installment_payment_id = $sub_payment->id;
                        $installment->active_status = ($all_sub_payment + $sub_payment->amount) === $payable_amount ? 1 : 2;
                        $installment->paid_amount = $all_sub_payment + $sub_payment->amount;
                        $installment->save();
                        $fees_payment->save();
                        Cache::forget('have_due_fees_'.@$fees_payment->studentInfo->user_id);
                        DB::commit();
                        Toastr::success('Operation successful', 'Success');
                        if ($role_id === 2) {
                            return redirect()->to(url('student-fees'))->send();
                        }

                        if ($role_id === 3) {
                            return redirect()->to(url('parent-fees/'.$student_id))->send();
                        }
                    }
                } elseif ($type === 'old_fees') {
                    $payment_id = $data[1];
                    $assign_id = $data[2];
                    $role_id = $data[3];
                    $source = $data[4];

                    $fees_payment = SmFeesPayment::find($payment_id);
                    $fees_payment->active_status = 1;
                    $fees_payment->save();
                    $income_head = generalSetting();

                    $add_income = new SmAddIncome();
                    $add_income->name = 'Fees Collect';
                    $add_income->date = date('Y-m-d');
                    $add_income->amount = $fees_payment->amount;
                    $add_income->fees_collection_id = $fees_payment->id;
                    $add_income->active_status = 1;
                    $add_income->income_head_id = $income_head->income_head_id;
                    $add_income->payment_method_id = @$payment_method->id;
                    $add_income->created_by = $fees_payment->created_by;
                    $add_income->school_id = $fees_payment->school_id;
                    $add_income->academic_id = $fees_payment->academic_id;
                    $add_income->save();
                    Cache::forget('have_due_fees_'.@$fees_payment->studentInfo->user_id);
                    DB::commit();
                    Toastr::success('Operation successful', 'Success');
                    if ($source === 'App') {
                        return response()->json('payment-success');
                    }

                    if ($role_id === 2) {
                        return redirect()->to(url('student-fees'))->send();
                    }

                    if ($role_id === 3) {
                        return redirect()->to(url('parent-fees/'.$fees_payment->student_id))->send();
                    }
                }
            }
        } catch (Exception $exception) {
            Log::info($exception);
            Toastr::error('Operation Failed', 'Failed');
            if ($source === 'App') {
                return response()->json('payment-failed');
            }

            if ($role_id === 2) {
                return redirect()->to(url('student-fees'))->send();
            }

            if ($role_id === 3) {
                return redirect()->to(url('parent-fees/'.$student_id))->send();
            }
        }

        return null;
    }
}
