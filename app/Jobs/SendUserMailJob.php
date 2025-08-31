<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendUserMailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $user_info = [];

    protected $sender;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_info, $sender)
    {
        $this->user_info = $user_info;
        $this->sender = $sender;
    }

    /**
     * Execute the job.
     */
    public function handle(Mailer $mailer): void
    {

        foreach ($this->user_info as $info) {

            @send_mail($info['email'], $info['name'], 'Login Credentials', 'backEnd.studentInformation.user_credential');
        }
    }
}
