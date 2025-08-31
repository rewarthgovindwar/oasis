<?php

namespace App\Jobs;

use App\Http\Controllers\Admin\SystemSettings\SmSystemSettingController;
use App\Notifications\CommunicateNotification;
use App\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class sendSmsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $sms;

    protected $title;

    protected $user;

    protected $numbers = [];

    public function __construct($sms, $title, $numbers, $user)
    {
        $this->sms = $sms;
        $this->title = $title;
        $this->numbers = $numbers;
        $this->user = $user;
    }

    public function handle(): void
    {

        try {
            foreach ($this->numbers as $number) {
                $notification_data = [];
                $notification_data['title'] = $this->title;
                $notification_data['body'] = $this->sms;
                $notification_data['phone_number'] = $number;
                $notification_data['deviceID'] = $this->user->device_token;
                // Notification::send($this->user, new CommunicateNotification($notification_data));
                $systemSettingController = new SmSystemSettingController();
                $systemSettingController->flutterNotificationSmsApi(new Request($notification_data));
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
        }

    }
}
