<?php

namespace Modules\Fees\Http\Controllers;

use Exception;
use DataTables;
use App\SmClass;
use App\SmSchool;
use App\SmStudent;
use App\Models\User;
use App\SmAddIncome;
use App\SmBankAccount;
use App\SmBankStatement;
use App\SmPaymentMethhod;
use App\SmGeneralSettings;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use Illuminate\Validation\Rule;
use App\SmPaymentGatewaySetting;
use App\Traits\NotificationSend;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\Fees\Entities\FmFeesType;
use Modules\Fees\Entities\FmFeesGroup;
use Modules\Fees\Entities\FmFeesWeaver;
use Modules\Fees\Entities\FmFeesInvoice;
use Illuminate\Support\Facades\Validator;
use Modules\Fees\Entities\FmFeesTransaction;
use Modules\Fees\Entities\FmFeesInvoiceChield;
use Modules\Wallet\Entities\WalletTransaction;
use Modules\Fees\Http\Requests\BankFeesPayment;
use Modules\Fees\Entities\FmFeesInvoiceSettings;
use Modules\Fees\Entities\FmFeesTransactionChield;

class FeesController extends Controller
{
    use NotificationSend;

    public function feesGroup()
    {
        $feesGroups = FmFeesGroup::where('school_id', Auth::user()->school_id)
            ->where('academic_id', getAcademicId())
            ->select(['name', 'id', 'description'])
            ->get();

        return view('fees::feesGroup', ['feesGroups' => $feesGroups]);
    }

    public function feesGroupStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:100', Rule::unique('fm_fees_groups', 'name')->where('school_id', auth()->user()->school_id)->where('school_id', getAcademicId())],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $fmFeesGroup = new FmFeesGroup();
            $fmFeesGroup->name = $request->name;
            $fmFeesGroup->description = $request->description;
            $fmFeesGroup->school_id = Auth::user()->school_id;
            $fmFeesGroup->academic_id = getAcademicId();
            $fmFeesGroup->save();

            Toastr::success('Save Successful', 'Success');

