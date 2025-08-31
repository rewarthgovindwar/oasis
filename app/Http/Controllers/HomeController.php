<?php

namespace App\Http\Controllers;

use App\SmToDo;
use App\SmEvent;
use App\SmStaff;
use App\SmHoliday;
use App\SmStudent;
use App\YearCheck;
use Carbon\Carbon;
use App\SmAddIncome;
use App\SmAddExpense;
use App\SmNoticeBoard;
use App\GlobalVariable;
use Illuminate\Http\Request;
use App\Models\SmCalendarSetting;
use App\Models\DueFeesLoginPrevent;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Modules\Lead\Entities\LeadReminder;
use Modules\Saas\Entities\SmPackagePlan;
use Modules\RolePermission\Entities\InfixRole;
use Modules\Wallet\Entities\WalletTransaction;

use Modules\RolePermission\Entities\Permission;
use Modules\Saas\Entities\SmSubscriptionPayment;
class HomeController extends Controller
{

    public function dashboard()
    {

        $user = Auth::user();
        $role_id = $user->role_id;
        $is_due_fees_login_permission = generalSetting()->due_fees_login;
        $due_fees_login_prevent = DueFeesLoginPrevent::where('user_id', $user->id)->where('school_id', $user->school_id)->where('role_id', $role_id)->first();
        
        if (($user->role_id == 1) && ($user->is_administrator == 'yes') && (moduleStatusCheck('Saas') == true)) {   // SuperAdmin
            return redirect('superadmin-dashboard');
        }

        if (($user->is_administrator == 'yes') && (moduleStatusCheck('Saas') == true) && (moduleStatusCheck('SaasHr') == true)) {
            return redirect('superadmin-dashboard');
        }

            if ($role_id == 2) {    #Student
                if($is_due_fees_login_permission == 1 && $due_fees_login_prevent != null){
                    $errorMessage = '';
                    Auth::logout();
                    session(['role_id' => '']);
                    Session::flash('toast_message', [
                        'type' => 'error', // 'success', 'info', 'warning', 'error'
                        'message' => 'Operation Failed, Unable to log in due to unpaid fees.'
                    ]);
                    return redirect('login')->withErrors(['custom_error' => $errorMessage]);
                } else {
                    return redirect('student-dashboard');
                }
            } elseif ($role_id == 3) {  #Parent
                if($is_due_fees_login_permission == 1 && $due_fees_login_prevent != null){
                    $errorMessage = '';
                    Auth::logout();
                    session(['role_id' => '']);
                    Session::flash('toast_message', [
                        'type' => 'error', // 'success', 'info', 'warning', 'error'
                        'message' => 'Operation Failed, Unable to log in due to unpaid fees.'
                    ]);
                    return redirect('login')->withErrors(['custom_error' => $errorMessage]);
                } else {
                    return redirect('parent-dashboard');
                }
            } elseif($role_id == GlobalVariable::isAlumni()) {  #Alumni
                return redirect('alumni-dashboard');
            } elseif ($role_id == "") {
                return redirect('login');
            } elseif (Auth::user()->is_saas == 1) {
                return redirect('saasStaffDashboard');
            }
            else {
                return redirect('admin-dashboard');
            }
    }

