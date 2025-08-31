<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class EmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $body;

    protected $details = [];

    public function __construct($body, $data)
    {
        $this->body = $body;
        $this->details = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->details['driver'] === 'php') {
            $receiver_name = $this->details['receiver_name'];
            $reciver_email = $this->details['reciver_email'];
            $sender_email = $this->details['sender_email'];
            $subject = $this->details['subject'];

            $view = view('backEnd.email.emailBody', ['body' => $this->body]);
            $message = (string) $view;
            $headers = "From: <{$sender_email}> \r\n";
            $headers .= "Reply-To: {$receiver_name} <{$reciver_email}> \r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=utf-8\r\n";
            @mail($reciver_email, $subject, $message, $headers);
        } elseif ($this->details['driver'] === 'smtp') {

            Mail::send('backEnd.email.emailBody', ['body' => $this->body], function ($message): void {
                $message->to($this->details['reciver_email'], $this->details['receiver_name'])->subject($this->details['subject']);
                $message->from($this->details['sender_email'], $this->details['sender_name']);
            });
        }
    }
}
