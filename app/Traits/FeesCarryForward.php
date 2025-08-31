<?php

namespace App\Traits;

use App\Http\Controllers\Admin\FeesCollection\SmFeesController;
use App\Models\DirectFeesInstallmentAssign;
use App\Models\FeesCarryForwardSettings;
use App\SmFeesCarryForward;
use Carbon\Carbon;
use Illuminate\Http\Request;

trait FeesCarryForward
{
    public function feesCarryForwardInstallment($studentRecord, $fees_master, $assign_fees, $installMentID, $payableAmount = null): ?bool
    {
        $carryForward = SmFeesCarryForward::where('student_id', $studentRecord->id)->first();
        if (! $carryForward) {
            return null;
        }

        $settings = FeesCarryForwardSettings::first();

        if (Carbon::now()->format('Y-m-d') <= $carryForward->due_date) {
            if ($carryForward->balance_type === 'due' && $carryForward->balance > 0) {
                $dueBalance = $carryForward->balance;
                $directFeesInstallmentAssign = new DirectFeesInstallmentAssign();
                $directFeesInstallmentAssign->fees_master_ids = json_encode([$fees_master->id]);
                $directFeesInstallmentAssign->assign_ids = json_encode([$assign_fees]);
                $directFeesInstallmentAssign->fees_installment_id = $installMentID;
                $directFeesInstallmentAssign->amount = $dueBalance;
                $directFeesInstallmentAssign->due_date = $carryForward->due_date;
                $directFeesInstallmentAssign->fees_type_id = $fees_master->fees_type_id;
                $directFeesInstallmentAssign->student_id = $studentRecord->student_id;
                $directFeesInstallmentAssign->record_id = $studentRecord->id;
                $directFeesInstallmentAssign->academic_id = getAcademicId();
                $directFeesInstallmentAssign->school_id = auth()->user()->school_id;
                $directFeesInstallmentAssign->save();
                $updateCarry = SmFeesCarryForward::where('student_id', $studentRecord->id)->first();
                $updateCarry->balance = null;
                $updateCarry->balance_type = 'add';
                $updateCarry->update();
                carryForwardLog($studentRecord->id, $dueBalance, 'due', 'Fees Payment', 'installment');
            } elseif ($payableAmount <= $carryForward->balance) {
                $addBalance = $carryForward->balance - $payableAmount;
                $request = app()->make(Request::class);
                $request->merge([
                    'date' => date('Y-m-d H:i:s'),
                    'record_id' => $studentRecord->id,
                    'request_amount' => $payableAmount,
                    'real_amount' => $payableAmount,
                    'student_id' => $studentRecord->student_id,
                    'payment_mode' => $settings->payment_gateway,
                ]);
                $feesController = new SmFeesController();
                $feesController->addPayment($request);
                $updateCarry = SmFeesCarryForward::where('student_id', $studentRecord->id)->first();
                $updateCarry->balance = $addBalance;
                $updateCarry->balance_type = 'add';
                $updateCarry->update();
                carryForwardLog($studentRecord->id, $payableAmount, 'due', 'Fees Payment Added', 'installment');
                carryForwardLog($studentRecord->id, $addBalance, 'add', 'Fees Payment and Carry Ballance Added', 'installment');
            } else {
                $request = app()->make(Request::class);
                $request->merge([
                    'date' => date('Y-m-d H:i:s'),
                    'record_id' => $studentRecord->id,
                    'request_amount' => $carryForward->balance,
                    'real_amount' => $carryForward->balance,
                    'student_id' => $studentRecord->student_id,
                    'payment_mode' => $settings->payment_gateway,
                ]);

                $feesController = new SmFeesController();
                $feesController->addPayment($request);

                $updateCarry = SmFeesCarryForward::where('student_id', $studentRecord->id)->first();
                $updateCarry->balance = null;
                $updateCarry->balance_type = 'add';
                $updateCarry->update();

                carryForwardLog($studentRecord->id, $carryForward->balance, 'due', 'Fees Payment', 'installment');
            }

            return true;
        }

        return null;

    }
}
