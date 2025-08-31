<?php

namespace App\Traits;

use App\Models\DirectFeesInstallment;
use App\Models\DirectFeesInstallmentAssign;
use App\Models\StudentRecord;
use App\SmFeesAssign;
use App\SmFeesDiscount;
use App\SmFeesMaster;
use Illuminate\Support\Facades\Auth;

trait DirectFeesAssignTrait
{
    use FeesCarryForward;

    public function assignDirectFees($record_id = null, $class_id = null, $section_id = null, $master_id = null): void
    {
        if (is_null($record_id)) {
            $fees_master = SmFeesMaster::find($master_id);
            $class_id = $fees_master->class_id;
            $section_id = $fees_master->section_id;
            $student_records = StudentRecord::query();
            $student_records = $student_records->where('is_promote', 0)->where('class_id', $class_id);
            if ($section_id !== null) {
                $student_records = $student_records->where('section_id', $section_id);
            }

            $student_records = $student_records->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->where('is_promote', 0)->get();
        } else {
            $student_records = StudentRecord::where('id', $record_id)->get();
            $fees_master = SmFeesMaster::where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->where('class_id', $class_id)
                ->when(! is_null($section_id), function ($q) use ($section_id): void {
                    $q->where('section_id', $section_id);
                })->latest()
                ->first();

            if (! $fees_master) {
                $fees_master = SmFeesMaster::where('school_id', Auth::user()->school_id)
                    ->where('academic_id', getAcademicId())
                    ->where('class_id', $class_id)
                    ->latest()
                    ->first();
            }
        }

        if (! $fees_master) {
            return;
        }

        foreach ($student_records as $student_record) {
            $old_assign = SmFeesAssign::where('record_id', $student_record->id)->where('fees_master_id', $fees_master->id)->first();

            $assign_fees = $old_assign ?: new SmFeesAssign();
            $assign_fees->student_id = $student_record->student_id;
            $assign_fees->fees_amount = $fees_master->amount;
            $assign_fees->fees_master_id = $fees_master->id;
            $assign_fees->class_id = $student_record->class_id;
            $assign_fees->section_id = $student_record->section_id;
            $assign_fees->record_id = $student_record->id;
            $assign_fees->school_id = Auth::user()->school_id;
            $assign_fees->academic_id = getAcademicId();
            $assign_fees->save();

            $installments = DirectFeesInstallment::where('fees_master_id', $fees_master->id)->get();
            if (count($installments) > 0) {
                $installMentID = '';
                foreach ($installments as $installment) {
                    $checkExist = DirectFeesInstallmentAssign::where('academic_id', getAcademicId())
                        ->where('record_id', $student_record->id)
                        ->where('student_id', $student_record->student_id)
                        ->where('fees_installment_id', $installment->id)
                        ->first();

                    if ($checkExist) {
                        $old_master = json_decode($checkExist->fees_master_ids);
                        $old_assign = json_decode($checkExist->assign_ids);

                        $old_assign[] = $assign_fees->id;
                        $old_master[] = $fees_master->id;

                        $assignInstallment = $checkExist;
                        $assignInstallment->fees_master_ids = json_encode($old_master);
                        $assignInstallment->assign_ids = json_encode($old_assign);
                    } else {
                        $assignInstallment = new DirectFeesInstallmentAssign();
                        $assignInstallment->fees_master_ids = json_encode([$fees_master->id]);
                        $assignInstallment->assign_ids = json_encode([$assign_fees->id]);
                    }

                    $assignInstallment->fees_installment_id = $installment->id;
                    $installMentID = $installment->id;
                    if (($installment->amount !== null)) {
                        $assignInstallment->amount = $installment->amount;
                    } else {
                        $assignInstallment->amount = (($fees_master->amount * $installment->percentange) / 100);
                    }

                    // $assignInstallment->amount = $installment->amount;
                    $assignInstallment->due_date = $installment->due_date;
                    $assignInstallment->fees_type_id = $fees_master->fees_type_id;
                    $assignInstallment->student_id = $student_record->student_id;
                    $assignInstallment->record_id = $student_record->id;
                    $assignInstallment->academic_id = getAcademicId();
                    $assignInstallment->school_id = auth()->user()->school_id;
                    $assignInstallment->save();
                }

                if ($student_record->studentDetail->forwardBalance) {
                    $this->feesCarryForwardInstallment($student_record, $fees_master, $assign_fees->id, $installMentID, $fees_master->amount);
                }
            }
        }
    }

    public function assignFeesDiscount($discount_id, $record_id): void
    {
        $fees_discount = SmFeesDiscount::find($discount_id);
        $installments = DirectFeesInstallmentAssign::where('active_status', 0)->where('record_id', $record_id)->get();

        if ($fees_discount && count($installments) > 0) {
            $total_discount = $fees_discount->amount;
            $num_of_installments = count($installments);
            $average = $total_discount / $num_of_installments;
            $avg_disc = round($average);
            $differnt = $total_discount - ($avg_disc * $num_of_installments);

            foreach ($installments as $key => $feesInstallment) {
                $feesInstallment->fees_discount_id = $discount_id;
                $feesInstallment->discount_amount = $avg_disc ?? null;
                if (($avg_disc + $differnt) <= $feesInstallment->amount) {
                    if ($differnt !== 0.0) {
                        $feesInstallment->discount_amount = $key === 0 ? ($avg_disc + $differnt) ?? null : $avg_disc ?? null;
                    }
                } else {

                    $feesInstallment->discount_amount = $feesInstallment->amount ?? null;
                    $feesInstallment->active_status = 1;
                }

                $feesInstallment->save();
            }
        }
    }
}
