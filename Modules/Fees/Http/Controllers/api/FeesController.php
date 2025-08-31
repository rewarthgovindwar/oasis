<?php

namespace Modules\Fees\Http\Controllers\api;

use App\Models\StudentRecord;
use App\Models\User;
use App\SmAddIncome;
use App\SmBankAccount;
use App\SmBankStatement;
use App\SmClass;
use App\SmGeneralSettings;
use App\SmPaymentGatewaySetting;
use App\SmPaymentMethhod;
use App\SmSchool;
use App\SmStudent;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Fees\Entities\FmFeesGroup;
use Modules\Fees\Entities\FmFeesInvoice;
use Modules\Fees\Entities\FmFeesInvoiceChield;
use Modules\Fees\Entities\FmFeesInvoiceSettings;
use Modules\Fees\Entities\FmFeesTransaction;
use Modules\Fees\Entities\FmFeesTransactionChield;
use Modules\Fees\Entities\FmFeesType;
use Modules\Fees\Entities\FmFeesWeaver;
use Modules\Fees\Http\Requests\BankFeesPayment;
use Modules\Fees\Http\Requests\FeesGroupRequest;
use Modules\Fees\Http\Requests\FeesPaymentRequest;
use Modules\Fees\Http\Requests\FeesTypeRequest;
use Modules\Fees\Http\Requests\InvoiceStoreRequest;
use Modules\Wallet\Entities\WalletTransaction;

class FeesController extends Controller
{
    public function feesGroup()
    {
        $feesGroups = FmFeesGroup::where('school_id', auth()->user()->school_id)
            ->where('academic_id', getAcademicId())
            ->get();

        return response()->json(['feesGroups' => $feesGroups]);
    }

    public function feesGroupStore(FeesGroupRequest $feesGroupRequest)
    {
        try {
            $fmFeesGroup = new FmFeesGroup();
            $fmFeesGroup->name = $feesGroupRequest->name;
            $fmFeesGroup->description = $feesGroupRequest->description;
            $fmFeesGroup->school_id = auth()->user()->school_id;
            $fmFeesGroup->academic_id = getAcademicId();
            $fmFeesGroup->save();

            return response()->json(['message' => 'Save Sucessfully']);
        } catch (Exception $exception) {
            return response()->json(['message' => 'Error']);
        }
    }

