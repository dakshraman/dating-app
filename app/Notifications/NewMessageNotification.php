<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Message $message,
    ) {}

    public function via(object $notifiable): array
    {
        return [FcmChannel::class];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        $senderName = $this->message->sender?->name ?? 'Someone';

        return FcmMessage::create()
            ->notification(
                FcmNotification::create()
                    ->title($senderName)
                    ->body(match ($this->message->type) {
                        'image' => 'sent a photo',
                        'voice' => 'sent a voice message',
                        default => $this->message->content,
                    })
            )
            ->data([
                'conversation_id' => (string) $this->message->conversation_id,
                'message_id' => (string) $this->message->id,
                'sender_id' => (string) $this->message->sender_id,
                'type' => $this->message->type,
                'notification_type' => 'new_message',
            ]);
    }
}
