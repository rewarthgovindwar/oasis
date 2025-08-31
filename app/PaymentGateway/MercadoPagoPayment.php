<?php

namespace App\PaymentGateway;

use App\SmPaymentGatewaySetting;
use App\SmStudent;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Fees\Entities\FmFeesTransaction;
use Modules\Fees\Http\Controllers\FeesExtendedController;
use Modules\Wallet\Entities\WalletTransaction;
use Modules\Wallet\Http\Controllers\WalletController;

class MercadoPagoPayment
{
    public function handle(array $data)
    {
        try {
            $mercadoPagoDetails = SmPaymentGatewaySetting::where('gateway_name', '=', 'MercadoPago')
                ->where('school_id', auth()->user()->school_id)
                ->select('mercado_pago_public_key', 'mercado_pago_acces_token')
                ->first();

            if (! $mercadoPagoDetails || ! $mercadoPagoDetails->mercado_pago_acces_token) {
                Toastr::warning('MercadoPago Credentials Can Not Be Blank', 'Warning');

                return redirect()->send()->back();
            }

            $token = 'TEST-782092712530435-041309-66de5c4bccd06a8c39d793428231d1fc-166366152';
            \MercadoPago\SDK::setAccessToken('TEST-782092712530435-041309-66de5c4bccd06a8c39d793428231d1fc-166366152');

            $amount = $data['amount'];
            if (array_key_exists('service_charge', $data)) {
                $amount = $data['amount'] + $data['service_charge'];
            }

            $payment = new \MercadoPago\Payment();
            $payment->transaction_amount = (float) $amount;
            $payment->token = $data['token'];
            $payment->description = $data['description'];
            $payment->installments = $data['installments'];
            $payment->payment_method_id = $data['payment_method_id'];
            $payment->issuer_id = (int) $data['issuer_id'];

            $payer = new \MercadoPago\Payer();
            $payer->email = $data['payer']['email'];
            $payment->payer = $payer;
            $p = $payment->save();

            if ($p && $payment->status === 'approved') {
                if ($data['type'] === 'Fees') {
                    $paymentResponse = [
                        'type' => $data['type'],
                        'trxID' => $data['traxId'],
                        'amount' => (float) $amount,
                        'studentId' => $data['studentId'],
                    ];
                } elseif ($data['type'] === 'Wallet') {
                    $paymentResponse = [
                        'type' => $data['type'],
                        'trxID' => $data['traxId'],
                        'amount' => (float) $amount,
                        'userId' => $data['userId'],
                    ];
                }

                return $this->successCallback($paymentResponse);
            }

            return $this->fail($data['type'], $data['studentId']);

        } catch (Exception $exception) {
            return response()->json(['message' => $exception]);
        }
    }

    public function successCallback(array $paymentResponse)
    {
        try {
            if ($paymentResponse['type'] === 'Fees') {
                $transcationInfo = FmFeesTransaction::find($paymentResponse['trxID']);
                $feesExtendedController = new FeesExtendedController();
                $feesExtendedController->addFeesAmount($paymentResponse['trxID'], $paymentResponse['amount']);
                $student = SmStudent::with('parents')->find($transcationInfo->student_id);

                sendNotification('Mercado Payment Done', null, 1, 1);
                sendNotification('Mercado Payment Done', null, $student->user_id, 2);
                sendNotification('Mercado Payment Done', null, $student->parents->user_id, 3);
                Cache::forget('have_due_fees_'.@$student->user_id);
                Toastr::success('Operation successful', 'Success');

                return response()->json(['target_url' => route('fees.student-fees-list', $transcationInfo->student_id)]);

            }

            if ($paymentResponse['type'] === 'Wallet') {
                $user = User::find($paymentResponse['userId']);
                $currentamount = $user->wallet_balance;
                $addedAmount = $currentamount + $paymentResponse['amount'];
                $user->wallet_balance = $addedAmount;
                $user->update();

                $status = WalletTransaction::find($paymentResponse['trxID']);
                $status->status = 'approve';
                $status->updated_at = date('Y-m-d');
                $status->update();

                $compact['user_email'] = $user->email;
                $compact['method'] = $status->payment_method;
                $compact['create_date'] = $status->created_by;
                $compact['current_balance'] = $user->wallet_balance;
                $compact['add_balance'] = $paymentResponse['amount'];

                @send_mail($user->email, $user->full_name, 'wallet_approve', $compact);
                $walletController = new WalletController();
                $walletController->sendNotification($user->id, $user->role_id, 'Wallet Approve');

                Toastr::success('Operation successful', 'Success');

                return response()->json(['target_url' => url('wallet/my-wallet')]);
            }

            return response()->json(['message' => 'Error']);

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            Toastr::error('Transaction is Invalid');

            return response()->json(['target_url' => route('fees.student-fees-list', $paymentResponse['studentId'])]);
        }
    }

    public function fail($type, $studentId)
    {
        if ($type === 'Fees') {
            Toastr::error('Transaction is Invalid');

            return response()->json(['target_url' => route('fees.student-fees-list', $studentId)]);
        }

        return null;
    }
}
