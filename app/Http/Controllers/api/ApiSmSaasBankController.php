<?php

namespace App\Http\Controllers\api;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\SmAcademicYear;
use App\SmBankAccount;
use App\SmBankPaymentSlip;
use App\SmBookCategory;
use App\SmNotification;
use App\SmPaymentMethhod;
use App\SmStudent;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ApiSmSaasBankController extends Controller
{
    public function saas_bankList(Request $request, $school_id)
    {
        try {
            $banks = SmBankAccount::where('active_status', 1)
                ->where('academic_id', SmAcademicYear::API_ACADEMIC_YEAR($school_id))
                ->where('school_id', $school_id)->get(['id', 'bank_name', 'account_name', 'account_number']);
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['banks'] = $banks->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }
        } catch (Throwable $throwable) {

        }

        return null;

    }

    public function saas_childBankSlipStore(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required',
                // 'class_id' => "required",
                // 'section_id' => "required",
                'user_id' => 'required',
                'fees_type_id' => 'required',
                'payment_mode' => 'required',
                'date' => 'required',
                'school_id' => 'required',
            ]);

            if ($request->payment_mode == 'bank' && empty($request->bank_id)) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Bank Field is required.',
                ], 422);
            }

            $fileName = '';
            if ($request->hasFile('slip')) {
                $file = $request->file('slip');
                $fileName = $request->input('user_id').time().'.'.$file->getClientOriginalExtension();
                $file->move('public/uploads/bankSlip/', $fileName);
                $fileName = 'public/uploads/bankSlip/'.$fileName;
            }

            $student = SmStudent::where('user_id', $request->user_id)->first();
            $details = $student->studentRecords->first();

            $newformat = date('Y-m-d', strtotime($request->date));
            $payment_mode_name = ucwords($request->payment_mode);
            $payment_method = SmPaymentMethhod::where('method', $payment_mode_name)->first();

            $smBankPaymentSlip = new SmBankPaymentSlip();
            $smBankPaymentSlip->date = $newformat;
            $smBankPaymentSlip->amount = $request->amount;
            $smBankPaymentSlip->note = $request->note;
            $smBankPaymentSlip->slip = $fileName;
            $smBankPaymentSlip->fees_type_id = $request->fees_type_id;
            $smBankPaymentSlip->student_id = $student->id;
            $smBankPaymentSlip->payment_mode = $request->payment_mode;
            if ($payment_method->id == 3) {
                $smBankPaymentSlip->bank_id = $request->bank_id;
            }

            $smBankPaymentSlip->class_id = $details->class_id;
            $smBankPaymentSlip->section_id = $details->section_id;
            $smBankPaymentSlip->school_id = $request->school_id;
            $smBankPaymentSlip->academic_id = SmAcademicYear::API_ACADEMIC_YEAR($request->school_id);
            $result = $smBankPaymentSlip->save();

            if ($result) {
                $users = User::whereIn('role_id', [1, 5])->where('school_id', 1)->get();
                foreach ($users as $user) {
                    $notification = new SmNotification();
                    $notification->message = $student->full_name.' Payment Received';
                    $notification->is_read = 0;
                    $notification->url = 'bank-payment-slip';
                    $notification->user_id = $user->id;
                    $notification->role_id = $user->role_id;
                    $notification->school_id = $request->school_id;
                    $notification->academic_id = $student->academic_id;
                    $notification->date = date('Y-m-d');
                    $notification->save();
                }
            }

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Payment Added, Please Wait for approval.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Something went wrong. Please try again.',
            ]);
        }
    }

    public function saas_roomList(Request $request)
    {
        $studentDormitory = DB::table('sm_room_lists')
            ->join('sm_dormitory_lists', 'sm_room_lists.dormitory_id', '=', 'sm_dormitory_lists.id')
            ->join('sm_room_types', 'sm_room_lists.room_type_id', '=', 'sm_room_types.id')
            ->select('sm_room_lists.id', 'sm_dormitory_lists.dormitory_name', 'sm_room_lists.name as room_number', 'sm_room_lists.number_of_bed', 'sm_room_lists.cost_per_bed', 'sm_room_lists.active_status')
            ->get();

        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendResponse($studentDormitory, null);
        }

        return null;
    }

    public function saas_bookCategory(Request $request, $school_id)
    {
        $book_category = DB::table('sm_book_categories')->where('school_id', $school_id)->get();

        // Return a JSON response with a success flag
        return response()->json([
            'success' => true,
            'data' => $book_category,
            'message' => 'Book categories retrieved successfully.',
        ]);
    }

    public function saas_bookCategoryStore(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'category_name' => 'required|max:200|unique:sm_book_categories,category_name',
            'school_id' => 'required',
        ]);
        if ($validator->fails() && ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
        }

        try {
            $smBookCategory = new SmBookCategory();
            $smBookCategory->category_name = $request->category_name;
            $smBookCategory->school_id = $request->school_id;
            $results = $smBookCategory->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($results) {
                    return ApiBaseMethod::sendResponse(null, 'Book Category has been created successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again.');

            }

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

        return null;

    }
}