    public function feesGroupEdit($id)
    {
        try {
            if (checkAdmin() === true) {
                $feesGroup = FmFeesGroup::find($id);
            } else {
                $feesGroup = FmFeesGroup::where('id', $id)
                    ->where('school_id', auth()->user()->school_id)
                    ->first();
            }

            $feesGroups = FmFeesGroup::where('school_id', auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            return response()->json(['feesGroup' => $feesGroup, 'feesGroups' => $feesGroups]);
        } catch (Exception $exception) {
            return response()->json(['message' => 'Error']);
        }
    }

    public function feesGroupUpdate(FeesGroupRequest $feesGroupRequest)
    {
        try {
            if (checkAdmin() === true) {
                $feesGroup = FmFeesGroup::find($feesGroupRequest->id);
            } else {
                $feesGroup = FmFeesGroup::where('id', $feesGroupRequest->id)
                    ->where('school_id', auth()->user()->school_id)
                    ->first();
            }

            $feesGroup->name = $feesGroupRequest->name;
            $feesGroup->description = $feesGroupRequest->description;
            $feesGroup->academic_id = getAcademicId();
            $feesGroup->save();

            return response()->json(['message' => 'Update Sucessfully']);
        } catch (Exception $exception) {
            return response()->json(['message' => 'Error']);
        }
    }

    public function feesGroupDelete(Request $request)
    {
        try {
            if (checkAdmin() === true) {
                FmFeesGroup::destroy($request->id);
            } else {
                FmFeesGroup::where('id', $request->id)
                    ->where('school_id', auth()->user()->school_id)
                    ->delete();
            }

            return response()->json(['message' => 'Delete Sucessfully']);
        } catch (Exception $exception) {
            return response()->json(['message' => 'Error']);
        }
    }

    public function feesType()
    {
        $feesGroups = FmFeesGroup::where('school_id', auth()->user()->school_id)
            ->where('academic_id', getAcademicId())
            ->get();

        $feesTypes = FmFeesType::where('type', 'fees')
            ->where('school_id', auth()->user()->school_id)
            ->where('academic_id', getAcademicId())
            ->get();

        return response()->json(['feesGroups' => $feesGroups, 'feesTypes' => $feesTypes]);
    }

    public function feesTypeStore(FeesTypeRequest $feesTypeRequest)
    {
        try {
            $fmFeesType = new FmFeesType();
            $fmFeesType->name = $feesTypeRequest->name;
            $fmFeesType->fees_group_id = $feesTypeRequest->fees_group;
            $fmFeesType->description = $feesTypeRequest->description;
            $fmFeesType->school_id = auth()->user()->school_id;
            $fmFeesType->academic_id = getAcademicId();
            $fmFeesType->save();

            return response()->json(['message' => 'Save Sucessfully']);
        } catch (Exception $exception) {
            return response()->json(['message' => 'Error']);
        }
    }

    public function feesTypeEdit($id)
    {
        try {
            if (checkAdmin() === true) {
                $feesType = FmFeesType::find($id);
            } else {
                $feesType = FmFeesType::where('id', $id)
                    ->where('school_id', auth()->user()->school_id)
                    ->first();
            }

            $feesGroups = FmFeesGroup::where('school_id', auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $feesTypes = FmFeesType::where('type', 'fees')
                ->where('school_id', auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            return response()->json(['feesGroups' => $feesGroups, 'feesTypes' => $feesTypes, 'feesType' => $feesType]);
        } catch (Exception $exception) {
            return response()->json(['message' => 'Error']);
        }
    }

    public function feesTypeUpdate(FeesTypeRequest $feesTypeRequest)
    {
        try {
            if (checkAdmin() === true) {
                $feesType = FmFeesType::find($feesTypeRequest->id);
            } else {
                $feesType = FmFeesType::where('type', 'fees')
                    ->where('id', $feesTypeRequest->id)
                    ->where('school_id', auth()->user()->school_id)
                    ->first();
            }

            $feesType->name = $feesTypeRequest->name;
            $feesType->fees_group_id = $feesTypeRequest->fees_group;
            $feesType->description = $feesTypeRequest->description;
            $feesType->save();

            return response()->json(['message' => 'Save Sucessfully']);
        } catch (Exception $exception) {
            return response()->json(['message' => 'Error']);
        }
    }

    public function feesTypeDelete(Request $request)
    {
        try {
            $feesGroupId = FmFeesType::find($request->id);
            $checkExistsData = FmFeesGroup::where('id', $feesGroupId->fees_group_id)->first();

            if (! $checkExistsData) {
                FmFeesType::find($request->id)->delete();

                return response()->json(['message' => 'Delete Sucessfully']);
            }

            return response()->json(['message' => 'This Data Already Used In Fees Group Please Remove Those Data First']);

        } catch (Exception $exception) {
            return response()->json(['message' => 'Error']);
        }
    }

    public function feesInvoiceList()
    {
        $studentInvoices = FmFeesInvoice::where('type', 'fees')
            ->where('school_id', auth()->user()->school_id)
            ->where('academic_id', getAcademicId())
            ->with(['studentInfo', 'recordDetail.class', 'recordDetail.section'])
            ->get()
            ->map(function ($value): array {
                $amount = $value->Tamount ?? 0;
                $weaver = $value->Tweaver ?? 0;
                $fine = $value->Tfine ?? 0;
                $paid_amount = $value->Tpaidamount ?? 0;
                $sub_total = $value->Tsubtotal ?? 0;
                $balance = ($amount + $fine) - ($paid_amount + $weaver);

                return [
                    'id' => $value->id,
                    'amount' => $amount,
                    'weaver' => $weaver,
                    'fine' => $fine,
                    'paid_amount' => $paid_amount,
                    'sub_total' => $sub_total,
                    'balance' => $balance,
                    'student' => $value->studentInfo->full_name ?? '',
                    'class' => $value->recordDetail->class->class_name ?? '',
                    'section' => $value->recordDetail->section->section_name ?? '',
                    'status' => $balance === 0 ? 'paid' : ($paid_amount > 0 ? 'partial' : 'unpaid'),
                    'date' => $value->create_date ? dateConvert($value->create_date) : '',
                ];
            });

        return response()->json(['studentInvoices' => $studentInvoices]);
    }

    public function feesInvoice()
    {
        try {
            $classes = SmClass::where('school_id', auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $feesGroups = FmFeesGroup::where('school_id', auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $feesTypes = FmFeesType::where('type', 'fees')
                ->where('school_id', auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $paymentMethods = SmPaymentMethhod::whereIn('method', ['Cash', 'Cheque', 'Bank'])
                ->where('school_id', auth()->user()->school_id)
                ->get();

            $bankAccounts = SmBankAccount::where('school_id', auth()->user()->school_id)
                ->get();

            $invoiceSettings = FmFeesInvoiceSettings::where('school_id', auth()->user()->school_id)->first();

            return response()->json(['classes' => $classes, 'feesGroups' => $feesGroups, 'feesTypes' => $feesTypes, 'paymentMethods' => $paymentMethods, 'bankAccounts' => $bankAccounts, 'invoiceSettings' => $invoiceSettings]);
        } catch (Exception $exception) {
            return response()->json(['message' => 'Error']);
        }
    }

    public function feesInvoiceStore(InvoiceStoreRequest $invoiceStoreRequest)
    {
        try {
            if ($invoiceStoreRequest->student !== 'all_student') {
                $student = StudentRecord::find($invoiceStoreRequest->student);
                if ($invoiceStoreRequest->groups) {
                    if (empty($invoiceStoreRequest->singleInvoice)) {
                        $feesType = [];
                        $amount = [];
                        $weaver = [];
                        $sub_total = [];
                        $note = [];
                    }

                    foreach ($invoiceStoreRequest->groups as $group) {
                        if ($invoiceStoreRequest->singleInvoice === 1) {
                            $feesType = [];
                            $amount = [];
                            $weaver = [];
                            $sub_total = [];
                            $note = [];
                        }

                        $feesType[] = gv($group, 'feesType');
                        $amount[] = gv($group, 'amount');
                        $weaver[] = gv($group, 'weaver');
                        $sub_total[] = gv($group, 'sub_total');
                        $note[] = gv($group, 'note');

                        if ($invoiceStoreRequest->singleInvoice === 1) {
                            $this->invStore($invoiceStoreRequest->merge(['student' => $student->student_id,
                                'record_id' => $student->id,
                                'feesType' => $feesType,
                                'amount' => $amount,
                                'weaver' => $weaver,
                                'sub_total' => $sub_total,
                                'note' => $note,
                            ]));
                        }
                    }

                    if (empty($invoiceStoreRequest->singleInvoice)) {
                        $this->invStore($invoiceStoreRequest->merge(['student' => $student->student_id,
                            'record_id' => $student->id,
                            'feesType' => $feesType,
                            'amount' => $amount,
                            'weaver' => $weaver,
                            'sub_total' => $sub_total,
                            'note' => $note,
                        ]));
                    }
                }

                if ($invoiceStoreRequest->types) {
                    foreach ($invoiceStoreRequest->types as $type) {
                        $tfeesType = [];
                        $tamount = [];
                        $tweaver = [];
                        $tsub_total = [];
                        $tnote = [];

                        $tfeesType[] = gv($type, 'feesType');
                        $tamount[] = gv($type, 'amount');
                        $tweaver[] = gv($type, 'weaver');
                        $tsub_total[] = gv($type, 'sub_total');
                        $tnote[] = gv($type, 'note');

                        $this->invStore($invoiceStoreRequest->merge(['student' => $student->student_id,
                            'record_id' => $student->id,
                            'feesType' => $tfeesType,
                            'amount' => $tamount,
                            'weaver' => $tweaver,
                            'sub_total' => $tsub_total,
                            'note' => $tnote,
                        ]));
                    }
                }

                // Notification
                $students = SmStudent::with('parents')->find($student->student_id);
                sendNotification('Fees Assign', null, $students->user_id, 2);
                sendNotification('Fees Assign', null, $students->parents->user_id, 3);
            } else {
                $allStudents = StudentRecord::with('studentDetail', 'studentDetail.parents')
                    ->where('class_id', $invoiceStoreRequest->class)
                    ->where('school_id', Auth::user()->school_id)
                    ->where('academic_id', getAcademicId())
                    ->get();

                foreach ($allStudents as $allStudent) {
                    if ($invoiceStoreRequest->groups) {
                        if (empty($invoiceStoreRequest->singleInvoice)) {
                            $feesType = [];
                            $amount = [];
                            $weaver = [];
                            $sub_total = [];
                            $note = [];
                        }

                        foreach ($invoiceStoreRequest->groups as $group) {
                            if ($invoiceStoreRequest->singleInvoice === 1) {
                                $feesType = [];
                                $amount = [];
                                $weaver = [];
                                $sub_total = [];
                                $note = [];
                            }

                            $feesType[] = gv($group, 'feesType');
                            $amount[] = gv($group, 'amount', 0);
                            $weaver[] = gv($group, 'weaver', 0);
                            $sub_total[] = gv($group, 'sub_total', 0);
                            $note[] = gv($group, 'note');

                            if ($invoiceStoreRequest->singleInvoice === 1) {
                                $this->invStore($invoiceStoreRequest->merge(['student' => $allStudent->student_id,
                                    'record_id' => $allStudent->id,
                                    'feesType' => $feesType,
                                    'amount' => $amount,
                                    'weaver' => $weaver,
                                    'sub_total' => $sub_total,
                                    'note' => $note,
                                ]));
                            }

                        }

                        if (empty($invoiceStoreRequest->singleInvoice)) {
                            $this->invStore($invoiceStoreRequest->merge(['student' => $allStudent->student_id,
                                'record_id' => $allStudent->id,
                                'feesType' => $feesType,
                                'amount' => $amount,
                                'weaver' => $weaver,
                                'sub_total' => $sub_total,
                                'note' => $note,
                            ]));
                        }
                    }

                    if ($invoiceStoreRequest->types) {
                        foreach ($invoiceStoreRequest->types as $type) {
                            $tfeesType = [];
                            $tamount = [];
                            $tweaver = [];
                            $tsub_total = [];
                            $tnote = [];

                            $tfeesType[] = gv($type, 'feesType');
                            $tamount[] = gv($type, 'amount');
                            $tweaver[] = gv($type, 'weaver');
                            $tsub_total[] = gv($type, 'sub_total');
                            $tnote[] = gv($type, 'note');

                            $this->invStore($invoiceStoreRequest->merge(['student' => $allStudent->student_id,
                                'record_id' => $allStudent->id,
                                'feesType' => $tfeesType,
                                'amount' => $tamount,
                                'weaver' => $tweaver,
                                'sub_total' => $tsub_total,
                                'note' => $tnote,
                            ]));
                        }
                    }

                    // Notification
                    sendNotification('Fees Assign', null, $allStudent->studentDetail->user_id, 2);
                    sendNotification('Fees Assign', null, $allStudent->studentDetail->parents->user_id, 3);
                }
            }

            return response()->json(['message' => 'Save Sucessfully']);
        } catch (Exception $exception) {
            return response()->json(['Error' => 'Error']);
        }
    }

    public function feesInvoiceEdit($id)
    {
        try {
            // View Start
            $classes = SmClass::where('school_id', auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $feesGroups = FmFeesGroup::where('school_id', auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $feesTypes = FmFeesType::where('type', 'fees')
                ->where('school_id', auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $paymentMethods = SmPaymentMethhod::whereIn('method', ['Cash', 'Cheque', 'Bank'])
                ->where('school_id', auth()->user()->school_id)
                ->get();

            $bankAccounts = SmBankAccount::where('school_id', auth()->user()->school_id)
                ->get();
            // View End

            $invoiceSettings = FmFeesInvoiceSettings::where('school_id', auth()->user()->school_id)->first();

            $invoiceInfo = FmFeesInvoice::find($id);
            $invoiceDetails = FmFeesInvoiceChield::where('fees_invoice_id', $invoiceInfo->id)
                ->where('school_id', auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $students = StudentRecord::where('id', $invoiceInfo->record_id)
                ->where('class_id', $invoiceInfo->class_id)
                ->where('school_id', auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            return response()->json(['classes' => $classes, 'feesGroups' => $feesGroups, 'feesTypes' => $feesTypes, 'paymentMethods' => $paymentMethods, 'bankAccounts' => $bankAccounts, 'invoiceSettings' => $invoiceSettings, 'invoiceInfo' => $invoiceInfo, 'invoiceDetails' => $invoiceDetails, 'students' => $students]);
        } catch (Exception $exception) {
            return response()->json(['message' => 'Error']);
        }
    }

    public function feesInvoiceUpdate(InvoiceStoreRequest $invoiceStoreRequest)
    {
        try {
            $student = StudentRecord::find($invoiceStoreRequest->student);
            $storeFeesInvoice = FmFeesInvoice::find($invoiceStoreRequest->id);
            $storeFeesInvoice->class_id = $invoiceStoreRequest->class;
            $storeFeesInvoice->create_date = date('Y-m-d', strtotime($invoiceStoreRequest->create_date));
            $storeFeesInvoice->due_date = date('Y-m-d', strtotime($invoiceStoreRequest->due_date));
            $storeFeesInvoice->payment_status = $invoiceStoreRequest->payment_status;
            $storeFeesInvoice->bank_id = $invoiceStoreRequest->bank;
            $storeFeesInvoice->student_id = $student->student_id;
            $storeFeesInvoice->record_id = $invoiceStoreRequest->student;
            $storeFeesInvoice->school_id = auth()->user()->school_id;
            $storeFeesInvoice->academic_id = getAcademicId();
            $storeFeesInvoice->update();

            FmFeesInvoiceChield::where('fees_invoice_id', $invoiceStoreRequest->id)->delete();
            FmFeesWeaver::where('fees_invoice_id', $storeFeesInvoice->id)->delete();

            foreach ($invoiceStoreRequest->feesType as $key => $type) {
                $storeFeesInvoiceChield = new FmFeesInvoiceChield();
                $storeFeesInvoiceChield->fees_invoice_id = $storeFeesInvoice->id;
                $storeFeesInvoiceChield->fees_type = $type;
                $storeFeesInvoiceChield->amount = $invoiceStoreRequest->amount[$key];
                $storeFeesInvoiceChield->weaver = $invoiceStoreRequest->weaver ? $invoiceStoreRequest->weaver[$key] : null;
                $storeFeesInvoiceChield->sub_total = $invoiceStoreRequest->sub_total[$key];
                $storeFeesInvoiceChield->due_amount = $invoiceStoreRequest->sub_total[$key];
                $storeFeesInvoiceChield->note = $invoiceStoreRequest->note ? $invoiceStoreRequest->note[$key] : null;

                if ($invoiceStoreRequest->paid_amount) {
                    $storeFeesInvoiceChield->paid_amount = $invoiceStoreRequest->paid_amount[$key];
                }

                $storeFeesInvoiceChield->school_id = auth()->user()->school_id;
                $storeFeesInvoiceChield->academic_id = getAcademicId();
                $storeFeesInvoiceChield->save();

                $storeWeaver = new FmFeesWeaver();
                $storeWeaver->fees_invoice_id = $storeFeesInvoice->id;
                $storeWeaver->fees_type = $type;
                $storeWeaver->student_id = $invoiceStoreRequest->student;
                $storeWeaver->weaver = $invoiceStoreRequest->weaver ? $invoiceStoreRequest->weaver[$key] : null;
                $storeWeaver->note = $invoiceStoreRequest->note ? $invoiceStoreRequest->note[$key] : null;
                $storeWeaver->school_id = auth()->user()->school_id;
                $storeWeaver->academic_id = getAcademicId();
                $storeWeaver->save();
            }

            // Notification
            $student = SmStudent::with('parents')->find($storeFeesInvoice->student_id);
            sendNotification('Fees Assign Update', null, $student->user_id, 2);
            sendNotification('Fees Assign Update', null, $student->parents->user_id, 3);

            return response()->json(['message' => 'Update Sucessfully']);
        } catch (Exception $exception) {
            return response()->json(['message' => 'Error']);
        }
    }

    public function feesInvoiceView($id, $state)
    {
        $invoiceInfo = FmFeesInvoice::find($id);
        $invoiceDetails = FmFeesInvoiceChield::where('fees_invoice_id', $invoiceInfo->id)
            ->where('school_id', auth()->user()->school_id)
            ->where('academic_id', getAcademicId())
            ->get()->map(function ($value): array {
                $total = ($value->amount + $value->fine) - ($value->paid_amount + $value->weaver);

                return [
                    'typeName' => $value->feesType ? $value->feesType->name : '',
                    'amount' => $value->amount ? (float) $value->amount : 0,
                    'weaver' => $value->weaver ?: 0,
                    'fine' => $value->fine ?: 0,
                    'sub_total' => $value->paid_amount ?: 0,
                    'total' => $total,
                ];
            });

        $totalAmount = $invoiceDetails->sum('amount');
        $totalWeaver = $invoiceDetails->sum('weaver');
        $totalPaidAmount = $invoiceDetails->sum('paid_amount');
        $totalFine = $invoiceDetails->sum('fine');

        $banks = SmBankAccount::where('active_status', '=', 1)
            ->where('school_id', auth()->user()->school_id)
            ->get()->map(function ($value): array {
                return [
                    'bank_name' => $value->bank_name,
                    'account_name' => $value->account_name,
                    'account_number' => $value->account_number,
                    'account_type' => $value->account_type,
                ];
            });

        if ($state === 'view') {
            return response()->json(['invoiceInfo' => $invoiceInfo, 'invoiceDetails' => $invoiceDetails, 'banks' => $banks, 'totalAmount' => $totalAmount, 'totalWeaver' => $totalWeaver, 'totalPaidAmount' => $totalPaidAmount, 'totalFine' => $totalFine]);
        }

        return response()->json(['invoiceInfo' => $invoiceInfo, 'invoiceDetails' => $invoiceDetails, 'banks' => $banks]);

    }

    public function feesInvoiceDelete(Request $request)
    {
        try {
            $invoiceDelete = FmFeesInvoice::find($request->id)->delete();
            if ($invoiceDelete) {
                FmFeesInvoiceChield::where('fees_invoice_id', $request->id)->delete();
            }

            return response()->json(['message' => 'Delete Sucessfully']);
        } catch (Exception $exception) {
            return response()->json(['message' => 'Error']);
        }
    }

    public function addFeesPayment($id)
    {
        try {
            $classes = SmClass::where('school_id', auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $feesGroups = FmFeesGroup::where('school_id', auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $feesTypes = FmFeesType::where('school_id', auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $feesTypes = FmFeesType::where('type', 'fees')
                ->where('school_id', auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $paymentMethods = SmPaymentMethhod::whereIn('method', ['Cash', 'Cheque', 'Bank'])
                ->where('active_status', 1)
                ->where('school_id', auth()->user()->school_id)
                ->get();

            $bankAccounts = SmBankAccount::where('school_id', auth()->user()->school_id)
                ->get();

            $invoiceInfo = FmFeesInvoice::with('studentInfo')->find($id);
            $walletBalance = $invoiceInfo->studentInfo->user->wallet_balance;

            $invoiceDetails = FmFeesInvoiceChield::where('fees_invoice_id', $invoiceInfo->id)
                ->where('school_id', auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $stripe_info = SmPaymentGatewaySetting::where('gateway_name', 'stripe')
                ->where('school_id', auth()->user()->school_id)
                ->first();

            return response()->json(['classes' => $classes, 'feesGroups' => $feesGroups, 'feesTypes' => $feesTypes, 'paymentMethods' => $paymentMethods, 'bankAccounts' => $bankAccounts, 'invoiceInfo' => $invoiceInfo, 'invoiceDetails' => $invoiceDetails, 'stripe_info' => $stripe_info, 'walletBalance' => $walletBalance]);
        } catch (Exception $exception) {
            return response()->json(['message' => 'Error']);
        }

    }

    public function feesPaymentStore(FeesPaymentRequest $feesPaymentRequest)
    {
        // if ($request->total_paid_amount == null) {
        //     Toastr::warning('Paid Amount Can Not Be Blank', 'Failed');
        //     return redirect()->back();
        // }

        try {
            $destination = 'public/uploads/student/document/';
            $file = fileUpload($feesPaymentRequest->file('file'), $destination);

            $record = StudentRecord::find($feesPaymentRequest->student_id);

            $student = SmStudent::with('parents')->find($record->student_id);

            if ($feesPaymentRequest->add_wallet > 0) {
                $user = User::find($student->user_id);
                $walletBalance = $user->wallet_balance;
                $user->wallet_balance = $walletBalance + $feesPaymentRequest->add_wallet;
                $user->update();

                $walletTransaction = new WalletTransaction();
                $walletTransaction->amount = $feesPaymentRequest->add_wallet;
                $walletTransaction->payment_method = $feesPaymentRequest->payment_method;
                $walletTransaction->user_id = $user->id;
                $walletTransaction->type = 'diposit';
                $walletTransaction->status = 'approve';
                $walletTransaction->note = 'Fees Extra Payment Add';
                $walletTransaction->school_id = auth()->user()->school_id;
                $walletTransaction->academic_id = getAcademicId();
                $walletTransaction->save();

                $school = SmSchool::find($user->school_id);

                $compact['user_email'] = $user->email;
                $compact['full_name'] = $user->full_name;
                $compact['method'] = $feesPaymentRequest->payment_method;
                $compact['create_date'] = date('Y-m-d');
                $compact['school_name'] = $school->school_name;
                $compact['current_balance'] = $user->wallet_balance;
                $compact['add_balance'] = $feesPaymentRequest->total_paid_amount;
                $compact['previous_balance'] = $user->wallet_balance - $feesPaymentRequest->add_wallet;

                @send_mail($user->email, $user->full_name, 'fees_extra_amount_add', $compact);

                // Notification
                sendNotification('Fees Xtra Amount Add', null, $student->user_id, 2);
            }

            $fmFeesTransaction = new FmFeesTransaction();
            $fmFeesTransaction->fees_invoice_id = $feesPaymentRequest->invoice_id;
            $fmFeesTransaction->payment_note = $feesPaymentRequest->payment_note;
            $fmFeesTransaction->payment_method = $feesPaymentRequest->payment_method;
            $fmFeesTransaction->bank_id = $feesPaymentRequest->bank;
            $fmFeesTransaction->student_id = $feesPaymentRequest->student_id;
            $fmFeesTransaction->user_id = auth()->user()->id;
            $fmFeesTransaction->file = $file;
            $fmFeesTransaction->paid_status = 'approve';
            $fmFeesTransaction->school_id = auth()->user()->school_id;
            $fmFeesTransaction->academic_id = getAcademicId();
            $fmFeesTransaction->save();

            foreach ($feesPaymentRequest->fees_type as $key => $type) {
                $id = FmFeesInvoiceChield::where('fees_invoice_id', $feesPaymentRequest->invoice_id)->where('fees_type', $type)->first('id')->id;
                $storeFeesInvoiceChield = FmFeesInvoiceChield::find($id);
                $storeFeesInvoiceChield->weaver = $feesPaymentRequest->weaver ? $feesPaymentRequest->weaver[$key] : null;
                $storeFeesInvoiceChield->due_amount = $feesPaymentRequest->due[$key];
                $storeFeesInvoiceChield->paid_amount += $feesPaymentRequest->paid_amount[$key] - $feesPaymentRequest->extraAmount[$key];
                $storeFeesInvoiceChield->fine += $feesPaymentRequest->fine[$key];
                $storeFeesInvoiceChield->update();

                $storeWeaver = new FmFeesWeaver();
                $storeWeaver->fees_invoice_id = $feesPaymentRequest->invoice_id;
                $storeWeaver->fees_type = $type;
                $storeWeaver->student_id = $student->id;
                $storeWeaver->weaver = $feesPaymentRequest->weaver ? $feesPaymentRequest->weaver[$key] : null;
                $storeWeaver->note = $feesPaymentRequest->note ? $feesPaymentRequest->note[$key] : null;
                $storeWeaver->school_id = auth()->user()->school_id;
                $storeWeaver->academic_id = getAcademicId();
                $storeWeaver->save();

                if ($feesPaymentRequest->paid_amount[$key] > 0) {
                    $storeTransactionChield = new FmFeesTransactionChield();
                    $storeTransactionChield->fees_transaction_id = $fmFeesTransaction->id;
                    $storeTransactionChield->fees_type = $type;
                    $storeTransactionChield->weaver = $feesPaymentRequest->weaver ? $feesPaymentRequest->weaver[$key] : null;
                    $storeTransactionChield->fine = $feesPaymentRequest->fine[$key];
                    $storeTransactionChield->paid_amount = $feesPaymentRequest->paid_amount[$key];
                    $storeTransactionChield->note = $feesPaymentRequest->note ? $feesPaymentRequest->note[$key] : null;
                    $storeTransactionChield->school_id = auth()->user()->school_id;
                    $storeTransactionChield->academic_id = getAcademicId();
                    $storeTransactionChield->save();
                }

                // Income
                $payment_method = SmPaymentMethhod::where('method', $feesPaymentRequest->payment_method)->first();
                $income_head = generalSetting();

                $add_income = new SmAddIncome();
                $add_income->name = 'Fees Collect';
                $add_income->date = date('Y-m-d');
                $add_income->amount = $feesPaymentRequest->paid_amount[$key];
                $add_income->fees_collection_id = $fmFeesTransaction->id;
                $add_income->active_status = 1;
                $add_income->income_head_id = $income_head->income_head_id;
                $add_income->payment_method_id = $payment_method->id;
                $add_income->created_by = Auth()->user()->id;
                $add_income->school_id = auth()->user()->school_id;
                $add_income->academic_id = getAcademicId();
                $add_income->save();

                // Bank
                if ($feesPaymentRequest->payment_method === 'Bank') {
                    $payment_method = SmPaymentMethhod::where('method', $feesPaymentRequest->payment_method)->first();
                    $bank = SmBankAccount::where('id', $feesPaymentRequest->bank)
                        ->where('school_id', auth()->user()->school_id)
                        ->first();
                    $after_balance = $bank->current_balance + $feesPaymentRequest->paid_amount[$key];

                    $bank_statement = new SmBankStatement();
                    $bank_statement->amount = $feesPaymentRequest->paid_amount[$key];
                    $bank_statement->after_balance = $after_balance;
                    $bank_statement->type = 1;
                    $bank_statement->details = 'Fees Payment';
                    $bank_statement->item_sell_id = $fmFeesTransaction->id;
                    $bank_statement->payment_date = date('Y-m-d');
                    $bank_statement->bank_id = $feesPaymentRequest->bank;
                    $bank_statement->school_id = auth()->user()->school_id;
                    $bank_statement->payment_method = $payment_method->id;
                    $bank_statement->save();

                    $current_balance = SmBankAccount::find($feesPaymentRequest->bank);
                    $current_balance->current_balance = $after_balance;
                    $current_balance->update();
                }
            }

            // Notification
            sendNotification('Add Fees Payment', null, $student->user_id, 2);
            sendNotification('Add Fees Payment', null, $student->parents->user_id, 3);

            return response()->json(['message' => 'Payment Sucessfully']);
        } catch (Exception $exception) {
            return response()->json(['message' => 'Error']);
        }
    }

    public function singlePaymentView($id)
    {
        $generalSetting = SmGeneralSettings::where('school_id', auth()->user()->school_id)->first();
        $transcationInfo = FmFeesTransaction::find($id);
        $transcationDetails = FmFeesTransactionChield::where('fees_transaction_id', $transcationInfo->id)
            ->where('school_id', auth()->user()->school_id)
            ->where('academic_id', getAcademicId())
            ->get();
        $invoiceInfo = FmFeesInvoice::find($transcationInfo->fees_invoice_id);

        return response()->json(['generalSetting' => $generalSetting, 'invoiceInfo' => $invoiceInfo, 'transcationDetails' => $transcationDetails]);
    }

    public function deleteSingleFeesTranscation($id)
    {
        try {
            $total_amount = 0;
            $transcation = FmFeesTransaction::find($id);
            $allTranscations = FmFeesTransactionChield::where('fees_transaction_id', $transcation->id)->get();
            foreach ($allTranscations as $allTranscation) {
                $total_amount += $allTranscation->paid_amount;

                $transcationId = FmFeesTransaction::find($allTranscation->fees_transaction_id);

                $fesInvoiceId = FmFeesInvoiceChield::where('fees_invoice_id', $transcationId->fees_invoice_id)
                    ->where('fees_type', $allTranscation->fees_type)
                    ->first();

                $storeFeesInvoiceChield = FmFeesInvoiceChield::find($fesInvoiceId->id);
                $storeFeesInvoiceChield->due_amount += $allTranscation->paid_amount;
                $storeFeesInvoiceChield->paid_amount -= $allTranscation->paid_amount;
                $storeFeesInvoiceChield->update();
            }

            if ($transcation->payment_method === 'Wallet') {
                $user = User::find($transcation->user_id);
                $user->wallet_balance += $total_amount;
                $user->update();

                $walletTransaction = new WalletTransaction();
                $walletTransaction->amount = $total_amount;
                $walletTransaction->payment_method = $transcation->payment_method;
                $walletTransaction->user_id = $user->id;
                $walletTransaction->type = 'fees_refund';
                $walletTransaction->status = 'approve';
                $walletTransaction->note = 'Fees Payment';
                $walletTransaction->school_id = auth()->user()->school_id;
                $walletTransaction->academic_id = getAcademicId();
                $walletTransaction->save();
            }

            SmAddIncome::where('fees_collection_id', $id)->delete();
            $transcation->delete();

            // Notification
            $student = SmStudent::with('parents')->find($transcation->student_id);
            sendNotification('Delete Fees Payment', null, 1, 1);
            sendNotification('Delete Fees Payment', null, $student->user_id, 2);
            sendNotification('Delete Fees Payment', null, $student->parents->user_id, 3);

            return response()->json(['message' => 'Delete Sucessfully']);
        } catch (Exception $exception) {
            return response()->json(['message' => 'Error']);
        }
    }

    public function bankPayment()
    {
        $classes = SmClass::where('active_status', 1)
            ->where('academic_id', getAcademicId())
            ->where('school_id', auth()->user()->school_id)
            ->get();

        $feesPayments = FmFeesTransaction::with('feeStudentInfo', 'transcationDetails', 'transcationDetails.transcationFeesType')
            ->where('paid_status', 0)
            ->whereIn('payment_method', ['Bank', 'Cheque'])
            ->where('school_id', auth()->user()->school_id)
            ->where('academic_id', getAcademicId())
            ->get();

        return response()->json(['classes' => $classes, 'feesPayments' => $feesPayments]);
    }

    public function searchBankPayment(BankFeesPayment $bankFeesPayment)
    {
        try {
            $rangeArr = $bankFeesPayment->payment_date ? explode('-', $bankFeesPayment->payment_date) : [date('m/d/Y'), date('m/d/Y')];

            if ($bankFeesPayment->payment_date) {
                $date_from = date('Y-m-d', strtotime(trim($rangeArr[0])));
                $date_to = date('Y-m-d', strtotime(trim($rangeArr[1])));
            }

            $classes = SmClass::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', auth()->user()->school_id)
                ->get();

            $class_id = $bankFeesPayment->class;
            $section_id = $bankFeesPayment->section;
            $class = SmClass::with('classSections')->where('id', $bankFeesPayment->class)->first();

            $student_ids = StudentRecord::when($bankFeesPayment->class, function ($query) use ($bankFeesPayment): void {
                $query->where('class_id', $bankFeesPayment->class);
            })
                ->when($bankFeesPayment->section, function ($query) use ($bankFeesPayment): void {
                    $query->where('section_id', $bankFeesPayment->section);
                })
                ->where('school_id', auth()->user()->school_id)
                ->pluck('student_id')
                ->unique();

            $feesPayments = FmFeesTransaction::with('feeStudentInfo', 'transcationDetails', 'transcationDetails.transcationFeesType')
                ->when($bankFeesPayment->approve_status, function ($query) use ($bankFeesPayment): void {
                    $query->where('paid_status', $bankFeesPayment->approve_status);
                })
                ->when($bankFeesPayment->class, function ($query) use ($bankFeesPayment): void {
                    $query->whereHas('recordDetail', function ($q) use ($bankFeesPayment) {
                        return $q->where('class_id', $bankFeesPayment->class);
                    });
                })
                ->when($bankFeesPayment->section, function ($query) use ($bankFeesPayment): void {
                    $query->whereHas('recordDetail', function ($q) use ($bankFeesPayment) {
                        return $q->where('section_id', $bankFeesPayment->section);
                    });
                })
                ->when($bankFeesPayment->payment_date, function ($query) use ($date_from, $date_to): void {
                    $query->whereDate('created_at', '>=', $date_from)
                        ->whereDate('created_at', '<=', $date_to);
                })
                ->whereIn('student_id', $student_ids)
                ->whereIn('payment_method', ['Bank', 'Cheque'])
                ->where('school_id', auth()->user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            return response()->json(['classes' => $classes, 'feesPayments' => $feesPayments, 'class_id' => $class_id, 'section_id' => $section_id, 'class' => $class]);
        } catch (Exception $exception) {
            return response()->json(['Message' => 'Error']);
        }
    }

    public function approveBankPayment(Request $request)
    {
        try {
            $transcation = $request->transcation_id;
            $total_paid_amount = $request->total_paid_amount ?: null;

            $transcationInfo = FmFeesTransaction::find($transcation);

            $this->addFeesAmount($transcation, $total_paid_amount);

            // Notification
            $student = SmStudent::with('parents')->find($transcationInfo->student_id);
            sendNotification('Approve Bank Payment', null, 1, 1);
            sendNotification('Approve Bank Payment', null, $student->user_id, 2);
            sendNotification('Approve Bank Payment', null, $student->parents->user_id, 3);

            return response()->json(['message' => 'Approve Sucessfully']);
        } catch (Exception $exception) {
            return response()->json(['message' => 'Error']);
        }
    }

    public function rejectBankPayment(Request $request)
    {
        try {
            $transcation = FmFeesTransaction::where('id', $request->transcation_id)->first();
            $fees_transcation = FmFeesTransaction::find($transcation->id);
            $fees_transcation->paid_status = 'reject';
            $fees_transcation->update();

            // Notification
            $student = SmStudent::with('parents')->find($transcation->student_id);
            sendNotification('Approve Bank Payment', null, 1, 1);
            sendNotification('Approve Bank Payment', null, $student->user_id, 2);
            sendNotification('Approve Bank Payment', null, $student->parents->user_id, 3);

            return response()->json(['message' => 'Rejected Sucessfully']);
        } catch (Exception $exception) {
            return response()->json(['message' => 'Error']);
        }
    }

    public function feesInvoiceSettings()
    {
        try {
            $invoiceSettings = FmFeesInvoiceSettings::where('school_id', auth()->user()->school_id)->first();

            return response()->json(['invoiceSettings' => $invoiceSettings]);
        } catch (Exception $exception) {
            return response()->json(['message' => 'Error']);
        }
    }

    public function ajaxFeesInvoiceSettingsUpdate(Request $request)
    {
        try {
            $updateData = FmFeesInvoiceSettings::find($request->id);
            $updateData->invoice_positions = $request->invoicePositions;
            $updateData->uniq_id_start = $request->uniqIdStart;
            $updateData->prefix = $request->prefix;
            $updateData->class_limit = $request->classLimit;
            $updateData->section_limit = $request->sectionLimit;
            $updateData->admission_limit = $request->admissionLimit;
            $updateData->weaver = $request->weaver;
            $updateData->school_id = auth()->user()->school_id;
            $updateData->update();

            return response()->json(['message' => 'Update Sucessfully']);
        } catch (Exception $exception) {
            return response()->json(['message' => 'Error']);
        }
    }

    public function addFeesAmount($transcation_id, $total_paid_amount): void
    {
        $transcation = FmFeesTransaction::find($transcation_id);
        $allTranscations = FmFeesTransactionChield::where('fees_transaction_id', $transcation->id)->get();
        foreach ($allTranscations as $allTranscation) {
            $transcationId = FmFeesTransaction::find($allTranscation->fees_transaction_id);

            $fesInvoiceId = FmFeesInvoiceChield::where('fees_invoice_id', $transcationId->fees_invoice_id)
                ->where('fees_type', $allTranscation->fees_type)
                ->first();

            $storeFeesInvoiceChield = FmFeesInvoiceChield::find($fesInvoiceId->id);
            $storeFeesInvoiceChield->due_amount -= $allTranscation->paid_amount;
            $storeFeesInvoiceChield->paid_amount += $allTranscation->paid_amount;
            $storeFeesInvoiceChield->update();

            // Income
            $payment_method = SmPaymentMethhod::where('method', $transcation->payment_method)->first();
            $income_head = generalSetting();

            $add_income = new SmAddIncome();
            $add_income->name = 'Fees Collect';
            $add_income->date = date('Y-m-d');
            $add_income->amount = $allTranscation->paid_amount;
            $add_income->fees_collection_id = $transcation->fees_invoice_id;
            $add_income->active_status = 1;
            $add_income->income_head_id = $income_head->income_head_id;
            $add_income->payment_method_id = $payment_method->id;
            if ($payment_method->id === 3) {
                $add_income->account_id = $transcation->bank_id;
            }

            $add_income->created_by = Auth()->user()->id;
            $add_income->school_id = auth()->user()->school_id;
            $add_income->academic_id = getAcademicId();
            $add_income->save();

            if ($transcation->payment_method === 'Bank') {
                $bank = SmBankAccount::where('id', $transcation->bank_id)
                    ->where('school_id', auth()->user()->school_id)
                    ->first();

                $after_balance = $bank->current_balance + $total_paid_amount;

                $bank_statement = new SmBankStatement();
                $bank_statement->amount = $allTranscation->paid_amount;
                $bank_statement->after_balance = $after_balance;
                $bank_statement->type = 1;
                $bank_statement->details = 'Fees Payment';
                $bank_statement->payment_date = date('Y-m-d');
                $bank_statement->item_sell_id = $transcation->id;
                $bank_statement->bank_id = $transcation->bank_id;
                $bank_statement->school_id = auth()->user()->school_id;
                $bank_statement->payment_method = $payment_method->id;
                $bank_statement->save();

                $current_balance = SmBankAccount::find($transcation->bank_id);
                $current_balance->current_balance = $after_balance;
                $current_balance->update();
            }

            $fees_transcation = FmFeesTransaction::find($transcation->id);
            $fees_transcation->paid_status = 'approve';
            $fees_transcation->update();
        }

        if ($transcation->add_wallet_money > 0) {
            $user = User::find($transcation->user_id);
            $walletBalance = $user->wallet_balance;
            $user->wallet_balance = $walletBalance + $transcation->add_wallet_money;
            $user->update();

            $walletTransaction = new WalletTransaction();
            $walletTransaction->amount = $transcation->add_wallet_money;
            $walletTransaction->payment_method = $transcation->payment_method;
            $walletTransaction->user_id = $user->id;
            $walletTransaction->type = 'diposit';
            $walletTransaction->status = 'approve';
            $walletTransaction->note = 'Fees Extra Payment Add';
            $walletTransaction->school_id = auth()->user()->school_id;
            $walletTransaction->academic_id = getAcademicId();
            $walletTransaction->save();

            $school = SmSchool::find($user->school_id);
            $compact['full_name'] = $user->full_name;
            $compact['method'] = $transcation->payment_method;
            $compact['create_date'] = date('Y-m-d');
            $compact['school_name'] = $school->school_name;
            $compact['current_balance'] = $user->wallet_balance;
            $compact['add_balance'] = $transcation->add_wallet_money;
            $compact['previous_balance'] = $user->wallet_balance - $transcation->add_wallet_money;

            @send_mail($user->email, $user->full_name, 'fees_extra_amount_add', $compact);

            sendNotification($user->id, null, null, $user->role_id);
        }
    }

    private function invStore($request): void
    {
        $fmFeesInvoice = new FmFeesInvoice();
        $fmFeesInvoice->class_id = $request->class;
        $fmFeesInvoice->create_date = date('Y-m-d', strtotime($request->create_date));
        $fmFeesInvoice->due_date = date('Y-m-d', strtotime($request->due_date));
        $fmFeesInvoice->payment_status = $request->payment_status;
        $fmFeesInvoice->payment_method = $request->payment_method;
        $fmFeesInvoice->bank_id = $request->bank;
        $fmFeesInvoice->student_id = $request->student;
        $fmFeesInvoice->record_id = $request->record_id;
        $fmFeesInvoice->school_id = auth()->user()->school_id;
        $fmFeesInvoice->academic_id = getAcademicId();
        $fmFeesInvoice->save();
        $fmFeesInvoice->invoice_id = feesInvoiceNumber($fmFeesInvoice);
        $fmFeesInvoice->save();

        if ($request->paid_amount > 0) {
            $fmFeesTransaction = new FmFeesTransaction();
            $fmFeesTransaction->fees_invoice_id = $fmFeesInvoice->id;
            $fmFeesTransaction->payment_method = $request->payment_method;
            $fmFeesTransaction->bank_id = $request->bank;
            $fmFeesTransaction->student_id = $request->student;
            $fmFeesTransaction->record_id = $request->record_id;
            $fmFeesTransaction->user_id = auth()->user()->id;
            $fmFeesTransaction->paid_status = 'approve';
            $fmFeesTransaction->school_id = auth()->user()->school_id;
            $fmFeesTransaction->academic_id = getAcademicId();
            $fmFeesTransaction->save();
        }

        foreach ($request->feesType as $key => $type) {
            $storeFeesInvoiceChield = new FmFeesInvoiceChield();
            $storeFeesInvoiceChield->fees_invoice_id = $fmFeesInvoice->id;
            $storeFeesInvoiceChield->fees_type = $type;
            $storeFeesInvoiceChield->amount = $request->amount[$key];
            $storeFeesInvoiceChield->weaver = $request->weaver ? $request->weaver[$key] : null;
            $storeFeesInvoiceChield->sub_total = $request->sub_total[$key];
            $storeFeesInvoiceChield->note = $request->note ? $request->note[$key] : null;
            if ($request->paid_amount > 0) {
                $storeFeesInvoiceChield->paid_amount = $request->paid_amount[$key];
                $storeFeesInvoiceChield->due_amount = $request->sub_total[$key] - $request->paid_amount[$key];
            } else {
                $storeFeesInvoiceChield->due_amount = $request->sub_total[$key];
            }

            $storeFeesInvoiceChield->school_id = auth()->user()->school_id;
            $storeFeesInvoiceChield->academic_id = getAcademicId();
            $storeFeesInvoiceChield->save();

            if ($request->paid_amount > 0) {
                $storeTransactionChield = new FmFeesTransactionChield();
                $storeTransactionChield->fees_transaction_id = $fmFeesTransaction->id;
                $storeTransactionChield->fees_type = $type;
                $storeTransactionChield->weaver = $request->weaver[$key];
                $storeTransactionChield->paid_amount = $request->paid_amount[$key];
                $storeTransactionChield->note = $request->note[$key];
                $storeTransactionChield->school_id = auth()->user()->school_id;
                $storeTransactionChield->academic_id = getAcademicId();
                $storeTransactionChield->save();

                // Income
                addIncome($request->payment_method, 'Fees Collect', $request->paid_amount[$key], $fmFeesTransaction->id, auth()->user()->id, null);

                // Bank
                if ($request->payment_method === 'Bank') {
                    $payment_method = SmPaymentMethhod::where('method', $request->payment_method)->first();
                    $bank = SmBankAccount::where('id', $request->bank)
                        ->where('school_id', auth()->user()->school_id)
                        ->first();
                    $after_balance = $bank->current_balance + $request->paid_amount[$key];

                    $bank_statement = new SmBankStatement();
                    $bank_statement->amount = $request->paid_amount[$key];
                    $bank_statement->after_balance = $after_balance;
                    $bank_statement->type = 1;
                    $bank_statement->details = 'Fees Payment';
                    $bank_statement->item_sell_id = $fmFeesTransaction->id;
                    $bank_statement->payment_date = date('Y-m-d');
                    $bank_statement->bank_id = $request->bank;
                    $bank_statement->school_id = auth()->user()->school_id;
                    $bank_statement->payment_method = $payment_method->id;
                    $bank_statement->save();

                    $current_balance = SmBankAccount::find($request->bank);
                    $current_balance->current_balance = $after_balance;
                    $current_balance->update();
                }
            }

            $storeWeaver = new FmFeesWeaver();
            $storeWeaver->fees_invoice_id = $fmFeesInvoice->id;
            $storeWeaver->fees_type = $type;
            $storeWeaver->student_id = $request->student;
            $storeWeaver->weaver = $request->weaver ? $request->weaver[$key] : null;
            $storeWeaver->note = $request->note ? $request->note[$key] : null;
            $storeWeaver->school_id = auth()->user()->school_id;
            $storeWeaver->academic_id = getAcademicId();
            $storeWeaver->save();
        }
    }
}
