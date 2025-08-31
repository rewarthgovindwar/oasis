<?php

namespace Modules\Wallet\Http\Controllers\api;

use App\SmBankAccount;
use App\SmGeneralSettings;
use App\SmNotification;
use App\SmPaymentGatewaySetting;
use App\SmPaymentMethhod;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Wallet\Entities\WalletTransaction;

class WalletApiController extends Controller
{
    public function myWallet()
    {
        try {

            $myBalance = Auth::user()->wallet_balance ? number_format(Auth::user()->wallet_balance, 2, '.', '') : 0.00;
            $currencySymbol = generalSetting()->currency_symbol;

            $paymentMethods = SmPaymentMethhod::withoutGlobalScopes()
                ->whereNotIn('method', ['Cash', 'Wallet'])
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $bankAccounts = SmBankAccount::where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $walletTransactions = WalletTransaction::where('user_id', Auth::user()->id)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $stripe_info = SmPaymentGatewaySetting::where('gateway_name', 'stripe')
                ->where('school_id', Auth::user()->school_id)
                ->first();
            $razorpay_info = null;
            if (moduleStatusCheck('RazorPay')) {
                $razorpay_info = SmPaymentGatewaySetting::where('gateway_name', 'RazorPay')
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
            }

            return response()->json(['currencySymbol' => $currencySymbol, 'myBalance' => $myBalance, 'paymentMethods' => $paymentMethods, 'bankAccounts' => $bankAccounts, 'walletTransactions' => $walletTransactions, 'stripe_info' => $stripe_info, 'razorpay_info' => $razorpay_info]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function addWalletAmount(Request $request)
    {
        $request->validate([
            'amount' => 'required',
            'payment_method' => 'required',
            'bank' => 'required_if:payment_method,Bank',
            'file' => 'mimes:jpg,jpeg,png,pdf',
        ]);

        try {
            if ($request->payment_method === 'Cheque' || $request->payment_method === 'Bank') {
                $uploadFile = '';
                if ($request->file('file') !== '') {
                    $maxFileSize = SmGeneralSettings::first('file_size')->file_size;
                    $file = $request->file('file');
                    $fileSize = filesize($file);
                    $fileSizeKb = ($fileSize / 1000000);
                    if ($fileSizeKb >= $maxFileSize) {
                        return response()->json(['error' => 'Max upload file size '.$maxFileSize.' Mb is set in system']);
                    }

                    $file = $request->file('file');
                    $uploadFile = 'doc1-'.md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();
                    $file->move('public/uploads/student/document/', $uploadFile);
                    $uploadFile = 'public/uploads/student/document/'.$uploadFile;
                }

                $addPayment = new WalletTransaction();
                $addPayment->amount = $request->amount;
                $addPayment->payment_method = $request->payment_method;
                $addPayment->bank_id = $request->bank;
                $addPayment->note = $request->note;
                $addPayment->file = $uploadFile;
                $addPayment->type = 'diposit';
                $addPayment->user_id = Auth::user()->id;
                $addPayment->school_id = Auth::user()->school_id;
                $addPayment->academic_id = getAcademicId();
                $addPayment->save();

                // Notification Start
                $this->sendNotification(1, 1, 'Wallet Request');

                $accounts_ids = User::where('role_id', 6)->get();
                foreach ($accounts_ids as $account_id) {
                    $this->sendNotification($account_id->id, $account_id->role_id, 'Wallet Request');
                }

                // Notification End
            } else {
                $addPayment = new WalletTransaction();
                $addPayment->amount = $request->amount;
                $addPayment->payment_method = $request->payment_method;
                $addPayment->user_id = Auth::user()->id;
                $addPayment->type = 'diposit';
                $addPayment->school_id = Auth::user()->school_id;
                $addPayment->academic_id = getAcademicId();
                $addPayment->save();
            }

            return response()->json([
                'sucess' => 'Wallet request submitted',
                'id' => $addPayment->id,
                'amount' => $request->amount,
                'transactionId' => 'wallet_request_id_'.$addPayment->id,
                'description' => 'Wallet Request',
            ]);

        } catch (Exception $exception) {
            return response()->json(['error' => 'Error adding wallet']);
        }
    }

    public function confirmWalletPayment(Request $request)
    {

        $walletTransaction = WalletTransaction::find($request->id);

        if ($walletTransaction) {
            $walletTransaction->amount = $request->amount;
            $walletTransaction->status = 'approve';
            $walletTransaction->updated_at = date('Y-m-d');
            $walletTransaction->update();

            $user = User::find($walletTransaction->user_id);

            $currentBalance = $user->wallet_balance;
            $user->wallet_balance = $currentBalance + $request->amount;
            $user->update();
            $gs = generalSetting();
            $compact['full_name'] = $user->full_name;
            $compact['method'] = $walletTransaction->payment_method;
            $compact['create_date'] = date('Y-m-d');
            $compact['school_name'] = $gs->school_name;
            $compact['current_balance'] = $user->wallet_balance;
            $compact['add_balance'] = $request->amount;

            @send_mail($user->email, $user->full_name, 'wallet_approve', $compact);

            return response()->json([
                'sucess' => 'Wallet added',
            ]);
        }

        return null;

    }

    public function walletRefundRequestStore(Request $request)
    {

        $request->validate([
            'refund_note' => 'required',
            'refund_file' => 'mimes:jpg,jpeg,png,pdf',
        ]);

        $existRefund = WalletTransaction::where('type', 'refund')
            ->where('user_id', $request->user_id)
            ->where('status', 'pending')
            ->where('school_id', Auth::user()->school_id)
            ->first();

        if ($existRefund) {
            return response()->json([
                'error' => 'You Already Request For Refund',
            ]);
        }

        try {
            $uploadFile = '';
            if ($request->file('refund_file') !== '') {
                $maxFileSize = SmGeneralSettings::first('file_size')->file_size;
                $file = $request->file('refund_file');
                $fileSize = filesize($file);
                $fileSizeKb = ($fileSize / 1000000);
                if ($fileSizeKb >= $maxFileSize) {
                    Toastr::error('Max upload file size '.$maxFileSize.' Mb is set in system', 'Failed');

                    return redirect()->back();
                }

                $file = $request->file('refund_file');
                $uploadFile = 'doc1-'.md5($file->getClientOriginalName().time()).'.'.$file->getClientOriginalExtension();
                $file->move('public/uploads/student/document/', $uploadFile);
                $uploadFile = 'public/uploads/student/document/'.$uploadFile;
            }

            $walletTransaction = new WalletTransaction();
            $walletTransaction->user_id = $request->user_id;
            $walletTransaction->amount = $request->refund_amount;
            $walletTransaction->type = 'refund';
            $walletTransaction->payment_method = 'Wallet';
            $walletTransaction->note = $request->refund_note;
            $walletTransaction->file = $uploadFile;
            $walletTransaction->school_id = Auth::user()->school_id;
            $walletTransaction->save();

            return response()->json(['success' => 'Refund Request Submitted']);
        } catch (Exception $exception) {
            return response()->json(['error' => 'error submitting refund request']);
        }
    }

    // Private Function

    private function walletAmounts($type, $status)
    {
        return WalletTransaction::where('type', $type)
            ->where('status', $status)
            ->where('school_id', Auth::user()->school_id)
            ->get();
    }

    private function sendNotification($user_id, $role_id, string $message): void
    {
        $smNotification = new SmNotification;
        $smNotification->user_id = $user_id;
        $smNotification->role_id = $role_id;
        $smNotification->date = date('Y-m-d');
        $smNotification->message = $message;
        $smNotification->school_id = Auth::user()->school_id;
        $smNotification->academic_id = getAcademicId();
        $smNotification->save();
    }
}
