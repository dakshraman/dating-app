<?php

namespace App\Notifications;

use App\Models\UserMatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class NewMatchNotification extends Notification
{
    use Queueable;

    public function __construct(
        public UserMatch $match
    ) {}

    public function via(object $notifiable): array
    {
        return [FcmChannel::class];
    }

    public function toFcm(object $notifiable): FcmMessage
    {
        $otherUser = $this->match->getOtherUser($notifiable);

        return FcmMessage::create()
            ->notification(
                FcmNotification::create()
                    ->title('New Match!')
                    ->body("You matched with {$otherUser->name}!")
            )
            ->data([
                'type' => 'new_match',
                'match_id' => (string) $this->match->id,
            ]);
    }
}
