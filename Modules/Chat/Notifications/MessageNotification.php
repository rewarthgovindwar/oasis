<?php

namespace Modules\Chat\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use SpondonIt\FCM\FcmMessage;

class MessageNotification extends Notification
{
    use Queueable;

    public $thread;

    public function __construct($thread)
    {
        $this->thread = $thread;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     */
    public function via($notifiable): array
    {
        return ['database', 'broadcast', 'fcm'];
    }

    public function toFcm($notifiable): FcmMessage
    {
        $fcmMessage = new FcmMessage();
        $notification = [
            'title' => __('chat::chat.new_message'),
            'body' => __('chat::chat.you_have_a_new_chat_message'),
            'phone_number' => $notifiable->phone_number,
            'deviceID' => $notifiable->device_token,
            'message' => $this->thread->message,
            'module' => 'chat',
        ];

        $data = [
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'id' => 1,
            'status' => 'done',
        ];
        $fcmMessage->content($notification)
            ->data($data)
            ->to($notifiable->device_token)
            ->priority(FcmMessage::PRIORITY_HIGH); // Optional - Default is 'normal'.

        return $fcmMessage;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', 'https://laravel.com')
            ->line('Thank you for using our application!');
    }

    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'thread' => $this->thread,
            'user' => auth()->user(),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'thread' => $this->thread,
            'user_name' => auth()->user()->first_name.' '.auth()->user()->last_name,
            'message' => $this->thread->message,
            'user' => auth()->user(),
        ]);
    }
}
