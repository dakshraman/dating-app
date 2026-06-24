<?php

namespace App\Console\Commands;

use App\Models\Message;
use Illuminate\Console\Command;

class ExpireVanishedMessages extends Command
{
    protected $signature = 'messages:expire-vanished';

    protected $description = 'Delete messages that have expired in 24h vanish mode';

    public function handle(): void
    {
        $deleted = Message::whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->delete();

        $this->info("Deleted {$deleted} expired messages.");
    }
}
