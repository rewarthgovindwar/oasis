<?php

namespace App\PaymentGateway;

use App\User;
use Illuminate\Support\Facades\Auth;
use Modules\Wallet\Entities\WalletTransaction;

class RazorPayPayment
{
    public function handle(array $data): void
    {

        if ($data['type'] === 'Wallet') {
            $user = User::find($data['user_id']);
            $currentBalance = $user->wallet_balance;
            $user->wallet_balance = $currentBalance + $data['amount'];
            $user->update();

            $walletTransaction = new WalletTransaction();
            $walletTransaction->amount = $data['amount'] - gv($data, 'service_charge', 0);
            $walletTransaction->payment_method = 'RazorPay';
            $walletTransaction->user_id = $user->id;
            $walletTransaction->type = $data['wallet_type'];
            $walletTransaction->school_id = Auth::user()->school_id;
            $walletTransaction->academic_id = getAcademicId();
            $walletTransaction->status = 'approve';
            $walletTransaction->save();

            $gs = generalSetting();
            $compact['full_name'] = $user->full_name;
            $compact['method'] = $walletTransaction->payment_method;
            $compact['create_date'] = date('Y-m-d');
            $compact['school_name'] = $gs->school_name;
            $compact['current_balance'] = $user->wallet_balance;
            $compact['add_balance'] = $data['amount'];
            @send_mail($user->email, $user->full_name, 'wallet_approve', $compact);
        }

    }
}