    // for display dashboard
    public function index(Request $request)
    {
        $chart_data = ' ';
        $day_incomes = SmAddIncome::where('academic_id', getAcademicId())
            ->where('name', '!=', 'Fund Transfer')
            ->where('school_id', Auth::user()->school_id)
            ->where('active_status', 1)
            ->where('date', '>=', date('Y').'-01-01')
            ->where('date', '<=', date('Y-m-d'))
            ->get(['amount', 'date']);

        $day_expenses = SmAddExpense::where('academic_id', getAcademicId())
            ->where('name', '!=', 'Fund Transfer')
            ->where('school_id', Auth::user()->school_id)
            ->where('active_status', 1)
            ->where('date', '>=', date('Y').'-01-01')
            ->where('date', '<=', date('Y-m-d'))
            ->get(['amount', 'date']);

        $m_total_income = $day_incomes->where('name', '!=', 'Fund Transfer')
            ->where('date', '>=', date('Y-m-01'))
            ->sum('amount');

        $m_total_expense = $day_expenses->where('name', '!=', 'Fund Transfer')
            ->where('date', '>=', date('Y-m-01'))
            ->sum('amount');

        for ($i = 1; $i <= date('d'); $i++) {
            $i = $i < 10 ? '0'.$i : $i;
            $income = $day_incomes->filter(function ($value) use ($i): bool {
                return $value->date->day == $i && $value->date->month == date('m');
            })->sum('amount');

            $expense = $day_expenses->filter(function ($value) use ($i): bool {
                return $value->date->day == $i && $value->date->month == date('m');
            })->sum('amount');

            $chart_data .= "{ day: '".$i."', income: ".@$income.', expense:'.@$expense.' },';
        }

        $chart_data_yearly = '';
        for ($i = 1; $i <= date('m'); $i++) {
            $i = $i < 10 ? '0'.$i : $i;
            $yearlyIncome = $day_incomes->filter(function ($value) use ($i): bool {
                return $value->date->month == $i;
            })->sum('amount');
            $yearlyExpense = $day_expenses->filter(function ($value) use ($i): bool {
                return $value->date->month == $i;
            })->sum('amount');
            $chart_data_yearly .= "{ y: '".$i."', income: ".@$yearlyIncome.', expense:'.@$yearlyExpense.' },';
        }

        $count_event = 0;
        $SaasSubscription = isSubscriptionEnabled();
        $saas = moduleStatusCheck('Saas');
        if ($SaasSubscription && ! SmPackagePlan::isSubscriptionAutheticate()) {
            return redirect('subscription/package-list');
        }

        $user_id = Auth::id();
        $school_id = Auth::user()->school_id;

        if (isSubscriptionEnabled()) {
            $last_payment = SmSubscriptionPayment::where('school_id', Auth::user()->school_id)
                ->where('start_date', '<=', Carbon::now())
                ->where('end_date', '>=', Carbon::now())
                ->where('approve_status', '=', 'approved')
                ->latest()->first();
            $package_info = [];

            if ($last_payment) {
                $package = SmPackagePlan::find($last_payment->package_id);

                $total_days = $package->payment_type == 'trial' ? $package->trial_days : $package->duration_days;
                $now_time = date('Y-m-d');
                $now_time = date('Y-m-d', strtotime($now_time.' + 1 days'));
                $end_date = date('Y-m-d', strtotime($last_payment->end_date));

                $formatted_dt1 = Carbon::parse($now_time);
                $formatted_dt2 = Carbon::parse($last_payment->end_date);
                $remain_days = $formatted_dt1->diffInDays($formatted_dt2);

                $package_info['package_name'] = $package->name;
                $package_info['student_quantity'] = $package->student_quantity;
                $package_info['staff_quantity'] = $package->staff_quantity;
                $package_info['remaining_days'] = $remain_days;
                $package_info['expire_date'] = date('Y-m-d', strtotime($last_payment->end_date.' + 1 days'));
            }

        }

        // for current month start
        if (moduleStatusCheck('Wallet')) {
            $monthlyWalletBalance = $this->showWalletBalance('diposit', 'refund', 'expense', 'fees_refund', 'Y-m-', $school_id);
        }

        // for current month end

        // for current year start
        $y_total_income = $day_incomes->where('name', '!=', 'Fund Transfer')
            ->where('date', '>=', date('Y-01-01'))
            ->sum('amount');

        $y_total_expense = $day_expenses->where('name', '!=', 'Fund Transfer')
            ->where('date', '>=', date('Y-01-01'))
            ->sum('amount');

        if (moduleStatusCheck('Wallet')) {
            $yearlyWalletBalance = $this->showWalletBalance('diposit', 'refund', 'expense', 'fees_refund', 'Y-', $school_id);
        }

        // for current year end

        if (Auth::user()->role_id == 4) {
            $events = SmEvent::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', $school_id)
                ->where(function ($q): void {
                    $q->where('for_whom', 'All')->orWhere('for_whom', 'Teacher');
                })
                ->get();
        } else {
            $events = SmEvent::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->where('for_whom', 'All')
                ->get();
        }

        $staffs = SmStaff::where('school_id', $school_id)
            ->where('active_status', 1);

        $holidays = SmHoliday::where('active_status', 1)
            ->where('academic_id', getAcademicId())
            ->where('school_id', $school_id)
            ->get();

        $calendar_events = [];
        foreach ($holidays as $k => $holiday) {
            $calendar_events[$k]['title'] = $holiday->holiday_title;
            $calendar_events[$k]['start'] = $holiday->from_date;
            $calendar_events[$k]['end'] = Carbon::parse($holiday->to_date)->addDays(1)->format('Y-m-d');
            $calendar_events[$k]['description'] = $holiday->details;
            $calendar_events[$k]['url'] = $holiday->upload_image_file;
            $count_event = $k;
            $count_event++;
        }

        foreach ($events as $event) {
            $calendar_events[$count_event]['title'] = $event->event_title;
            $calendar_events[$count_event]['start'] = $event->from_date;
            $calendar_events[$count_event]['end'] = Carbon::parse($event->to_date)->addDays(1)->format('Y-m-d');
            $calendar_events[$count_event]['description'] = $event->event_des;
            $calendar_events[$count_event]['url'] = $event->uplad_image_file;
            $count_event++;
        }

        // added by abu nayem -for lead
        if (moduleStatusCheck('Lead') == true) {
            $reminders = LeadReminder::with('lead:first_name,last_name,id')->where('academic_id', getAcademicId())
                ->where('school_id', $school_id)
                ->when(auth()->user()->role_id !== 1 && auth()->user()->staff, function ($q): void {
                    $q->where('reminder_to', auth()->user()->staff->id);
                })->get();
            foreach ($reminders as $reminder) {
                $calendar_events[$count_event]['title'] = 'Lead Reminder';
                $calendar_events[$count_event]['start'] = Carbon::parse($reminder->date_time)->format('Y-m-d').' '.$reminder->time;
                $calendar_events[$count_event]['end'] = Carbon::parse($reminder->date_time)->format('Y-m-d');
                $calendar_events[$count_event]['description'] = view('lead::lead_calender', ['event' => $reminder])->render();
                $calendar_events[$count_event]['url'] = 'lead/show/'.$reminder->id;
                $count_event++;
            }
        }

        // end lead reminder

        $notices = SmNoticeBoard::query();
        $notices->where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', $school_id)->where('publish_on', '<=', date('Y-m-d'));
        $notices->when(auth()->user()->role_id !== 1, function ($query): void {
            $query->where('inform_to', 'LIKE', '%'.auth()->user()->role_id.'%');
        });
        $notices = $notices->get();

        $all_staffs = $staffs->where('role_id', '!=', 1)
            ->where('school_id', $school_id)->get();
        $all_students = SmStudent::where('active_status', 1)
            ->where('school_id', $school_id)->get();
        $data = [
            'totalStudents' => $all_students->count(),
            'totalParents' => $all_students->whereNotNull('parent_id')->unique('parent_id')
                ->count(),

            'totalTeachers' => $all_staffs->where('role_id', 4)->count(),

            'totalStaffs' => $all_staffs->count(),

            'toDos' => SmToDo::where('created_by', $user_id)
                ->where('school_id', $school_id)
                ->get(),

            'notices' => $notices,

            // where('inform_to', 'LIKE', '%2%')

            'm_total_income' => $m_total_income,
            'y_total_income' => $y_total_income,
            'm_total_expense' => $m_total_expense,
            'y_total_expense' => $y_total_expense,
            'holidays' => $holidays,
            'events' => $events,

            'year' => YearCheck::getYear(),
        ];
        if (moduleStatusCheck('Wallet')) {
            $data['monthlyWalletBalance'] = $monthlyWalletBalance;
            $data['yearlyWalletBalance'] = $yearlyWalletBalance;
        }

        if (Session::has('info_check')) {
            session(['info_check' => 'no']);
        } else {
            session(['info_check' => 'yes']);
        }

        $data['settings'] = SmCalendarSetting::get();
        $data['roles'] = InfixRole::where('is_saas', 0)->where(function ($q): void {
            $q->where('school_id', auth()->user()->school_id)->orWhere('type', 'System');
        })
            ->whereNotIn('id', [1, 2])
            ->get();
            $academicCalendar = new SmAcademicCalendarController();
            $data['events'] = $academicCalendar->calenderData();
            if(isSubscriptionEnabled()){
                return view('backEnd.dashboard',compact('chart_data','chart_data_yearly','calendar_events','package_info'))->with($data);
            }else{
                return view('backEnd.dashboard',compact('chart_data','chart_data_yearly','calendar_events'))->with($data);
            }
        return view('backEnd.dashboard', ['chart_data' => $chart_data, 'chart_data_yearly' => $chart_data_yearly, 'calendar_events' => $calendar_events])->with($data);

    }

