<?php

namespace Modules\Chat\Http\Controllers\Api;

use App\ApiBaseMethod;
use App\Models\User;
use App\Traits\ImageStore;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Exception;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Mail;
use Modules\Chat\Http\Requests\UserUpdateRequest;
use Modules\Chat\Services\UserService;
use Modules\UserActivityLog\Traits\LogActivity;
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

        if ($validation->fails() && ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendError('Validation Error.', $validation->errors());
        }

        $keywords = $request->keywords;
        $users = $this->userService->search($keywords)->load('activeStatus');

        return response()->json(['keywords' => $keywords, 'users' => $users]);

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

            return response()->json(['message' => 'Data successfully updated!']);

        } catch (Exception $exception) {
            \LogActivity::errorLog($exception->getMessage());

            return response()->json(['message' => 'Something happened Wrong!']);

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
            return response()->json(['message' => 'Oops! your current password is wrong!']);

        }

        auth()->user()->update([
            'password' => bcrypt($request->password),
        ]);

        // Toastr::success(__('Your password updated!'));
        return response()->json(['message' => 'Your password updated!']);

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
            return response()->json(['message' => 'A reset link has been sent to your email address']);

            // return redirect()->back()->with('status', trans('A reset link has been sent to your email address.'));
        }

        return response()->json(['message' => 'A Network Error occurred. Please try again']);

        // return redirect()->back()->withErrors(['error' => trans('A Network Error occurred. Please try again.')]);

    }

    public function changeStatus($type)
    {
        if ($type > 3) {
            Toastr::error(__('Oops! Something went wrong!'));

            return response()->json(['message' => 'Oops! Something went wrong!']);

            // return redirect()->to(request()->url);
        }

        userStatusChange(auth()->id(), $type);

        // Toastr::success(__('Your Status successfully updated!'));
        return response()->json(['message' => 'Your Status successfully updated!']);

        // return redirect()->to(request()->url);
    }

    public function blockAction(string $type, $user)
    {
        try {
            $this->userService->blockAction($type, $user);
            $message = 'You just '.$type.' this user!';

            return response()->json(['message' => $message]);

            // return redirect()->route('chat.index');
        } catch (Exception $exception) {
            \LogActivity::errorLog($exception->getMessage());

            // Toastr::error('Something happened Wrong!', 'Error!!');
            return response()->json(['message' => 'Something happened Wrong!']);

        }
    }

    public function blockedUsers()
    {
        $users = $this->userService->allBlockedUsers();

        return response()->json(['users' => $users]);
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

        if ($validator->fails() && ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
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

        if ($validator->fails() && ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
        }

        try {
            app('general_settings')->put([
                'chatting_method' => $request['chat_method'],
            ]);

            if ($request->chat_method === 'pusher') {
                $this->updateDotEnv('BROADCAST_DRIVER', 'pusher');
                foreach ($request->except('_token', 'chat_method') as $key => $val) {
                    $this->putPermanentEnv(mb_strtoupper($key), $val);
                }
            } else {
                $this->updateDotEnv('BROADCAST_DRIVER', 'null');
            }

            Toastr::success(__('Chat method has been updated Successfully'));

            return redirect()->back();
        } catch (Exception $exception) {
            LogActivity::errorLog($exception->getMessage());
            Toastr::error('Something went Wrong!', 'Error!!');

            return redirect()->back();
        }
    }

    public function putPermanentEnv($key, $value): void
    {
        $path = app()->environmentFilePath();

        $escaped = preg_quote('='.env($key), '/');

        file_put_contents($path, preg_replace(
            sprintf('/^%s%s/m', $key, $escaped),
            sprintf('%s=%s', $key, $value),
            file_get_contents($path)
        ));
    }

    protected function updateDotEnv(string $key, string $newValue, string $delim = '')
    {

        $path = base_path('.env');
        // get old value from current env
        $oldValue = env($key);
        if ($oldValue === null) {
            $oldValue = 'null';
        }

        // was there any change?
        if ($oldValue === $newValue) {
            return;
        }

        // rewrite file content with changed data
        if (file_exists($path)) {
            // replace current value with new value
            file_put_contents(
                $path, str_replace(
                    $key.'='.$delim.$oldValue.$delim,
                    $key.'='.$delim.$newValue.$delim,
                    file_get_contents($path)
                )
            );
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
