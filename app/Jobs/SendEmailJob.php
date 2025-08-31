<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $data = [];

    protected $details;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $details)
    {
        $this->data = $data;
        $this->details = $details;
    }

    /**
     * Execute the job.
     */
    public function handle(Mailer $mailer): void
    {
        $mailer->send('backEnd.emails.mail', ['data' => $this->data], function ($message): void {
            $message->from($this->data['system_email'], $this->data['school_name']);
            $message->to($this->details)->subject($this->data['email_sms_title']);
        });
    }
}
