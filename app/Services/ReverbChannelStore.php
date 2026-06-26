<?php

namespace App\Services;

use Laravel\Reverb\Protocols\Pusher\Contracts\ChannelManager;

class ReverbChannelStore
{
    protected static ?ChannelManager $channelManager = null;

    public static function set(ChannelManager $cm): void
    {
        self::$channelManager = $cm;
    }

    public static function get(): ?ChannelManager
    {
        return self::$channelManager;
    }
}