    public function saveToDoData(Request $request)
    {

        $toDolists = new SmToDo();
        $toDolists->todo_title = $request->todo_title;
        $toDolists->date = date('Y-m-d', strtotime($request->date));
        $toDolists->created_by = Auth()->user()->id;
        $toDolists->school_id = Auth()->user()->school_id;
        $toDolists->academic_id = getAcademicId();
        $results = $toDolists->save();

            if ($results) {
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
    }

    public function viewToDo($id)
    {

        if (checkAdmin()) {
            $toDolists = SmToDo::find($id);
        } else {
            $toDolists = SmToDo::where('id', $id)->where('school_id', Auth::user()->school_id)->first();
        }

        return view('backEnd.dashboard.viewToDo', compact('toDolists'));

    }

    public function editToDo($id)
    {
            // $editData = SmToDo::find($id);
        if (checkAdmin() == true) {
            $editData = SmToDo::find($id);
        } else {
            $editData = SmToDo::where('id', $id)->where('school_id', Auth::user()->school_id)->first();
        }

        return view('backEnd.dashboard.editToDo', compact('editData', 'id'));

    }

    public function updateToDo(Request $request)
    {

        $to_do_id = $request->to_do_id;
        $toDolists = SmToDo::find($to_do_id);
        $toDolists->todo_title = $request->todo_title;
        $toDolists->date = date('Y-m-d', strtotime($request->date));
        $toDolists->complete_status = $request->complete_status;
        $toDolists->updated_by = Auth()->user()->id;
        $results = $toDolists->update();

            if ($results) {
                Toastr::success('Operation successful', 'Success');
                return redirect()->back();
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
    }

    public function removeToDo(Request $request)
    {

        $to_do = SmToDo::find($request->id);
        $to_do->complete_status = 'C';
        $to_do->academic_id = getAcademicId();
        $to_do->save();
        $html = '';

        return response()->json('html');

    }

    public function getToDoList(Request $request)
    {

        $to_do_list = SmToDo::where('complete_status', 'C')->where('school_id', Auth::user()->school_id)->get();
        $datas = [];
        foreach ($to_do_list as $to_do) {
            $datas[] = [
                'title' => $to_do->todo_title,
                'date' => date('jS M, Y', strtotime($to_do->date)),
            ];
        }

        return response()->json($datas);

    }

    public function viewNotice($id)
    {

        $notice = SmNoticeBoard::find($id);

        return view('backEnd.dashboard.view_notice', compact('notice'));

    }

    public function updatePassowrd()
    {
        return view('backEnd.update_password');
    }

    public function updatePassowrdStore(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|same:confirm_password|min:6|different:current_password',
            'confirm_password' => 'required|min:6',
        ]);

            $user = Auth::user();
            if (Hash::check($request->current_password, $user->password)) {
                $user->password = Hash::make($request->new_password);
                $result = $user->save();
                if ($result){
                    Toastr::success('Operation successful', 'Success');
                    return redirect()->back();
                    // return redirect()->back()->with('message-success', 'Password has been changed successfully');
                } else {
                    Toastr::error('Operation Failed', 'Failed');
                    return redirect()->back();
                    // return redirect()->back()->with('message-danger', 'Something went wrong, please try again');
                }
            } else {
                Toastr::error('Current password not match!', 'Failed');
                return redirect()->back();
                // return redirect()->back()->with('password-error', 'You have entered a wrong current password');
            }
    }

    public function userCustomMenu($slug = null)
    {
        $custom_menus = \Modules\CustomMenu\Entities\CustomMenu::where('active_status', 1)->where('slug', $slug)->first();
        if (! empty($custom_menus) && $custom_menus->menu_type == 'url') {
            return redirect()->to($custom_menus->url_link);
        }

        $menu_item = $custom_menus;

        return view('backEnd.userCustomMenu.index', ['menu_item' => $menu_item]);
    }

    private function showWalletBalance(string $diposit, string $refund, string $expense, string $feesRefund, string $date, $school_id)
    {

        $walletTranscations = WalletTransaction::where('status', 'approve')
            ->where('updated_at', 'like', date($date).'%')
            ->where('school_id', $school_id)
            ->get();

        $totalWalletBalance = $walletTranscations->where('type', $diposit)->sum('amount');
        $totalWalletRefundBalance = $walletTranscations->where('type', $refund)->sum('amount');
        $totalWalletExpenseBalance = $walletTranscations->where('type', $expense)->sum('amount');
        $totalFeesRefund = $walletTranscations->where('type', $feesRefund)->sum('amount');

        return ($totalWalletBalance - $totalWalletExpenseBalance) - $totalWalletRefundBalance + $totalFeesRefund;
    }
}
