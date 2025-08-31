<?php

namespace Modules\Wallet\Http\Controllers;

use App\User;
use Exception;
use DataTables;
use App\SmStudent;
use App\SmBankAccount;
use App\SmNotification;
use App\SmPaymentMethhod;
use App\SmGeneralSettings;
use Illuminate\Http\Request;
use App\SmPaymentGatewaySetting;
use App\Traits\NotificationSend;
use App\PaymentGateway\SslCommerz;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Wallet\Entities\WalletTransaction;
use Modules\CcAveune\Http\Controllers\CcAveuneController;
use Modules\ToyyibPay\Http\Controllers\ToyyibPayController;

class WalletController extends Controller
{
    use NotificationSend;

    public function addWalletAmount(Request $request)
    {
        $request->validate([
            'amount' => 'nullable',
            'payment_method' => 'nullable',
            'bank' => 'required_if:payment_method,Bank',
            'file' => 'mimes:jpg,jpeg,png,pdf',
        ]);
        $url = '';
        if ($request->payment_method == 'Cheque' || $request->payment_method == 'Bank') {
            $uploadFile = '';
            if ($request->hasFile('file')) {
                $maxFileSize = SmGeneralSettings::first('file_size')->file_size;
                $file = $request->file('file');
                $fileSize = filesize($file);
                $fileSizeKb = ($fileSize / 1000000);
                if ($fileSizeKb >= $maxFileSize) {
                    Toastr::error('Max upload file size '.$maxFileSize.' Mb is set in system', 'Failed');

                    return redirect()->back();
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
        } else {
            $data = [];
            $data['payment_method'] = $request->payment_method;
            $data['amount'] = $request->amount;
            $data['service_charge'] = chargeAmount($request->payment_method, $request->amount);
            $data['user_id'] = Auth::user()->id;
            $data['type'] = 'Wallet';
            $data['wallet_type'] = 'diposit';
            $data['description'] = 'Wallet Amount Request';
            $data['stripeToken'] = $request->stripeToken;
            $data['student_id'] = SmStudent::where('user_id', $data['user_id'])->value('id');
            if ($data['payment_method'] == 'CcAveune') {
                $addPayment = new WalletTransaction();
                $addPayment->amount = $data['amount'];
                $addPayment->payment_method = $data['payment_method'];
                $addPayment->user_id = $data['user_id'];
                $addPayment->type = $data['wallet_type'];
                $addPayment->school_id = auth()->user()->school_id;
                $addPayment->academic_id = getAcademicId();
                $addPayment->save();

                $ccAveuneController = new CcAveuneController();
                $ccAveuneController->studentFeesPay($data['amount'], $addPayment->id, $data['type']);
            } elseif ($data['payment_method'] == 'ToyyibPay') {
                DB::beginTransaction();

                try {
                    $addPayment = new WalletTransaction();
                    $addPayment->amount = $data['amount'];
                    $addPayment->payment_method = $data['payment_method'];
                    $addPayment->user_id = $data['user_id'];
                    $addPayment->type = $data['wallet_type'];
                    $addPayment->school_id = auth()->user()->school_id;
                    $addPayment->academic_id = getAcademicId();
                    $addPayment->save();

                    $toyyibPayController = new ToyyibPayController();
                    $data = [
                        'amount' => $data['amount'],
                        'type' => $data['wallet_type'],
                        'student_id' => $data['student_id'],
                        'user_id' => $data['user_id'],
                        'service_charge' => chargeAmount($request->payment_method, $request->total_paid_amount),
                        'invoice_id' => $addPayment->id,
                        'payment_method' => $request->payment_method,
                    ];
                    $data_store = $toyyibPayController->studentFeesPay($data);
                    DB::commit();

                    return response()->json(['payment_link' => $data_store]);
                } catch (Exception $e) {
                    DB::rollback();
                    Log::error($e);
                    return response()->json(['error' => 'An error occurred while processing your request. Please try again later.'], 500);
                }
            }
            else if($data['payment_method'] == 'ToyyibPay') { 
                    DB::beginTransaction();
                
                    try {
                        $addPayment = new WalletTransaction();
                        $addPayment->amount = $data['amount'];
                        $addPayment->payment_method = $data['payment_method'];
                        $addPayment->user_id = $data['user_id'];
                        $addPayment->type = $data['wallet_type'];
                        $addPayment->school_id = auth()->user()->school_id;
                        $addPayment->academic_id = getAcademicId();
                        $addPayment->save();
                
                        $toyyibPayController = new ToyyibPayController();
                        $data = [
                            'amount' => $data['amount'],
                            'type' => $data['wallet_type'],
                            'student_id' => $data['student_id'],
                            'user_id' => $data['user_id'],
                            'service_charge' => chargeAmount($request->payment_method, $request->total_paid_amount),
                            'invoice_id' => $addPayment->id,
                            'payment_method' => $request->payment_method,
                        ];
                        $data_store = $toyyibPayController->studentFeesPay($data);
                        DB::commit();
                
                        return response()->json(['payment_link' => $data_store]);
                    } catch (\Exception $e) {
                        DB::rollback();
                        Log::error($e);
                        return response()->json(['error' => 'An error occurred while processing your request. Please try again later.'], 500);
                    }
            }elseif($data['payment_method'] == 'SslCommerz'){
                DB::beginTransaction();
            
                try {
                    $addPayment = new WalletTransaction();
                    $addPayment->amount = $data['amount'];
                    $addPayment->payment_method = $data['payment_method'];
                    $addPayment->user_id = $data['user_id'];
                    $addPayment->type = $data['wallet_type'];
                    $addPayment->school_id = auth()->user()->school_id;
                    $addPayment->academic_id = getAcademicId();
                    $addPayment->save();
            
                    $sslCommerz = new SslCommerz();
                    $data = [
                        'amount' => $data['amount'],
                        'type' => $data['wallet_type'],
                        'student_id' => $data['student_id'],
                        'user_id' => $data['user_id'],
                        'service_charge' => chargeAmount($request->payment_method, $request->total_paid_amount),
                        'invoice_id' => $addPayment->id,
                        'payment_method' => $request->payment_method,
                        
                    ];
                    $ssl_response = $sslCommerz->handle($data);
                    if($ssl_response == true)
                    {
                        DB::commit();                
                        return response()->json(['payment_link' => $ssl_response['url']]);
                    }else{
                        WalletTransaction::where('id',$addPayment->id)->delete();
                        return response()->json(['payment_link' => false]);
                    }
                    
                }catch(\Exception $e){
                    DB::rollback();
                    Log::error($e);
                    return response()->json(['error' => 'An error occurred while processing your request. Please try again later.'], 500);
                }   
            }             
            else{
                $classMap = config('paymentGateway.' . $data['payment_method']);
                $make_payment = new $classMap();
                $url = $make_payment->handle($data);
                if (!$url) {
                    $url = 'wallet/my-wallet';
                }
                if ($request->wantsJson()) {
                    return response()->json(['goto' => $url]);
                } else {
                    return redirect($url);
                }
            }
             return redirect($url);

        }
        
    

        // Notification Start
        $data['title'] = 'Wallet Amount Add';
        $data['amount'] = $request->amount;
        $this->sent_notifications('Wallet_Add', (array) auth()->user()->id, $data, ['Student', 'Parent']);

        $accounts_ids = User::where('role_id', 6)->get();
        foreach ($accounts_ids as $account_id) {
            $this->sendNotification($account_id->id, $account_id->role_id, 'Wallet Request');
        }

        return response()->json(['success']);
        // Notification End

    }

    public function walletPendingDiposit()
    {
        $walletTotalAmounts = $this->getTotalWalletAmount('diposit', 'pending');

        return view('wallet::walletPending', ['walletTotalAmounts' => $walletTotalAmounts]);
    }

    public function walletApprovePayment(Request $request)
    {
        try {
            $user = User::find($request->user_id);

            $currentamount = $user->wallet_balance;
            $addedAmount = $currentamount + $request->amount;
            $user->wallet_balance = $addedAmount;
            $user->update();

            $status = WalletTransaction::find($request->approveId);
            $status->status = 'approve';
            $status->updated_at = date('Y-m-d');
            $status->update();

            $compact['user_email'] = $user->email;
            $compact['method'] = $status->payment_method;
            $compact['create_date'] = $status->created_by;
            $compact['current_balance'] = $user->wallet_balance;
            $compact['add_balance'] = $request->amount;

            $data['title'] = 'Wallet Amount Approve';
            $data['amount'] = $request->amount;
            $this->sent_notifications('Approve_Deposit', (array) $request->user_id, $data, ['Student', 'Parent']);

            // @send_mail($user->email, $user->full_name, "wallet_approve", $compact);

            // $this->sendNotification($user->id, $user->role_id, "Wallet Approve");

            Toastr::success('Approve Successful', 'Success');

            return redirect()->route('wallet.pending-diposit');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function walletRejectPayment(Request $request)
    {
        try {
            $user = User::find($request->user_id);
            $status = WalletTransaction::find($request->rejectPId);
            $status->status = 'reject';
            $status->reject_note = $request->reject_note;
            $status->updated_at = date('Y-m-d');
            $status->update();

            $compact['user_email'] = $user->email;
            $compact['method'] = $status->payment_method;
            $compact['create_date'] = $status->created_at;
            $compact['current_balance'] = $user->wallet_balance;
            $compact['add_balance'] = $request->amount;
            $compact['reject_reason'] = $request->reject_note;

            $data['title'] = 'Wallet Amount Reject';
            $data['amount'] = $request->amount;
            $this->sent_notifications('Reject_Deposit', (array) $request->user_id, $data, ['Student', 'Parent']);

            // @send_mail($user->email, $user->full_name, "wallet_reject", $compact);

            // $this->sendNotification($user->id, $user->role_id, "Wallet Approve");

            Toastr::success('Reject Successful', 'Success');

            return redirect()->route('wallet.pending-diposit');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function walletApproveDiposit()
    {
        $walletTotalAmounts = $this->getTotalWalletAmount('diposit', 'pending');

        return view('wallet::walletApprove', ['walletTotalAmounts' => $walletTotalAmounts]);
    }

    public function walletRejectDiposit()
    {
        $walletTotalAmounts = $this->getTotalWalletAmount('diposit', 'pending');

        return view('wallet::walletReject', ['walletTotalAmounts' => $walletTotalAmounts]);
    }

    public function walletTransaction()
    {
        $walletAmounts = WalletTransaction::where('school_id', Auth::user()->school_id)
            ->get();

        return view('wallet::walletTransaction', ['walletAmounts' => $walletAmounts]);
    }

    public function walletTransactionAjax(Request $request)
    {
        
        if ($request->ajax()) {
            $walletAmounts = WalletTransaction::with('userName')->where('school_id', Auth::user()->school_id)->latest();

            return DataTables::of($walletAmounts)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    return dateConvert(@$row->updated_at);
                })
                ->addColumn('t_status', function ($row): string {
                    if ($row->status == 'pending') {
                        $btn = '<button class="primary-btn small bg-warning text-white border-0">'.app('translator')->get('common.pending').'</button>';
                    } elseif ($row->status == 'approve') {
                        $btn = '<button class="primary-btn small bg-success text-white border-0">'.app('translator')->get('wallet::wallet.approve').'</button>';
                    } elseif ($row->status == 'reject') {
                        $btn = '<button class="primary-btn small bg-danger text-white border-0">'.app('translator')->get('wallet::wallet.reject').'</button>';
                    } elseif ($row->status == 'refund') {
                        $btn = '<button class="primary-btn small bg-primary text-white border-0">'.app('translator')->get('wallet::wallet.refund').'</button>';
                    } else {
                        $btn = '<button class="primary-btn small bg-primary text-white border-0">'.app('translator')->get('accounts.expense').'</button>';
                    }

                    return $btn;
                })

                ->addColumn('pending_amount', function ($row) {
                    if ($row->status == 'pending') {
                        return generalSetting()->currency_symbol.' '.$row->amount;
                    }
                })
                ->addColumn('approve_amount', function ($row) {
                    if ($row->status == 'approve') {
                        return generalSetting()->currency_symbol.' '.$row->amount;
                    }
                })
                ->addColumn('reject_amount', function ($row) {
                    if ($row->status == 'reject') {
                        return generalSetting()->currency_symbol.' '.$row->amount;
                    }

                })

                ->addColumn('refund_amount', function ($row) {
                    if ($row->type == 'refund' && $row->status == 'approve') {
                        return generalSetting()->currency_symbol.' '.$row->amount;
                    }
                })
                ->addColumn('expense_amount', function ($row) {
                    if ($row->type == 'expense') {

                        return generalSetting()->currency_symbol.' '.$row->amount;
                    }
                })
                ->addColumn('fees_refund_amount', function ($row) {
                    if ($row->type == 'fees_refund') {
                        return generalSetting()->currency_symbol.' '.$row->amount;
                    }
                })

                ->rawColumns(['action', 'date', 'pending_amount', 'approve_amount', 'reject_amount', 'refund_amount', 'expense_amount', 'fees_refund_amount', 't_status'])
                ->make(true);
        }

        return null;
    }

    public function walletRefundRequest()
    {
        $walletRefunds = WalletTransaction::where('type', 'refund')
            ->where('school_id', Auth::user()->school_id)
            ->get();

        return view('wallet::walletRefundRequest', ['walletRefunds' => $walletRefunds]);
    }

    public function walletRefundRequestAjax(Request $request)
    {
        if ($request->ajax()) {
            $walletRefunds = WalletTransaction::with('userName')->where('type', 'refund')
                ->where('school_id', Auth::user()->school_id)->latest();

            return DataTables::of($walletRefunds)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    return dateConvert(@$row->created_at);
                })
                ->addColumn('t_status', function ($row): string {
                    if ($row->status == 'pending') {
                        $btn = '<button class="primary-btn small bg-warning text-white border-0">'.app('translator')->get('common.pending').'</button>';
                    } elseif ($row->status == 'approve') {
                        $btn = '<button class="primary-btn small bg-success text-white border-0">'.app('translator')->get('wallet::wallet.approved').'</button>';
                    } else {
                        $btn = '<button class="primary-btn small bg-danger text-white border-0">'.app('translator')->get('wallet::wallet.rejecred').'</button>';
                    }

                    return $btn;
                })
                ->addColumn('pending_refund', function ($row) {
                    if ($row->type == 'refund' && $row->status == 'pending') {
                        return generalSetting()->currency_symbol.' '.$row->amount;
                    }
                })
                ->addColumn('approve_refund', function ($row) {
                    if ($row->type == 'refund' && $row->status == 'approve') {
                        return generalSetting()->currency_symbol.' '.$row->amount;
                    }
                })
                ->addColumn('reject_refund', function ($row) {
                    if ($row->type == 'refund' && $row->status == 'reject') {
                        return generalSetting()->currency_symbol.' '.$row->amount;
                    }

                })
                ->addColumn('file_view', function ($row) {
                    if ($row->file) {
                        return '<a class="dropdown-item file_'.$row->id.'" data-file="'.asset($row->file).'"  onclick="fileView('.$row->id.');" href="#">'.app('translator')->get('common.view').'</a>';
                    }
                })
                ->addColumn('refund_note', function ($row) {
                    if ($row->note) {
                        return '<a class="dropdown-item note_'.$row->id.'" data-note="'.$row->note.'"  onclick="refundNote('.$row->id.');" href="#">'.app('translator')->get('common.view').'</a>';
                    }
                })
                ->addColumn('action', function ($row) {
                    if ($row->status == 'pending') {
                        return '<div class="dropdown CRM_dropdown">
                            <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">'.app('translator')->get('common.select').'</button>

                            <div class="dropdown-menu dropdown-menu-right">'
                                    .(userPermission('wallet.approve-refund') ? (Config::get('app.app_sync') ? '<span  data-toggle="tooltip" title="Disabled For Demo "><a  class="dropdown-item" href="#"  >'.app('translator')->get('common.disable').'</a></span>' :
                                        '<a onclick="approveRefund('.$row->id.');"  class="dropdown-item" href="#" data-toggle="modal" data-target="#approveRefund" data-id="'.$row->id.'"  >'.app('translator')->get('wallet::wallet.approve').'</a>') : '').
                                        (userPermission('wallet.reject-refund') ? (Config::get('app.app_sync') ? '<span  data-toggle="tooltip" title="Disabled For Demo "><a  class="dropdown-item" href="#"  >'.app('translator')->get('common.disable').'</a></span>' :
                                        '<a onclick="rejectRefund('.$row->id.');"  class="dropdown-item" href="#" data-toggle="modal" data-target="#approveRefund" data-id="'.$row->id.'"  >'.app('translator')->get('wallet::wallet.reject').'</a>') : '').

                                '</div>
                        </div>';
                    }

                })
                ->rawColumns(['pending_refund', 'approve_refund', 'reject_refund', 'date', 't_status', 'action', 'file_view', 'refund_note'])
                ->make(true);

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
            throw ValidationException::withMessages(['exist' => 'You Already Request For Refund']);
        }

        try {
            $uploadFile = '';
            if ($request->file('refund_file') != '') {
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

            return response()->json(['success' => 'success']);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function walletApproveRefund(Request $request)
    {
        try {
            $status = WalletTransaction::find($request->id);
            
            $user = User::find($status->user_id);
            if($user->wallet_balance < $status->amount){
                Toastr::error('insufficient balance', 'Success');
                return redirect()->route('wallet.wallet-refund-request');
            }
            $balance = $user->wallet_balance - $status->amount;
            $user->wallet_balance = $balance;
            $user->update();

            $status->status = 'approve';
            $status->updated_at = date('Y-m-d');
            $status->update();

            $compact['user_email'] = $user->email;
            $compact['create_date'] = $status->created_at;
            $compact['current_balance'] = $user->wallet_balance;
            $compact['refund_amount'] = $status->amount;

            $data['title'] = 'Wallet Amount Refund';
            $data['amount'] = $status->amount;
            $this->sent_notifications('Refund_Deposit', (array) $user->id, $data, ['Student', 'Parent']);

            // @send_mail($user->email, $user->full_name, "wallet_refund", $compact);

            // $this->sendNotification($user->id, $user->role_id, "Wallet Approve");

            Toastr::success('Approve Successful', 'Success');
            return redirect()->route('wallet.wallet-refund-request');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function walletRejectRefund(Request $request)
    {
        try {
            $status = WalletTransaction::find($request->id);
            $user = User::find($status->user_id);

            $status->status = 'reject';
            $status->note = $request->reject_note;
            $status->updated_at = date('Y-m-d');
            $status->update();

            $compact['user_email'] = $user->full_name;
            $compact['method'] = $status->payment_method;
            $compact['create_date'] = $status->created_by;
            $compact['current_balance'] = $user->wallet_balance;
            $compact['add_balance'] = $request->amount;
            $compact['reject_reason'] = $request->reject_note;

            @send_mail($user->email, $user->full_name, 'wallet_reject', $compact);

            $this->sendNotification($user->id, $user->role_id, 'Wallet Reject');

            Toastr::success('Reject Successful', 'Success');

            return redirect()->route('wallet.wallet-refund-request');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function walletReport()
    {
        return view('wallet::walletReport');

    }

    public function walletReportSearch(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route('wallet.wallet-report')->withErrors($validator)->withInput();
        }

        try {
            $rangeArr = $request->date_range ? explode('-', $request->date_range) : [date('Y/m/d'), date('Y/m/d')];

            $date_from = \Carbon\Carbon::parse($rangeArr[0])->format('Y-m-d');
            $date_to = \Carbon\Carbon::parse($rangeArr[1])->format('Y-m-d');

            $walletReports = WalletTransaction::whereDate('created_at', '>=', $date_from)
                ->whereDate('created_at', '<=', $date_to)
                ->where('school_id', Auth::user()->school_id);

            $walletReports = $walletReports->when($request->type, function ($q) use ($request) {
                return $q->where('type', $request->type);
            })->when($request->status, function ($q) use ($request) {
                return $q->where('status', $request->status);
            })->get();

            return view('wallet::walletReport', ['walletReports' => $walletReports]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function myWallet()
    {
        try {
            $paymentMethods = SmPaymentMethhod::whereNotIn('method', ['Cash', 'Wallet'])
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $bankAccounts = SmBankAccount::where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $walletAmounts = WalletTransaction::where('user_id', Auth::user()->id)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $stripe_info = SmPaymentGatewaySetting::where('gateway_name', 'Stripe')
                ->where('school_id', Auth::user()->school_id)
                ->first();
            $razorpay_info = null;
            if (moduleStatusCheck('RazorPay')) {
                $razorpay_info = SmPaymentGatewaySetting::where('gateway_name', 'RazorPay')
                    ->where('school_id', Auth::user()->school_id)
                    ->first();
            }

            return view('wallet::myWallet', ['paymentMethods' => $paymentMethods, 'bankAccounts' => $bankAccounts, 'walletAmounts' => $walletAmounts, 'stripe_info' => $stripe_info, 'razorpay_info' => $razorpay_info]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function walletDipositDatatable(Request $request)
    {
        try {
            if ($request->ajax()) {
                $walletAmounts = WalletTransaction::select('wallet_transactions.*', 'users.full_name as user_name')
                    ->join('users', 'users.id', '=', 'wallet_transactions.user_id')
                    ->where('wallet_transactions.type','diposit')
                    ->where('wallet_transactions.school_id', Auth::user()->school_id);

                if ($request->status) {
                    $walletAmounts = $walletAmounts->where('wallet_transactions.status', $request->status);
                }
               
                return DataTables::of($walletAmounts)
                    ->addIndexColumn()
                    ->addColumn('amount', function ($row) {
                        return currency_format($row->amount);
                    })
                    ->filterColumn('amount', function ($query, $keyword): void {
                        $query->whereRaw('amount like ?', [sprintf('%%%s%%', $keyword)]);
                    })
                    ->orderColumn('amount', function ($query, $order): void {
                        $query->orderBy('amount', $order)->orderBy('amount', 'DESC');
                    })
                    ->addColumn('walletStatus', function ($row): string {
                        $btn = '';
                        if ($row->status == 'pending') {
                            $btn = '<button class="primary-btn small bg-warning text-white border-0">'.__('common.pending').'</button>';
                        } elseif ($row->status == 'approve') {
                            $btn = '<button class="primary-btn small bg-success text-white border-0" style="cursor:default">'.__('wallet::wallet.approved').'</button>';
                        } else {
                            $btn = '<button class="primary-btn small bg-danger text-white border-0" style="cursor:default">'.__('wallet::wallet.rejected').'</button>';
                        }

                        return $btn;
                    })
                    ->addColumn('walletNote', function ($row): string {
                        if ($row->note) {
                            return '<a class="text-color walletNote'.$row->id.'" data-note="'.$row->note.'" onclick="noteModal('.$row->id.');" data-toggle="modal" data-target="#noteModal'.$row->id.'"  href="#">'.__('common.view').'</a>';
                        }

                        return '';
                    })
                    ->addColumn('walletRejectNote', function ($row): string {
                        return '<td>'.(@$row->reject_note ? '<a class="text-color rejectNote'.$row->id.'" data-rejectnote="'.$row->reject_note.'" onclick="rejectNote('.$row->id.');" data-toggle="modal" data-target="#rejectNote'.$row->id.'"  href="#">'.__('common.view').'</a>' : '').'</td>';
                    })
                    ->addColumn('showFile', function ($row): string {
                        $btn = '';
                        if ($row->file) {
                            $pdf = $row->file ? explode('.', @$row->file) : ' . ';
                            if ($pdf[1] == 'pdf') {
                                $btn = '<a class="text-color" href="'.url(@$row->file).'" download>'.__('common.download').' <span class="pl ti-download"></span></a>';
                            } elseif (file_exists($row->file)) {
                                $btn = '<a class="text-color showFile'.$row->id.' asset'.$row->id.'" onclick="showFile('.$row->id.');" data-asset="'.asset($row->file).'" data-file="'.url(@$row->file).'" data-toggle="modal" data-target="#showFile'.$row->id.'"  href="#">'.__('common.view').'</a>';
                            }
                        }

                        return $btn;
                    })
                    ->addColumn('createdDate', function ($row) {
                        return dateConvert($row->created_at);
                    })
                    ->addColumn('updatedDate', function ($row): string {
                        return '<td>'.(@$row->status == 'approve' || $row->status == 'reject' ? dateConvert($row->updated_at) : '').'</td>';
                    })
                    ->addColumn('action', function ($row): string {
                        return '<div class="dropdown CRM_dropdown">
                                    <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">'.app('translator')->get('common.select').'</button>
                                    <div class="dropdown-menu dropdown-menu-right">'.

                                (userPermission('wallet.approve-diposit') ? '<a class="dropdown-item approvePayment'.$row->id.' amount'.$row->id.'" onclick="approvePayment('.$row->id.');" data-user="'.$row->user_id.'" data-amount="'.$row->amount.'" data-toggle="modal" data-target="#approvePayment'.$row->id.'" href="#">'.__('wallet::wallet.approve').'</a>' : '').

                                (userPermission('wallet.reject-diposit') ? '<a class="dropdown-item rejectwalletPayment'.$row->id.' amount'.$row->id.'" onclick="rejectwalletPayment('.$row->id.');" data-user="'.$row->user_id.'" data-amount="'.$row->amount.'" data-toggle="modal" data-target="#rejectwalletPayment'.$row->id.'" href="#">'.__('wallet::wallet.reject').'</a>' : '').
                            '</div>
                                </div>';
                    })
                    ->rawColumns(['walletStatus', 'walletNote', 'walletRejectNote', 'showFile', 'updatedDate', 'action', 'date'])
                    ->make(true);
            }
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

        return null;
    }

    // Private Function

    private function getTotalWalletAmount(string $type, string $status)
    {
        return $walletTotalAmounts = WalletTransaction::where('type', $type)
            ->where('status', $status)
            ->where('school_id', Auth::user()->school_id)
            ->sum('amount');
    }

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
