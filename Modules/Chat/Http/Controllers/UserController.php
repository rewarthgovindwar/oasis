<?php

namespace Modules\Chat\Http\Controllers;

use App\Models\User;
use App\Traits\ImageStore;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Exception;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use LogActivity;
use Mail;
use Modules\Chat\Http\Requests\UserUpdateRequest;
use Modules\Chat\Services\UserService;
use Str;

class UserController extends Controller
{
    use ImageStore;

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        return view('chat::index');
    }

    public function create()
    {
        return view('chat::create');
    }

    public function store(Request $request): void
    {
        //
    }

    public function show($id)
    {
        return view('chat::show');
    }

    public function edit($id)
    {
        return view('chat::edit');
    }

    public function update(Request $request, $id): void
    {
        //
    }

    public function destroy($id): void
    {
        //
    }

    public function search(Request $request)
    {
        $validation = \Validator::make($request->all(), [
            'keywords' => 'required',
        ]);

        if ($validation->fails()) {
            Toastr::error(__('validation.required', ['attribute' => 'Keywords']));

            return redirect()->back();
        }

        $keywords = $request->keywords;
        $users = $this->userService->search($keywords)->load('activeStatus');

        return view('chat::user.search_result', ['keywords' => $keywords, 'users' => $users]);

    }

    public function profile()
    {
        return view('chat::user.profile');
    }

    public function profileUpdate(UserUpdateRequest $userUpdateRequest)
    {
        if ($userUpdateRequest->profile_avatar !== null) {
            $userUpdateRequest['avatar'] = $this->saveAvatarImage($userUpdateRequest->profile_avatar);
        }

        try {
            $this->userService->profileUpdate($userUpdateRequest->except('_token', 'profile_avatar'));
            Toastr::success(__('Data successfully updated!'));

            return redirect()->back();
        } catch (Exception $exception) {
            LogActivity::errorLog($exception->getMessage());
            Toastr::error('Something happened Wrong!', 'Error!!');

            return redirect()->back();
        }
    }

    public function passwordUpdate(Request $request)
    {
        $validation = \Validator::make($request->all(), [
            'current_password' => 'min:8|required',
            'password' => 'min:8|required|confirmed',
        ]);

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation);
        }

        if (! Hash::check($request->current_password, auth()->user()->password)) {
            Toastr::error(__('Oops! your current password is wrong!'));

            return redirect()->back();
        }

        auth()->user()->update([
            'password' => bcrypt($request->password),
        ]);
        Toastr::success(__('Your password updated!'));

        return redirect()->back();
    }

    public function passwordResetLink(Request $request)
    {
        \Validator::make($request->all(), [
            'email' => 'email|required',
        ]);

        $user = User::where('email', '=', $request->email)
            ->exists();
        if (count($user) < 1) {
            return redirect()->back()->withErrors(['email' => trans('User does not exist')]);
        }

        \DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => Str::random(60),
            'created_at' => Carbon::now(),
        ]);

        DB::table('password_resets')
            ->where('email', $request->email)->first();

        if ($this->sendResetEmail($request->email)) {
            return redirect()->back()->with('status', trans('A reset link has been sent to your email address.'));
        }

        return redirect()->back()->withErrors(['error' => trans('A Network Error occurred. Please try again.')]);

    }

    public function changeStatus($type)
    {
        if ($type > 3) {
            Toastr::error(__('Oops! Something went wrong!'));

            return redirect()->to(request()->url);
        }

        userStatusChange(auth()->id(), $type);
        Toastr::success(__('Your Status successfully updated!'));

        return redirect()->to(request()->url);
    }

    public function blockAction(string $type, $user)
    {
        try {
            $this->userService->blockAction($type, $user);
            Toastr::success(__('You just '.$type.' this user!'));

            return redirect()->route('chat.index');
        } catch (Exception $exception) {
            LogActivity::errorLog($exception->getMessage());
            Toastr::error('Something happened Wrong!', 'Error!!');

            return redirect()->back();
        }
    }

    public function blockedUsers()
    {
        $users = $this->userService->allBlockedUsers();

        return view('chat::user.blocked', ['users' => $users]);
    }

    public function settings()
    {
        return view('chat::settings');
    }

    public function invitationRequirementSetting(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invitation_requirement' => 'required',
        ]);

        if ($validator->fails()) {
            Toastr::error($validator->messages());

            return redirect()->back();
        }

        app('general_settings')->put('chat_invitation_requirement', $request->invitation_requirement);
        Toastr::success('Invitation requirement setting updated!', 'Success');

        return redirect()->back();
    }

    public function settingsUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pusher_app_id' => 'required_if:chat_method,pusher',
            'pusher_app_key' => 'required_if:chat_method,pusher',
            'pusher_app_secret' => 'required_if:chat_method,pusher',
            'pusher_app_cluster' => 'required_if:chat_method,pusher',
        ]);

        if ($validator->fails()) {
            Toastr::error($validator->messages());

            return redirect()->back();
        }

        app('general_settings')->put([
            'chatting_method' => $request->chat_method,
            'pusher_app_id' => $request->pusher_app_id,
            'pusher_app_key' => $request->pusher_app_key,
            'pusher_app_secret' => $request->pusher_app_secret,
            'pusher_app_cluster' => $request->pusher_app_cluster,
        ]);

        try {
            app('general_settings')->put([
                'chatting_method' => $request->chat_method,
                'pusher_app_id' => $request->pusher_app_id,
                'pusher_app_key' => $request->pusher_app_key,
                'pusher_app_secret' => $request->pusher_app_secret,
                'pusher_app_cluster' => $request->pusher_app_cluster,
            ]);
            Toastr::success(__('Chat method has been updated Successfully'));

            return redirect()->back();
        } catch (Exception $exception) {
            Toastr::error('Something went Wrong!', 'Error!!');

            return redirect()->back();
        }
    }

    private function sendResetEmail($email): bool
    {
        $user = DB::table('users')->where('email', $email)->select('firstname', 'email')->first();

        try {
            Mail::send([], [], function ($message) use ($user): void {
                $message->to($user->email)
                    ->subject('Password Reset')
                    ->setBody('Hi, welcome user!')
                    ->setBody('<h1>Hi, welcome user!</h1><br> <a href="">{{ $token}}</a>', 'text/html'); // for HTML rich messages
            });

            return true;
        } catch (Exception $exception) {
            return false;
        }
    }
}
