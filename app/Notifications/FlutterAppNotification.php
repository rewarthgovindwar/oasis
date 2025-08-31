<?php

namespace App\Notifications;

use App\SmNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use SpondonIt\FCM\FcmMessage;

class FlutterAppNotification extends Notification
{
    use Queueable;

    private $sm_notification;

    private $title;

    public function __construct(SmNotification $smNotification, $title)
    {
        $this->sm_notification = $smNotification;
        $this->title = $title;
    }

    public function via($notifiable): array
    {
        return ['fcm'];
    }

    public function toFcm($notifiable): FcmMessage
    {
        $fcmMessage = new FcmMessage();
        $notification = [
            'title' => $this->title,
            'body' => $this->sm_notification->message,
        ];
        $data = [
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'id' => 1,
            'status' => 'done',
            'message' => $notification,
            'image' => 'https://freeschoolsoftware.in/spn4/infixedu/v7.0.1/public/uploads/settings/logo.png',
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
