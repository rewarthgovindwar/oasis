<?php

namespace App\PaymentGateway;

use App\SmFeesPayment;
use App\SmParent;
use App\SmPaymentGatewaySetting;
use App\SmStudent;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Modules\Fees\Entities\FmFeesTransaction;
use Modules\Fees\Http\Controllers\FeesExtendedController;
use Modules\Wallet\Entities\WalletTransaction;
use Xendit\Xendit;

class XenditPayment
{
    public function handle(array $data)
    {
        
        try {
            $xendit_config = SmPaymentGatewaySetting::where('gateway_name', 'Xendit')
                ->where('school_id', auth()->user()->school_id)
                ->first('gateway_secret_key');

            if (! $xendit_config || ! $xendit_config->gateway_secret_key) {
                Toastr::warning('Xendit Credentials Can Not Be Blank', 'Warning');

                return redirect()->send()->back();
            }

            if ($xendit_config) {
                if ($data['type'] == 'Wallet') {
                    $amount = $data['amount'];
                    if (array_key_exists('service_charge', $data)) {
                        $amount = $data['amount'] + $data['service_charge'];
                    }

                    $student = SmStudent::where('user_id', $data['user_id'])->first();
                    if (! ($student->email)) {
                        $parent = SmParent::find($student->parent_id);
                        $email = $parent->guardians_email;
                    } else {
                        $email = $student->email;
                    }

                    Xendit::setApiKey($xendit_config->gateway_secret_key);
                    $params = [
                        'external_id' => $data['wallet_type'],
                        'payer_email' => $email,
                        'description' => 'Wallet_Diposit',
                        'amount' => $amount * 1000,
                        'success_redirect_url' => url('payment_gateway_success_callback/Xendit'),
                        'failure_redirect_url' => url('payment_gateway_cancel_callback/Xendit'),
                    ];

                    $createInvoice = \Xendit\Invoice::create($params);
                    if ($createInvoice && $createInvoice['status'] == 'PENDING') {
                        $user = auth()->user();
                        $walletTransaction = new WalletTransaction();
                        $walletTransaction->amount = $data['amount'];
                        $walletTransaction->payment_method = 'Xendit';
                        $walletTransaction->user_id = $user->id;
                        $walletTransaction->type = $data['wallet_type'];
                        $walletTransaction->school_id = auth()->user()->school_id;
                        $walletTransaction->academic_id = getAcademicId();
                        $walletTransaction->status = 'pending';
                        $walletTransaction->save();

                        Session::put('type', $data['type']);
                        Session::put('wallet_payment_id', $walletTransaction->id);
                        Session::put('user_id', $data['user_id']);
                        Session::put('amount', $data['amount']);
                        Session::put('payment_method', $data['payment_method']);

                        return $createInvoice['invoice_url'];
                    }
                } elseif ($data['type'] == 'Fees') {
                    $email = '';
                    $student = SmStudent::find($data['student_id']);
                    if (! ($student->email)) {
                        $parent = SmParent::find($student->parent_id);
                        $email = $parent->guardians_email;
                    } else {
                        $email = $student->email;
                    }

                    Xendit::setApiKey($xendit_config->gateway_secret_key);
                    $params = [
                        'external_id' => $data['type'],
                        'payer_email' => $email,
                        'description' => 'Fees_Payment',
                        'amount' => $data['amount'] * 1000,
                        'success_redirect_url' => url('payment_gateway_success_callback/Xendit'),
                        'failure_redirect_url' => url('payment_gateway_cancel_callback/Xendit'),
                    ];

                    $createInvoice = \Xendit\Invoice::create($params);
                    if ($createInvoice && $createInvoice['status'] == 'PENDING') {
                        Session::put('type', $data['type']);
                        Session::put('xendit_payment_id', $data['transcationId']);
                        Session::put('payment_method', $data['payment_method']);
                        Session::put('amount', $data['amount']);

                        return $createInvoice['invoice_url'];
                    }
                } elseif ($data['type'] == 'Lms') {
                    $student = SmStudent::where('user_id', $data['user_id'])->first();
                    if (! ($student->email)) {
                        $parent = SmParent::find($student->parent_id);
                        $email = $parent->guardians_email;
                    } else {
                        $email = $student->email;
                    }

                    Xendit::setApiKey($xendit_config->gateway_secret_key);
                    $params = [
                        'external_id' => $data['type'],
                        'payer_email' => $email,
                        'description' => 'Lms_Fees_Payment',
                        'amount' => $data['amount'] * 1000,
                        'success_redirect_url' => url('payment_gateway_success_callback/Xendit'),
                        'failure_redirect_url' => url('payment_gateway_cancel_callback/Xendit'),
                    ];

                    $createInvoice = \Xendit\Invoice::create($params);
                    if ($createInvoice && $createInvoice['status'] == 'PENDING') {
                        Session::put('student_id', $data['student_id']);
                        Session::put('type', $data['type']);
                        Session::put('purchase_log_id', $data['purchase_log_id']);
                        Session::put('payment_method', $data['payment_method']);
                        Session::put('amount', $data['amount']);

                        return $createInvoice['invoice_url'];
                    }
                }
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->send()->back();
        }

        return null;
    }

    public function successCallBack()
    {
        try {
            if (Session::get('type') == 'Wallet') {
                $user = User::find(Session::get('user_id'));
                $currentBalance = $user->wallet_balance;
                $user->wallet_balance = $currentBalance + Session::get('amount');
                $user->update();

                $addPayment = WalletTransaction::find(Session::get('wallet_payment_id'));
                $addPayment->status = 'approve';
                $addPayment->update();

                $gs = generalSetting();
                $compact['full_name'] = $user->full_name;
                $compact['method'] = Session::get('payment_method');
                $compact['create_date'] = date('Y-m-d');
                $compact['school_name'] = $gs->school_name;
                $compact['current_balance'] = $user->wallet_balance;
                $compact['add_balance'] = Session::get('amount');
                @send_mail($user->email, $user->full_name, 'wallet_approve', $compact);

                Session::forget('type');
                Session::forget('wallet_payment_id');
                Session::forget('user_id');
                Session::forget('amount');
                Session::forget('payment_method');

                return redirect()->route('wallet.my-wallet');

            }

            if (Session::get('type') == 'Fees') {
                $payment_id = Session::get('xendit_payment_id');
                if ($payment_id) {
                    $transcation = FmFeesTransaction::find(Session::get('xendit_payment_id'));

                    $feesExtendedController = new FeesExtendedController();
                    $feesExtendedController->addFeesAmount(Session::get('xendit_payment_id'), null);

                    Session::forget('type');
                    Session::forget('xendit_payment_id');
                    Session::forget('amount');
                    Session::forget('payment_method');
                }

                Toastr::success('Operation successful', 'Success');

                return redirect()->to(url('fees/student-fees-list', $transcation->student_id));
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->send()->back();
        }

        return null;
    }

    public function cancelCallback()
    {

        try {
            $payment_id = Session::get('xendit_payment_id');
            if ($payment_id) {
                $success_payment = SmFeesPayment::find($payment_id);
                $success_payment->delete();
                Session::forget('xendit_payment_id');
                if (auth()->user()->role_id == 2) {
                    Toastr::error('Payment failed', 'Failed');

                    return redirect('student-fees');

                }

                if (auth()->user()->role_id == 3) {
                    Toastr::error('Payment failed', 'Failed');

                    return redirect('parent-fees', $success_payment->student_id);
                }
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->send()->back();
        }

        return null;
    }
}
