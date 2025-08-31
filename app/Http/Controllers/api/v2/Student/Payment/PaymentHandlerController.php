<?php

namespace App\Http\Controllers\api\v2\Student\Payment;

use App\Http\Controllers\Controller;
use App\Models\StudentRecord;
use App\Scopes\AcademicSchoolScope;
use App\Scopes\ActiveStatusSchoolScope;
use App\SmAcademicYear;
use App\SmAddIncome;
use App\SmGeneralSettings;
use App\SmPaymentMethhod;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Modules\Fees\Entities\FmFeesInvoice;
use Modules\Fees\Entities\FmFeesInvoiceChield;
use Modules\Fees\Entities\FmFeesTransaction;
use Modules\Wallet\Entities\WalletTransaction;

class PaymentHandlerController extends Controller
{
    // request = amount,payment_method,fees_invoice_id,type
    public function handlePayment(Request $request)
    {
        if ($request->type == 'walletAddBallence') {
            $walletTransaction = new WalletTransaction();
            $walletTransaction->amount = $request->amount;
            $walletTransaction->payment_method = $request->payment_method;
            $walletTransaction->user_id = auth()->user()->id;
            $walletTransaction->type = 'diposit';
            $walletTransaction->status = 'approve';
            $walletTransaction->school_id = auth()->user()->school_id;
            $walletTransaction->academic_id = SmAcademicYear::API_ACADEMIC_YEAR(auth()->user()->school_id);
            $result = $walletTransaction->save();

            if ($result) {
                $user = User::where('school_id', auth()->user()->school_id)->find($walletTransaction->user_id);
                $currentBalance = $user->wallet_balance;
                $user->wallet_balance = $currentBalance + $walletTransaction->amount;
                $user->update();
                $gs = generalSetting();
                $compact['full_name'] = $user->full_name;
                $compact['method'] = $walletTransaction->payment_method;
                $compact['create_date'] = date('Y-m-d');
                $compact['school_name'] = $gs->school_name;
                $compact['current_balance'] = $user->wallet_balance;
                $compact['add_balance'] = $walletTransaction->amount;

                @send_mail($user->email, $user->full_name, 'wallet_approve', $compact);

                $paymentMethod = SmPaymentMethhod::withoutGlobalScope(ActiveStatusSchoolScope::class)
                    ->where('school_id', auth()->user()->school_id)
                    ->find($walletTransaction->payment_method);

                $data = [
                    'add_amount' => (float) $walletTransaction->amount,
                    'add_method' => (string) $paymentMethod->method,
                    'add_status' => (string) $walletTransaction->status,
                    'add_type' => (string) $walletTransaction->type,
                ];

                $response = [
                    'success' => true,
                    'data' => $data,
                    'message' => 'Wallet Ballance Added Successfully.',
                ];
            }
        } elseif ($request->type == 'feesInvoice') {
            $invoice = FmFeesInvoice::withoutGlobalScope(AcademicSchoolScope::class)
                ->where('school_id', auth()->user()->school_id)
                ->find($request->fees_invoice_id);

            if ($invoice) {
                $record = StudentRecord::where('student_id', $invoice->student_id)
                    ->where('school_id', auth()->user()->school_id)
                    ->where('class_id', $invoice->class_id)
                    ->first();

                if ($record) {
                    $fmFeesTransaction = new FmFeesTransaction();
                    $fmFeesTransaction->fees_invoice_id = $request->fees_invoice_id;
                    $fmFeesTransaction->payment_method = $request->payment_method;
                    $fmFeesTransaction->student_id = auth()->user()->school_id;
                    $fmFeesTransaction->record_id = $record->id;
                    $fmFeesTransaction->user_id = auth()->user()->id;
                    $fmFeesTransaction->paid_status = 'approve';
                    $fmFeesTransaction->school_id = auth()->user()->school_id;
                    $fmFeesTransaction->academic_id = SmAcademicYear::API_ACADEMIC_YEAR(auth()->user()->school_id);
                    $fmFeesTransaction->save();

                    $feesInvoiceChilds = FmFeesInvoiceChield::where('fees_invoice_id', $invoice->id)
                        ->where('school_id', auth()->user()->school_id)
                        ->get();

                    foreach ($feesInvoiceChilds as $feeInvoiceChild) {
                        // $storeTransactionChield = new FmFeesTransactionChield();
                        $storeTransactionChield = FmFeesInvoiceChield::where('fees_invoice_id', $request->fees_invoice_id)
                            ->where('fees_type', $feeInvoiceChild->fees_type)
                            ->first();
                        // $storeTransactionChield->fees_transaction_id = $storeTransaction->id;
                        // $storeTransactionChield->fees_type = $feesInvoiceChild->fees_type;
                        $storeTransactionChield->due_amount = $feeInvoiceChild->due_amount - $request->amount;
                        $storeTransactionChield->paid_amount = $feeInvoiceChild->paid_amount + $request->amount;
                        $storeTransactionChield->school_id = auth()->user()->school_id;
                        $storeTransactionChield->academic_id = SmAcademicYear::API_ACADEMIC_YEAR(auth()->user()->school_id);
                        $storeTransactionChield->save();

                        // $transcationId = FmFeesTransaction::find($storeTransactionChield->fees_transaction_id);

                        // $fesInvoiceId = FmFeesInvoiceChield::where('fees_invoice_id', $transcationId->fees_invoice_id)
                        //     ->where('fees_type', $feesInvoiceChild->fees_type)
                        //     ->first();

                        // $storeFeesInvoiceChield = FmFeesInvoiceChield::find($fesInvoiceId->id);
                        // $storeFeesInvoiceChield->due_amount = $storeFeesInvoiceChield->due_amount - $feesInvoiceChild->paid_amount;
                        // $storeFeesInvoiceChield->paid_amount = $storeFeesInvoiceChield->paid_amount + $feesInvoiceChild->paid_amount;
                        // $storeFeesInvoiceChield->service_charge = chargeAmount($transcation->payment_method, $feesInvoiceChild->paid_amount);
                        // $storeFeesInvoiceChield->update();

                        // $feesInvoiceChild->paid_amount = $request->amount;
                        // $feesInvoiceChild->due_amount = 0;
                        $feeInvoiceChild->save();
                    }

                    $income_head = SmGeneralSettings::where('school_id', auth()->user()->school_id)->first('income_head_id');

                    $smAddIncome = new SmAddIncome();
                    $smAddIncome->name = 'Fees Collect';
                    $smAddIncome->date = date('Y-m-d');
                    $smAddIncome->amount = $request->amount;
                    $smAddIncome->fees_collection_id = $fmFeesTransaction->fees_invoice_id;
                    $smAddIncome->active_status = 1;
                    $smAddIncome->income_head_id = $income_head->income_head_id;
                    $smAddIncome->payment_method_id = $request->payment_method;
                    $smAddIncome->created_by = Auth()->user()->id;
                    $smAddIncome->school_id = auth()->user()->school_id;
                    $smAddIncome->academic_id = SmAcademicYear::API_ACADEMIC_YEAR(auth()->user()->school_id);
                    $smAddIncome->save();

                    $paymentMethod = SmPaymentMethhod::withoutGlobalScope(ActiveStatusSchoolScope::class)
                        ->where('school_id', auth()->user()->school_id)
                        ->find($request->payment_method);

                    $invoice->payment_status = 'approved';
                    $invoice->payment_method = $paymentMethod->method;
                    $invoice->save();

                    $data = [
                        'paid_amount' => (float) $request->amount,
                        'paid_method' => (string) $paymentMethod->method,
                        'paid_status' => (string) $invoice->payment_status,
                    ];

                    $response = [
                        'success' => true,
                        'data' => $data,
                        'message' => 'Fees Payment Successfully.',
                    ];
                }
            }
        }

        return response()->json($response, 200);
    }
}