            return redirect()->route('fees.fees-group');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function feesGroupEdit($id)
    {
        try {
            if (checkAdmin() == true) {
                $feesGroup = FmFeesGroup::find($id);
            } else {
                $feesGroup = FmFeesGroup::where('id', $id)->where('school_id', Auth::user()->school_id)->first();
            }

            $feesGroups = FmFeesGroup::where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            return view('fees::feesGroup', ['feesGroup' => $feesGroup, 'feesGroups' => $feesGroups]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function feesGroupUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100',
        ]);

        $ifExistes = FmFeesGroup::where('name', $request->name)
            ->where('school_id', Auth::user()->school_id)
            ->where('id', '!=', $request->id)
            ->where('academic_id', getAcademicId())
            ->first();
        if ($ifExistes) {
            Toastr::Warning('Duplicate Name Found!', 'Warning');

            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            if (checkAdmin() == true) {
                $feesGroup = FmFeesGroup::find($request->id);
            } else {
                $feesGroup = FmFeesGroup::where('id', $request->id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
            }

            $feesGroup->name = $request->name;
            $feesGroup->description = $request->description;
            $feesGroup->academic_id = getAcademicId();
            $feesGroup->save();

            Toastr::success('Update Successful', 'Success');

            return redirect()->route('fees.fees-group');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function feesGroupDelete(Request $request)
    {
        try {
            $groupData = FmFeesGroup::where('id',$request->id)->first();
            $checkExistsData = FmFeesType::where('fees_group_id', $groupData->id)->first();
            if (! $checkExistsData) {
                if (checkAdmin() == true) {
                    FmFeesGroup::destroy($request->id);
                } else {
                    FmFeesGroup::where('id', $request->id)
                        ->where('school_id', auth()->user()->school_id)
                        ->delete();
                }

                Toastr::success('Delete Successful', 'Success');

                return redirect()->route('fees.fees-group');
            }

            Toastr::warning('This Data Already Used In Fees Type Please Remove Those Data First', 'Warning');

            return redirect()->back();

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function feesType()
    {
        $feesGroups = FmFeesGroup::where('school_id', Auth::user()->school_id)
            ->where('academic_id', getAcademicId())
            ->get();

        $feesTypes = FmFeesType::where('type', 'fees')
            ->where('school_id', Auth::user()->school_id)
            ->where('academic_id', getAcademicId())
            ->get();

        return view('fees::feesType', ['feesGroups' => $feesGroups, 'feesTypes' => $feesTypes]);
    }

    public function feesTypeStore(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'fees_group' => ['required'],
            'name' => ['required', 'max:50', Rule::unique('fm_fees_types', 'name')->where('fees_group_id', $request->fees_group)->where('school_id', auth()->user()->school_id)],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $fmFeesType = new FmFeesType();
            $fmFeesType->name = $request->name;
            $fmFeesType->fees_group_id = $request->fees_group;
            $fmFeesType->description = $request->description;
            $fmFeesType->school_id = Auth::user()->school_id;
            $fmFeesType->academic_id = getAcademicId();
            $fmFeesType->save();

            Toastr::success('Save Successful', 'Success');

            return redirect()->route('fees.fees-type');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function feesTypeEdit($id)
    {
        try {
            if (checkAdmin() == true) {
                $feesType = FmFeesType::find($id);
            } else {
                $feesType = FmFeesType::where('id', $id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
            }

            $feesGroups = FmFeesGroup::where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $feesTypes = FmFeesType::where('type', 'fees')
                ->where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            return view('fees::feesType', ['feesGroups' => $feesGroups, 'feesTypes' => $feesTypes, 'feesType' => $feesType]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function feesTypeUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:50', Rule::unique('fm_fees_types', 'name')->where('fees_group_id', $request->fees_group)->where('school_id', auth()->user()->school_id)->ignore($request->id)],
        ]);

        $ifExistes = FmFeesType::where('id', '!=', $request->id)
            ->where('type', 'fees')
            ->where('school_id', Auth::user()->school_id)
            ->where('name', $request->name)
            ->where('fees_group_id', $request->fees_group)
            ->where('academic_id', getAcademicId())
            ->first();

        if ($ifExistes) {
            Toastr::Warning('Duplicate Name Found!', 'Warning');

            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            if (checkAdmin() == true) {
                $feesType = FmFeesType::find($request->id);
            } else {
                $feesType = FmFeesType::where('type', 'fees')
                    ->where('id', $request->id)
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
            }

            $feesType->name = $request->name;
            $feesType->fees_group_id = $request->fees_group;
            $feesType->description = $request->description;
            $feesType->save();

            Toastr::success('Update Successful', 'Success');

            return redirect()->route('fees.fees-type');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function feesTypeDelete(Request $request)
    {
        try {
            $checkExistsData = FmFeesInvoiceChield::where('fees_type', $request->id)->first();

            if (! $checkExistsData) {
                if (checkAdmin() == true) {
                    FmFeesType::find($request->id)->delete();
                } else {
                    FmFeesType::where('id', $request->id)
                        ->where('school_id', Auth::user()->school_id)
                        ->delete();
                }
                Toastr::success('Delete Successful', 'Success');
                return redirect()->route('fees.fees-type');
            }

            $msg = 'This Data Already Used In Fees Invoice Please Remove Those Data First';
            Toastr::warning($msg, 'Warning');

            return redirect()->back();

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function feesInvoiceList()
    {
        return view('fees::feesInvoice.feesInvoiceList');
    }

    public function feesInvoice()
    {
        try {
            $classes = SmClass::where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $feesGroups = FmFeesGroup::where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $feesTypes = FmFeesType::where('type', 'fees')
                ->where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $paymentMethods = SmPaymentMethhod::whereIn('method', ['Cash', 'Cheque', 'Bank'])
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $bankAccounts = SmBankAccount::where('school_id', Auth::user()->school_id)
                ->get();

            $invoiceSettings = FmFeesInvoiceSettings::where('school_id', Auth::user()->school_id)->first();

            if (! $invoiceSettings) {
                $invoiceSettings = new FmFeesInvoiceSettings();
                $invoiceSettings->invoice_positions = '[{"id":"prefix","text":"prefix"},{"id":"admission_no","text":"Admission No"},{"id":"class","text":"Class"},{"id":"section","text":"Section"}]';
                $invoiceSettings->uniq_id_start = '0011';
                $invoiceSettings->prefix = 'infixEdu';
                $invoiceSettings->class_limit = 3;
                $invoiceSettings->section_limit = 1;
                $invoiceSettings->admission_limit = 3;
                $invoiceSettings->weaver = 'amount';
                $invoiceSettings->school_id = auth()->user()->school_id;
                $invoiceSettings->save();
            }

            return view('fees::feesInvoice.feesInvoice', ['classes' => $classes, 'feesGroups' => $feesGroups, 'feesTypes' => $feesTypes, 'paymentMethods' => $paymentMethods, 'bankAccounts' => $bankAccounts, 'invoiceSettings' => $invoiceSettings]);

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function feesInvoiceStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class' => 'required',
            'student' => 'required',
            'create_date' => 'required',
            'due_date' => 'required|date|after:create_date',
            'payment_status' => 'required',
            'payment_method' => 'required_if:payment_status,partial|required_if:payment_status,full',
            'bank' => 'required_if:payment_method,Bank',
            'fees_type' => 'required',
        ]);
        if ($validator->fails()) {

            return redirect()->back()->withErrors($validator)->withInput();
        }
        
       

        if ($request->payment_status == 'partial' && $request->total_paid_amount == null) {
            Toastr::warning('Paid Amount Can Not Be Blank', 'Failed');

            return redirect()->back();
        }

        try {
            
            $feesExtendedController = new FeesExtendedController();
            $payment_method = $request->payment_method ?? '';
            if ($request->student != 'all_student') {
                
                $student = StudentRecord::find($request->student);
                if ($request->groups) {
                    
                    if (empty($request->singleInvoice)) {
                        $feesType = [];
                        $amount = [];
                        $weaver = [];
                        $sub_total = [];
                        $note = [];
                        $paid_amount = [];
                    }

                    foreach ($request->groups as $group) {
                        
                        if ($request->singleInvoice == 1) {
                            $feesType = [];
                            $amount = [];
                            $weaver = [];
                            $sub_total = [];
                            $note = [];
                            $paid_amount = [];
                        }

                        $feesType[] = gv($group, 'feesType');
                        $amount[] = gv($group, 'amount');
                        $weaver[] = gv($group, 'weaver');
                        $sub_total[] = gv($group, 'sub_total');
                        $note[] = gv($group, 'note');
                        $paid_amount[] = gv($group, 'paid_amount');

                        if ($request->singleInvoice == 1) {
                            $feesCarry = feesCarryForward($student->id, $feesType, $amount, $sub_total);
                            if ($feesCarry != null && $feesCarry != []) {
                                if ($feesCarry['type'] == 'due') {
                                    $feesType = $feesCarry['feesTypes'];
                                    $amount = $feesCarry['amount'];
                                } elseif ($feesCarry['type'] == 'full_paid_add_xtra_amount') {
                                    $paid_amount = $feesCarry['paymentAmount'];
                                    $payment_method = $feesCarry['paymentMethod'];
                                } elseif ($feesCarry['type'] == 'multi_payment') {
                                    $paid_amount = $feesCarry['paidFeesAmount'];
                                    $payment_method = $feesCarry['paymentMethod'];
                                }
                            }

                            $feesExtendedController->invStore($request->merge(['student' => $student->student_id,
                                'record_id' => $student->id,
                                'feesType' => $feesType,
                                'amount' => $amount,
                                'weaver' => $weaver,
                                'sub_total' => $sub_total,
                                'note' => $note,
                                'paid_amount' => $paid_amount,
                                'payment_method' => $payment_method,
                            ]));
                        }
                    }

                    if (!$request->singleInvoice) {
                        
                        $feesCarry = feesCarryForward($request->student, $feesType, $amount, $sub_total);
                        if ($feesCarry != null && $feesCarry != []) {
                            if ($feesCarry['type'] == 'due') {
                                $feesType = $feesCarry['feesTypes'];
                                $amount = $feesCarry['amount'];
                                $sub_total = $feesCarry['sub_total'];
                            } elseif ($feesCarry['type'] == 'full_paid_add_xtra_amount') {
                                $paid_amount = $feesCarry['paymentAmount'];
                                $payment_method = $feesCarry['paymentMethod'];
                            } elseif ($feesCarry['type'] == 'multi_payment') {
                                $paid_amount = $feesCarry['paidFeesAmount'];
                                $payment_method = $feesCarry['paymentMethod'];
                            }
                        }
                        
                       
                        
                       
                        $feesExtendedController->invStore($request->merge(['student' => $student->student_id,
                            'record_id' => $student->id,
                            'feesType' => $feesType,
                            'amount' => $amount,
                            'weaver' => $weaver,
                            'sub_total' => $sub_total,
                            'note' => $note,
                            'paid_amount' => $paid_amount,
                            'payment_method' => $payment_method,
                        ]));
                    }
                }

                if ($request->types) {
                    if (empty($request->singleInvoice)) {
                        $tfeesType = [];
                        $tamount = [];
                        $tweaver = [];
                        $tsub_total = [];
                        $tnote = [];
                        $tpaid_amount = [];
                    }

                    foreach ($request->types as $type) {
                        if ($request->singleInvoice == 1) {
                            $tfeesType = []; 
                            $tamount = [];
                            $tweaver = [];
                            $tsub_total = [];
                            $tnote = [];
                            $tpaid_amount = [];
                        }

                        $tfeesType[] = gv($type, 'feesType');
                        $tamount[] = gv($type, 'amount');
                        $tweaver[] = gv($type, 'weaver');
                        $tsub_total[] = gv($type, 'sub_total');
                        $tnote[] = gv($type, 'note');
                        $tpaid_amount[] = gv($type, 'paid_amount');
                        if ($request->singleInvoice == 1) {
                            $feesCarry = feesCarryForward($student->id, $tfeesType, $tamount, $tsub_total);
                            if ($feesCarry != null && $feesCarry != []) {
                                if ($feesCarry['type'] == 'due') {
                                    $tfeesType = $feesCarry['feesTypes'];
                                    $tamount = $feesCarry['amount'];
                                } elseif ($feesCarry['type'] == 'full_paid_add_xtra_amount') {
                                    $tpaid_amount = $feesCarry['paymentAmount'];
                                    $payment_method = $feesCarry['paymentMethod'];
                                } elseif ($feesCarry['type'] == 'multi_payment') {
                                    $tpaid_amount = $feesCarry['paidFeesAmount'];
                                    $payment_method = $feesCarry['paymentMethod'];
                                }
                            }

                            $feesExtendedController->invStore($request->merge(
                                [
                                    'student' => $student->student_id,
                                    'record_id' => $student->id,
                                    'feesType' => $tfeesType,
                                    'amount' => $tamount,
                                    'weaver' => $tweaver,
                                    'sub_total' => $tsub_total,
                                    'note' => $tnote,
                                    'paid_amount' => $tpaid_amount,
                                    'payment_method' => $payment_method,
                                ]));
                        }
                    }

                    if (empty($request->singleInvoice)) {
                        $feesCarry = feesCarryForward($request->student, $tfeesType, $tamount, $tsub_total);
                        if ($feesCarry != null && $feesCarry != []) {
                            if ($feesCarry['type'] == 'due') {
                                $tfeesType = $feesCarry['feesTypes'];
                                $tamount = $feesCarry['amount'];
                                $tsub_total = $feesCarry['sub_total'];
                            } elseif ($feesCarry['type'] == 'full_paid_add_xtra_amount') {
                                $tpaid_amount = $feesCarry['paymentAmount'];
                                $payment_method = $feesCarry['paymentMethod'];
                            } elseif ($feesCarry['type'] == 'multi_payment') {
                                $tpaid_amount = $feesCarry['paidFeesAmount'];
                                $payment_method = $feesCarry['paymentMethod'];
                            }
                        }

                        $feesExtendedController->invStore($request->merge(['student' => $student->student_id,
                            'record_id' => $student->id,
                            'feesType' => $tfeesType,
                            'amount' => $tamount,
                            'weaver' => $tweaver,
                            'sub_total' => $tsub_total,
                            'note' => $tnote,
                            'paid_amount' => $tpaid_amount,
                            'payment_method' => $payment_method,
                        ]));
                    }
                }

                // Notification

                $students = SmStudent::with('parents')->find($student->student_id);
                sendNotification('Fees Assign', null, $students->user_id, 2);
                sendNotification('Fees Assign', null, $students->parents->user_id, 3);

                $student_user_id = SmStudent::find($student->student_id)->user_id;
                $data['student_name'] = $student->studentDetail->full_name;

                if ($request->types) {
                    $data['fees'] = is_array($tsub_total) ? (string) $tsub_total[0] : (string) $tsub_total;
                } elseif ($request->groups) {
                    $data['fees'] = is_array($sub_total) ? (string) $sub_total[0] : (string) $sub_total;
                }

                try {
                    $this->sent_notifications('Fees_Assign', [$student_user_id], $data, ['Student', 'Parent']);
                } catch (Exception $e) {
                    Log::info($e->getMessage());
                }

            } else {
               
                $allStudents = StudentRecord::with(['studentDetail' => function ($q) {
                    return $q->where('active_status', 1);
                }, 'studentDetail.parents'])
                ->whereHas('studentDetail', function ($q) {
                    return $q->where('active_status', 1);
                })
                ->when(shiftEnable(), function ($query) use ($request) {
                    $query->where('shift_id', $request->shift);
                })
                ->where('class_id', $request->class)
                ->where('school_id', Auth::user()->school_id)
                ->where('is_promote', 0)
                ->where('academic_id', getAcademicId())
                ->get();
                foreach ($allStudents as $allStudent) {
                    if ($request->groups) {
                        if (empty($request->singleInvoice)) {
                            $feesType = [];
                            $amount = [];
                            $weaver = [];
                            $sub_total = [];
                            $note = [];
                            $paid_amount = [];
                        }

                        foreach ($request->groups as $group) {
                            if ($request->singleInvoice == 1) {
                                $feesType = [];
                                $amount = [];
                                $weaver = [];
                                $sub_total = [];
                                $note = [];
                                $paid_amount = [];
                            }

                            $feesType[] = gv($group, 'feesType');
                            $amount[] = gv($group, 'amount', 0);
                            $weaver[] = gv($group, 'weaver', 0);
                            $sub_total[] = gv($group, 'sub_total', 0);
                            $note[] = gv($group, 'note');
                            $paid_amount[] = gv($group, 'paid_amount');

                            if ($request->singleInvoice == 1) {
                                $feesCarry = feesCarryForward($allStudent->id, $feesType, $amount, $sub_total);
                                if ($feesCarry != null && $feesCarry != []) {
                                    if ($feesCarry['type'] == 'due') {
                                        $feesType = $feesCarry['feesTypes'];
                                        $amount = $feesCarry['amount'];
                                    } elseif ($feesCarry['type'] == 'full_paid_add_xtra_amount') {
                                        $paid_amount = $feesCarry['paymentAmount'];
                                        $payment_method = $feesCarry['paymentMethod'];
                                    } elseif ($feesCarry['type'] == 'multi_payment') {
                                        $paid_amount = $feesCarry['paidFeesAmount'];
                                        $payment_method = $feesCarry['paymentMethod'];
                                    }
                                }

                               $inv_store =  $feesExtendedController->invStore($request->merge(['student' => $allStudent->student_id,
                                    'record_id' => $allStudent->id,
                                    'feesType' => $feesType,
                                    'amount' => $amount,
                                    'weaver' => $weaver,
                                    'sub_total' => $sub_total,
                                    'note' => $note,
                                    'paid_amount' => $paid_amount,
                                    'payment_method' => $payment_method,
                                ]));
                            }

                        }

                        if (empty($request->singleInvoice)) {
                            $feesCarry = feesCarryForward($allStudent->id, $feesType, $amount, $sub_total);
                            if ($feesCarry != null && $feesCarry != []) {
                                if ($feesCarry['type'] == 'due') {
                                    $feesType = $feesCarry['feesTypes'];
                                    $amount = $feesCarry['amount'];
                                } elseif ($feesCarry['type'] == 'full_paid_add_xtra_amount') {
                                    $paid_amount = $feesCarry['paymentAmount'];
                                    $payment_method = $feesCarry['paymentMethod'];
                                } elseif ($feesCarry['type'] == 'multi_payment') {
                                    $paid_amount = $feesCarry['paidFeesAmount'];
                                    $payment_method = $feesCarry['paymentMethod'];
                                }
                            }

                          $inv_store =   $feesExtendedController->invStore($request->merge(['student' => $allStudent->student_id,
                                'record_id' => $allStudent->id,
                                'feesType' => $feesType,
                                'amount' => $amount,
                                'weaver' => $weaver,
                                'sub_total' => $sub_total,
                                'note' => $note,
                                'paid_amount' => $paid_amount,
                                'payment_method' => $payment_method,
                            ]));
                          
                        }
                    }

                    $tsub_total = 0;
                    

                    if ($request->types) {
                        foreach ($request->types as $type) {
                            $tfeesType = [];
                            $tamount = [];
                            $tweaver = [];
                            $tsub_total = [];
                            $tnote = [];
                            $tpaid_amount = [];

                            $tfeesType[] = gv($type, 'feesType');
                            $tamount[] = gv($type, 'amount');
                            $tweaver[] = gv($type, 'weaver');
                            $tsub_total[] = gv($type, 'sub_total');
                            $tnote[] = gv($type, 'note');
                            $tpaid_amount[] = gv($type, 'paid_amount');
                            $feesCarry = feesCarryForward($allStudent->id, $tfeesType, $tamount, $tsub_total);
                            if($feesCarry){
                                if($feesCarry['type'] == 'due'){
                                    $tfeesType = $feesCarry['feesTypes'];
                                    $tamount = $feesCarry['amount'];
                                } elseif ($feesCarry['type'] == 'full_paid_add_xtra_amount') {
                                    $tpaid_amount = $feesCarry['paymentAmount'];
                                    $payment_method = $feesCarry['paymentMethod'];
                                } elseif ($feesCarry['type'] == 'multi_payment') {
                                    $tpaid_amount = $feesCarry['paidFeesAmount'];
                                    $payment_method = $feesCarry['paymentMethod'];
                                }
                            }

                            $feesExtendedController->invStore($request->merge(['student' => $allStudent->student_id,
                                'record_id' => $allStudent->id,
                                'feesType' => $tfeesType,
                                'amount' => $tamount,
                                'weaver' => $tweaver,
                                'sub_total' => $tsub_total,
                                'note' => $tnote,
                                'paid_amount' => $tpaid_amount,
                                'payment_method' => $payment_method,
                            ]));
                        }
                    }
                    //Notification
                    sendNotification("Fees Assign", null, $allStudent->studentDetail->user_id, 2);
                    sendNotification("Fees Assign", null, $allStudent->studentDetail->parents->user_id, 3);
                    
                    $student_user_id      = SmStudent::find($allStudent->student_id)->user_id;
                    $data['student_name'] = $allStudent->studentDetail->full_name;
                    $data['fees']         = isset($tsub_total[0]) &&  is_array($tsub_total) ? (string) $tsub_total[0] : $tsub_total;
                    
                    try{
                        $this->sent_notifications('Fees_Assign', [$student_user_id], $data, ['Student', 'Parent']);
                    } catch (Exception $e) {
                        Log::info($e->getMessage());
                    }

                }
            }

            sendNotification('Fees Assign', null, 1, 1);
            Toastr::success('Store Successful', 'Success');

            return redirect()->route('fees.fees-invoice');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function feesInvoiceEdit($id)
    {
        try {
            // View Start
            $classes = SmClass::where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $feesGroups = FmFeesGroup::where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $feesTypes = FmFeesType::where('type', 'fees')
                ->where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $paymentMethods = SmPaymentMethhod::whereIn('method', ['Cash', 'Cheque', 'Bank'])
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $bankAccounts = SmBankAccount::where('school_id', Auth::user()->school_id)
                ->get();
            // View End

            $invoiceSettings = FmFeesInvoiceSettings::where('school_id', Auth::user()->school_id)->first();

            $invoiceInfo = FmFeesInvoice::find($id);
            $invoiceDetails = FmFeesInvoiceChield::where('fees_invoice_id', $invoiceInfo->id)
                ->where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $students = StudentRecord::where('id', $invoiceInfo->record_id)
                    ->when(shiftEnable(), function ($query) use ($invoiceInfo) {
                        $query->where('shift_id', $invoiceInfo->shift_id);
                    })
                    ->where('class_id', $invoiceInfo->class_id)
                    ->where('school_id', Auth::user()->school_id)
                    ->where('academic_id', getAcademicId())
                    ->get();

            return view('fees::feesInvoice.feesInvoice', ['classes' => $classes, 'feesGroups' => $feesGroups, 'feesTypes' => $feesTypes, 'paymentMethods' => $paymentMethods, 'bankAccounts' => $bankAccounts, 'invoiceSettings' => $invoiceSettings, 'invoiceInfo' => $invoiceInfo, 'invoiceDetails' => $invoiceDetails, 'students' => $students]);

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function feesInvoiceUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class' => 'required',
            'student' => 'required',
            'create_date' => 'required',
            'due_date' => 'required',
            'payment_status' => 'required',
            'payment_method' => 'required_if:payment_status,partial|required_if:payment_status,full',
            'bank' => 'required_if:payment_method,Bank',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $student = StudentRecord::find($request->student);

            $storeFeesInvoice = FmFeesInvoice::find($request->id);
            $storeFeesInvoice->shift_id = shiftEnable() ? $request->shift : null;
            $storeFeesInvoice->class_id = $request->class;
            $storeFeesInvoice->create_date = date('Y-m-d', strtotime($request->create_date));
            $storeFeesInvoice->due_date = date('Y-m-d', strtotime($request->due_date));
            $storeFeesInvoice->payment_status = $request->payment_status;
            $storeFeesInvoice->bank_id = $request->bank;
            $storeFeesInvoice->student_id = $student->student_id;
            $storeFeesInvoice->record_id = $request->student;
            $storeFeesInvoice->school_id = Auth::user()->school_id;
            $storeFeesInvoice->academic_id = getAcademicId();
            $storeFeesInvoice->update();

            FmFeesInvoiceChield::where('fees_invoice_id', $request->id)->delete();
            FmFeesWeaver::where('fees_invoice_id', $storeFeesInvoice->id)->delete();

            $feesType = $request->feesType;
            $amount = $request->amount;
            $weaver = $request->weaver;
            $sub_total = $request->sub_total;
            $note = $request->note;
            if ($request->types) {
                foreach ($request->types as $type) {
                    $feesType[] = $type['feesType'];
                    $amount[] = $type['amount'];
                    $weaver[] = $type['weaver'];
                    $sub_total[] = $type['sub_total'];
                    $note[] = $type['note'];
                }
            }

            foreach ($feesType as $key => $type) {
                $storeFeesInvoiceChield = new FmFeesInvoiceChield();
                $storeFeesInvoiceChield->fees_invoice_id = $storeFeesInvoice->id;
                $storeFeesInvoiceChield->fees_type = $type;
                $storeFeesInvoiceChield->amount = $amount[$key];
                $storeFeesInvoiceChield->weaver = $weaver[$key];
                $storeFeesInvoiceChield->sub_total = $sub_total[$key];
                $storeFeesInvoiceChield->due_amount = $sub_total[$key];
                $storeFeesInvoiceChield->note = $note[$key];

                if ($request->paid_amount) {
                    $storeFeesInvoiceChield->paid_amount = $request->paid_amount[$key];
                }

                $storeFeesInvoiceChield->school_id = Auth::user()->school_id;
                $storeFeesInvoiceChield->academic_id = getAcademicId();
                $storeFeesInvoiceChield->save();

                $storeWeaver = new FmFeesWeaver();
                $storeWeaver->fees_invoice_id = $storeFeesInvoice->id;
                $storeWeaver->fees_type = $type;
                $storeWeaver->student_id = $request->student;
                $storeWeaver->weaver = $weaver[$key];
                $storeWeaver->note = $note[$key];
                $storeWeaver->school_id = Auth::user()->school_id;
                $storeWeaver->academic_id = getAcademicId();
                $storeWeaver->save();
            }

            // Notification
            $student = SmStudent::with('parents')->find($storeFeesInvoice->student_id);
            sendNotification('Fees Assign Update', null, $student->user_id, 2);
            sendNotification('Fees Assign Update', null, $student->parents->user_id, 3);
            Toastr::success('Update Successful', 'Success');

            return redirect()->route('fees.fees-invoice-list');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function feesInvoiceView($id, $state)
    {
        $generalSetting = SmGeneralSettings::where('school_id', Auth::user()->school_id)->first();
        $invoiceInfo = FmFeesInvoice::find($id);

        $invoiceDetails = FmFeesInvoiceChield::where('fees_invoice_id', $invoiceInfo->id)
            ->where('school_id', Auth::user()->school_id)
            ->where('academic_id', getAcademicId())
            ->get();
        $banks = SmBankAccount::where('active_status', '=', 1)
            ->where('school_id', Auth::user()->school_id)
            ->get();

        if ($state == 'view') {
            return view('fees::feesInvoice.feesInvoiceView', ['generalSetting' => $generalSetting, 'invoiceInfo' => $invoiceInfo, 'invoiceDetails' => $invoiceDetails, 'banks' => $banks]);
        }

        return view('fees::feesInvoice.feesInvoicePrint', ['invoiceInfo' => $invoiceInfo, 'invoiceDetails' => $invoiceDetails, 'banks' => $banks]);

    }

    public function feesInvoiceDelete(Request $request)
    {
        try {
            $invoiceDelete = FmFeesInvoice::find($request->feesInvoiceId)->delete();
            if ($invoiceDelete) {
                FmFeesInvoiceChield::where('fees_invoice_id', $request->id)->delete();
            }

            Toastr::success('Delete Successful', 'Success');

            return redirect()->route('fees.fees-invoice-list');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }

    }

    public function addFeesPayment($id)
    {
        try {
            $classes = SmClass::where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $feesGroups = FmFeesGroup::where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $feesTypes = FmFeesType::where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $feesTypes = FmFeesType::where('type', 'fees')
                ->where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $paymentMethods = SmPaymentMethhod::whereIn('method', ['Cash', 'Cheque', 'Bank'])
                ->where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $bankAccounts = SmBankAccount::where('school_id', Auth::user()->school_id)
                ->get();

            $invoiceInfo = FmFeesInvoice::find($id);
            $invoiceDetails = FmFeesInvoiceChield::where('fees_invoice_id', $invoiceInfo->id)
                ->where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $stripe_info = SmPaymentGatewaySetting::where('gateway_name', 'stripe')
                ->where('school_id', Auth::user()->school_id)
                ->first();

            return view('fees::addFessPayment', ['classes' => $classes, 'feesGroups' => $feesGroups, 'feesTypes' => $feesTypes, 'paymentMethods' => $paymentMethods, 'bankAccounts' => $bankAccounts, 'invoiceInfo' => $invoiceInfo, 'invoiceDetails' => $invoiceDetails, 'stripe_info' => $stripe_info]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

    }

    public function feesPaymentStore(Request $request)
    {
       
        if ($request->total_paid_amount == null) {
            Toastr::warning('Paid Amount Can Not Be Blank', 'Failed');
            return redirect()->back();
        }

        $validator = Validator::make($request->all(), [
            'payment_method' => 'required',
            'bank' => 'required_if:payment_method,Bank',
            'file' => 'mimes:jpg,jpeg,png,pdf',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        try {
            $destination = 'public/uploads/student/document/';
            $file = fileUpload($request->file('file'), $destination);
            $record = StudentRecord::find($request->record_id);
            $student = SmStudent::with('parents')->find($record->student_id);
            if ($request->add_wallet > 0) {
                $user = User::find($student->user_id);
                $walletBalance = $user->wallet_balance;
                $user->wallet_balance = $walletBalance + $request->add_wallet;
                $user->update();
                $walletTransaction = new WalletTransaction();
                $walletTransaction->amount = $request->add_wallet;
                $walletTransaction->payment_method = $request->payment_method;
                $walletTransaction->user_id = $user->id;
                $walletTransaction->type = 'diposit';
                $walletTransaction->status = 'approve';
                $walletTransaction->note = 'Fees Extra Payment Add';
                $walletTransaction->school_id = Auth::user()->school_id;
                $walletTransaction->academic_id = getAcademicId();
                $walletTransaction->save();
                $school = SmSchool::find($user->school_id);
                $compact['user_email'] = $user->email;
                $compact['full_name'] = $user->full_name;
                $compact['method'] = $request->payment_method;
                $compact['create_date'] = date('Y-m-d');
                $compact['school_name'] = $school->school_name;
                $compact['current_balance'] = $user->wallet_balance;
                $compact['add_balance'] = $request->total_paid_amount;
                $compact['previous_balance'] = $user->wallet_balance - $request->add_wallet;
                @send_mail($user->email, $user->full_name, 'fees_extra_amount_add', $compact);
                // Notification
                sendNotification('Fees Xtra Amount Add', null, $student->user_id, 2);
            }

            $fmFeesTransaction = new FmFeesTransaction();
            $fmFeesTransaction->fees_invoice_id = $request->invoice_id;
            $fmFeesTransaction->payment_note = $request->payment_note ?? '';
            $fmFeesTransaction->payment_method = $request->payment_method;
            $fmFeesTransaction->bank_id = $request->bank;
            $fmFeesTransaction->student_id = $student->id;
            $fmFeesTransaction->record_id = $request->record_id;
            $fmFeesTransaction->user_id = Auth::user()->id;
            $fmFeesTransaction->file = $file;
            $fmFeesTransaction->paid_status = 'approve';
            $fmFeesTransaction->school_id = Auth::user()->school_id;
            $fmFeesTransaction->academic_id = getAcademicId();
            $fmFeesTransaction->save();

            foreach ($request->fees_type as $key => $type) {
                $id = FmFeesInvoiceChield::where('fees_invoice_id', $request->invoice_id)->where('fees_type', $type)->first('id')->id;
                $storeFeesInvoiceChield = FmFeesInvoiceChield::find($id);
                $storeFeesInvoiceChield->weaver = $request->weaver[$key];
                $storeFeesInvoiceChield->due_amount = $request->due[$key];
                $storeFeesInvoiceChield->paid_amount += $request->paid_amount[$key] - $request->extraAmount[$key];
                $storeFeesInvoiceChield->fine += $request->fine[$key];
                $storeFeesInvoiceChield->update();
                $storeWeaver = new FmFeesWeaver();
                $storeWeaver->fees_invoice_id = $request->invoice_id;
                $storeWeaver->fees_type = $type;
                $storeWeaver->student_id = $student->id;
                $storeWeaver->weaver = $request->weaver[$key];
                $storeWeaver->note = $request->note[$key];
                $storeWeaver->school_id = Auth::user()->school_id;
                $storeWeaver->academic_id = getAcademicId();
                $storeWeaver->save();
                if ($request->paid_amount[$key] > 0) {
                    $storeTransactionChield = new FmFeesTransactionChield();
                    $storeTransactionChield->fees_transaction_id = $fmFeesTransaction->id;
                    $storeTransactionChield->fees_type = $type;
                    $storeTransactionChield->weaver = $request->weaver[$key];
                    $storeTransactionChield->fine = $request->fine[$key];
                    $storeTransactionChield->paid_amount = $request->paid_amount[$key];
                    $storeTransactionChield->note = $request->note[$key];
                    $storeTransactionChield->school_id = Auth::user()->school_id;
                    $storeTransactionChield->academic_id = getAcademicId();
                    $storeTransactionChield->save();
                }
                // Income
                $payment_method = SmPaymentMethhod::where('method', $request->payment_method)->first();
                $income_head = generalSetting();
                $add_income = new SmAddIncome();
                $add_income->name = 'Fees Collect';
                $add_income->date = date('Y-m-d');
                $add_income->amount = $request->paid_amount[$key];
                $add_income->fees_collection_id = $fmFeesTransaction->id;
                $add_income->active_status = 1;
                $add_income->income_head_id = $income_head->income_head_id;
                $add_income->payment_method_id = $payment_method->id;
                if ($payment_method->id == 3) {
                    $add_income->account_id = $request->bank;
                }

                $add_income->created_by = Auth()->user()->id;
                $add_income->school_id = Auth::user()->school_id;
                $add_income->academic_id = getAcademicId();
                $add_income->save();

                // Bank
                if ($request->payment_method == 'Bank') {
                    $payment_method = SmPaymentMethhod::where('method', $request->payment_method)->first();
                    $bank = SmBankAccount::where('id', $request->bank)
                        ->where('school_id', Auth::user()->school_id)
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
                    $bank_statement->school_id = Auth::user()->school_id;
                    $bank_statement->payment_method = $payment_method->id;
                    $bank_statement->save();

                    $current_balance = SmBankAccount::find($request->bank);
                    $current_balance->current_balance = $after_balance;
                    $current_balance->update();
                }
            }

            $student_user_id = $student->user_id;
            $data['fees'] = $request->total_paid_amount;
            try {
                $this->sent_notifications('Fees_Payment', [$student_user_id], $data, ['Student', 'Parent']);
            } catch (Exception $e) {
                Toastr::error('Operation Failed', 'Failed');

                return redirect()->back();
            }

            Toastr::success('Save Successful', 'Success');

            return redirect()->route('fees.fees-invoice-list');
        } catch (Exception $exception) {
            dd($exception);
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function feesInvoiceSettings()
    {
        try {
            $invoiceSettings = FmFeesInvoiceSettings::where('school_id', Auth::user()->school_id)->first();

            return view('fees::feesInvoiceSettings', ['invoiceSettings' => $invoiceSettings]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function bankPayment()
    {
        $classes = SmClass::get();

        $feesPayments = FmFeesTransaction::with('feeStudentInfo', 'transcationDetails', 'transcationDetails.transcationFeesType')
            ->where('paid_status', 'pending')
            ->whereIn('payment_method', ['Bank', 'Cheque'])
            ->where('school_id', auth()->user()->school_id)
            ->where('academic_id', getAcademicId())
            ->get();

        return view('fees::bankPayment', ['classes' => $classes, 'feesPayments' => $feesPayments]);
    }

    public function searchBankPayment(BankFeesPayment $bankFeesPayment)
    {
        try {
            $rangeArr = $bankFeesPayment->payment_date ? explode('-', $bankFeesPayment->payment_date) : [date('m/d/Y'), date('m/d/Y')];

            if ($bankFeesPayment->payment_date) {
                $date_from = date('Y-m-d', strtotime(trim($rangeArr[0])));
                $date_to = date('Y-m-d', strtotime(trim($rangeArr[1])));
            }

            $classes = SmClass::get();

            $class_id = $bankFeesPayment->class;
            $shift_id = shiftEnable() ? $bankFeesPayment->shift : null;
            $section_id = $bankFeesPayment->section;
            $class = SmClass::with('classSections')->where('id', $bankFeesPayment->class)->first();

            $student_ids = StudentRecord::when($bankFeesPayment->class, function ($query) use ($bankFeesPayment): void {
                $query->where('class_id', $bankFeesPayment->class);
            })
                ->when($bankFeesPayment->section, function ($query) use ($bankFeesPayment): void {
                    $query->where('section_id', $bankFeesPayment->section);
                })
                ->when($bankFeesPayment->shift, function ($query) use ($bankFeesPayment) {
                    $query->where('shift_id', $bankFeesPayment->shift);
                })
                ->where('school_id', auth()->user()->school_id)
                ->pluck('student_id')
                ->unique();

            $feesPayments = FmFeesTransaction::when($bankFeesPayment->approve_status, function ($query) use ($bankFeesPayment): void {
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
                ->when($bankFeesPayment->shift, function ($query) use ($bankFeesPayment) {
                    $query->whereHas('recordDetail', function ($q) use ($bankFeesPayment) {
                        return $q->where('shift_id', $bankFeesPayment->shift);
                    });
                })
                ->when($bankFeesPayment->payment_date, function ($query) use ($date_from, $date_to): void {
                    $query->whereDate('created_at', '>=', $date_from)
                        ->whereDate('created_at', '<=', $date_to);
                })
                ->whereIn('student_id', $student_ids)
                ->whereIn('payment_method', ['Bank', 'Cheque'])
                ->where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            return view('fees::bankPayment', ['classes' => $classes, 'shift_id' => $shift_id, 'feesPayments' => $feesPayments, 'class_id' => $class_id, 'section_id' => $section_id, 'class' => $class]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
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
            $updateData->school_id = Auth::user()->school_id;
            $updateData->update();

            return response()->json(['success']);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function approveBankPayment(Request $request)
    {
        try {
            $transcation = $request->transcation_id;
            $total_paid_amount = $request->total_paid_amount ?: null;

            $transcationInfo = FmFeesTransaction::find($transcation);

            $feesExtendedController = new FeesExtendedController();
            $feesExtendedController->addFeesAmount($transcation, $total_paid_amount);

            // Notification
            $student = SmStudent::with('parents')->find($transcationInfo->student_id);
            sendNotification('Approve Bank Payment', null, 1, 1);
            sendNotification('Approve Bank Payment', null, $student->user_id, 2);
            sendNotification('Approve Bank Payment', null, $student->parents->user_id, 3);

            $data = [];
            $student_user_id = SmStudent::find($transcationInfo->student_id)->user_id;
            $data['student_name'] = $student->full_name;
            $data['amount'] = $total_paid_amount;
            try {
                $this->sent_notifications('Approve_Deposit', [$student_user_id], $data, ['Student', 'Parent']);
            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

            Toastr::success('Save Successful', 'Success');
            return redirect()->back();
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
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
            sendNotification('Reject Bank Payment', null, 1, 1);
            sendNotification('Reject Bank Payment', null, $student->user_id, 2);
            sendNotification('Reject Bank Payment', null, $student->parents->user_id, 3);

            $data = [];
            $student_user_id = SmStudent::find($transcation->student_id)->user_id;
            $data['student_name'] = $student->full_name;
            $data['amount'] = $transcation->total_paid_amount;
            try {
                $this->sent_notifications('Reject_Deposit', [$student_user_id], $data, ['Student', 'Parent']);
            } catch (Exception $e) {
                Log::info($e->getMessage());
            }

            Toastr::success('Save Successful', 'Success');

            return redirect()->back();
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
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
                $fees_inv = FmFeesInvoice::find($transcationId->fees_invoice_id);
                if ($fees_inv) {
                    $cache_key = 'have_due_fees_'.$transcationId->user_id;
                    Cache::rememberForever($cache_key, function (): bool {
                        return true;
                    });
                }
            }

            if ($transcation->payment_method == 'Wallet') {
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
                $walletTransaction->school_id = Auth::user()->school_id;
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

            Toastr::success('Delete Successful', 'Success');

            return redirect()->route('fees.fees-invoice-list');

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function singlePaymentView($id, $type)
    {
        $generalSetting = SmGeneralSettings::where('school_id', Auth::user()->school_id)->first();

        $transcationInfo = FmFeesTransaction::find($id);

        $transcationDetails = FmFeesTransactionChield::where('fees_transaction_id', $transcationInfo->id)
            ->where('school_id', Auth::user()->school_id)
            ->where('academic_id', getAcademicId())
            ->get();

        $invoiceInfo = FmFeesInvoice::find($transcationInfo->fees_invoice_id);

        if ($type == 'view') {
            return view('fees::feesInvoice.feesInvoiceSingleView', ['generalSetting' => $generalSetting, 'invoiceInfo' => $invoiceInfo, 'transcationDetails' => $transcationDetails, 'id' => $id]);
        }

        return view('fees::feesInvoice.feesInvoiceSinglePrint', ['generalSetting' => $generalSetting, 'invoiceInfo' => $invoiceInfo, 'transcationDetails' => $transcationDetails]);

    }

    public function feesInvoiceDatatable()
    {
        $previous_url = url()->previous();
        $previous_route = app('router')->getRoutes()->match(app('request')->create($previous_url))->getName();

        $fees_type = $previous_route == 'lms.fees-invoice' ? 'lms' : 'fees';

        $studentInvoices = FmFeesInvoice::where('type', $fees_type)
            ->with(['studentInfo' => function ($query): void {
                $query->select(['id', 'admission_no', 'first_name', 'last_name']);
            }, 'invoiceDetails' => function ($query): void {
                $query->select(['amount', 'weaver', 'fine', 'paid_amount', 'sub_total', 'id']);
            }])
            ->select('fm_fees_invoices.*')
            ->where('school_id', Auth::user()->school_id)
            ->where('academic_id', getAcademicId())
            ->withInvoiceDetailsSums();

        if (isset($studentInvoices)) {

            return DataTables::of($studentInvoices)
                ->addIndexColumn()
                ->addColumn('student_name', function ($row): string {
                    return '<a href="'.route('fees.fees-invoice-view', ['id' => $row->id, 'state' => 'view']).'target="_blank">'.@$row->studentInfo->full_name.'</a>';
                })
                ->addColumn('admission_no', function ($row) {
                    return $row->studentInfo->admission_no;
                })
                ->addColumn('amount', function ($row) {
                    return $row->Tamount;
                })
                ->addColumn('weaver', function ($row) {
                    return $row->Tweaver;
                })
                ->addColumn('fine', function ($row) {
                    return $row->Tfine;
                })
                ->addColumn('paid_amount', function ($row) {
                    return $row->Tpaidamount;
                })
                ->addColumn('balance', function ($row) {
                    $amount = $row->Tamount;
                    $weaver = $row->Tweaver;
                    $fine = $row->Tfine;
                    $paid_amount = $row->Tpaidamount;

                    return $amount + $fine - ($paid_amount + $weaver);
                })
                ->addColumn('status', function ($row): string {
                    $amount = $row->Tamount;
                    $weaver = $row->Tweaver;
                    $fine = $row->Tfine;
                    $paid_amount = $row->Tpaidamount;
                    
                    
                    $balance = $amount + $fine - ($paid_amount + $weaver);
                    
                    
                    if ($balance == 0) {
                        if ($amount == 0 && $balance == 0 && $paid_amount == 0) {
                            $btn = '<button class="primary-btn small bg-danger text-white border-0">'.__('fees.unpaid').'</button>';
                        } else {
                            $btn = '<button class="primary-btn small bg-success text-white border-0">'.__('fees.paid').'</button>';
                        }
                    } elseif ($balance > 0 && $paid_amount > 0) {
                        $btn = '<button class="primary-btn small bg-warning text-white border-0">'.__('fees.partial').'</button>';
                    } else {
                        $btn = '<button class="primary-btn small bg-danger text-white border-0">'.__('fees.unpaid').'</button>';
                    }

                    return $btn;
                })
                ->filterColumn('admission_no', function ($query, $keyword): void {
                    $query->whereHas('studentInfo', function ($query) use ($keyword): void {
                        $query->where('admission_no', 'like', '%'.$keyword.'%');
                    });
                })->filterColumn('amount', function ($query, $keyword): void {
                    $query->whereHas('invoiceDetails', function ($query) use ($keyword): void {
                        $query->where('amount', 'like', '%'.$keyword.'%');
                    });
                })
                ->filterColumn('weaver', function ($query, $keyword): void {
                    $query->whereHas('invoiceDetails', function ($query) use ($keyword): void {
                        $query->where('weaver', 'like', '%'.$keyword.'%');
                    });
                })
                ->filterColumn('fine', function ($query, $keyword): void {
                    $query->whereHas('invoiceDetails', function ($query) use ($keyword): void {
                        $query->where('fine', 'like', '%'.$keyword.'%');
                    });
                })
                ->filterColumn('paid_amount', function ($query, $keyword): void {
                    $query->whereHas('invoiceDetails', function ($query) use ($keyword): void {
                        $query->where('paid_amount', 'like', '%'.$keyword.'%');
                    });
                })
                ->filterColumn('student_name', function ($query, $keyword): void {
                    $query->whereHas('studentInfo', function ($query) use ($keyword): void {
                        $query->where('full_name', 'like', '%'.$keyword.'%');
                    });
                })
                ->addColumn('create_date', function ($row) {
                    return dateConvert($row->create_date);
                })
                ->filterColumn('create_date', function ($query, $keyword): void {
                    $date = date('Y-m-d', strtotime($keyword));

                    if ($date != '0') {
                        $query->whereDate('create_date', '=', $date);
                    }
                })
                ->orderColumn('create_date', function ($query, $order): void {
                    $query->orderBy('create_date', $order)->orderBy('create_date', 'DESC');
                })
                ->addColumn('action', function ($row): string {
                    $role = 'admin';
                    $amount = $row->Tamount;
                    $weaver = $row->Tweaver;
                    $fine = $row->Tfine;
                    $paid_amount = $row->Tpaidamount;
                    $balance = $amount + $fine - ($paid_amount + $weaver);
                    $view = view('fees::__allFeesListAction', ['row' => $row, 'balance' => $balance, 'paid_amount' => $paid_amount, 'role' => $role, 'amount' => $amount]);

                    return (string) $view;
                })
                ->rawColumns(['student_name', 'admission_no', 'status', 'action', 'date'])
                ->make(true);
        }

        return null;
    }
}
