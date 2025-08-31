<?php

namespace Modules\Chat\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use SpondonIt\FCM\FcmMessage;

class InvitationNotification extends Notification
{
    use Queueable;

    public $invitation;

    public $message;

    public function __construct($invitation, $message)
    {
        $this->invitation = $invitation;
        $this->message = $message;
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
            'title' => __('chat::chat.group_invitation'),
            'body' => __('chat::chat.you_are_invited_in_a_new_group'),
            'phone_number' => $notifiable->phone_number,
            'deviceID' => $notifiable->device_token,
            'invitation' => $this->invitation,
            'module' => 'chat',
        ];

        $data = [
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'id' => 1,
            'status' => 'done',
            'message' => $notification,
        ];
        $fcmMessage->content($notification)
            ->data($data)
            ->to($notifiable->device_token)
            ->priority(FcmMessage::PRIORITY_HIGH); // Optional - Default is 'normal'.

        return $fcmMessage;
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
            'invitation' => $this->invitation,
            'user' => auth()->user(),
            'message' => $this->message,
            'url' => route('chat.invitation'),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'invitation' => $this->invitation,
            'user' => auth()->user(),
            'message' => $this->message,
            'url' => route('chat.invitation'),
        ]);
    }
}
