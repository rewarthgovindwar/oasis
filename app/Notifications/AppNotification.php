<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use SpondonIt\FCM\FcmMessage;

class AppNotification extends Notification
{
    use Queueable;

    private $notificationData;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($notificationData)
    {
        $this->notificationData = $notificationData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     */
    public function via($notifiable): array
    {
        return ['fcm'];
    }

    public function toFcm($notifiable): FcmMessage
    {
        $fcmMessage = new FcmMessage();
        $notification = [
            'title' => gv($this->notificationData, 'title', null),
            'body' => gv($this->notificationData, 'message', null),
        ];
        $data = [
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'id' => 1,
            'status' => 'done',
            'message' => $notification,
        ];
        $fcmMessage->content($notification)
            ->data($data)
            ->priority(FcmMessage::PRIORITY_HIGH); // Optional - Default is 'normal'.

        return $fcmMessage;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
