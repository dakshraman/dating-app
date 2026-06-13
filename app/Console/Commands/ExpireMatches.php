<?php

namespace App\Console\Commands;

use App\Models\UserMatch;
use Illuminate\Console\Command;

class ExpireMatches extends Command
{
    protected $signature = 'dating:expire-matches';

    protected $description = 'Expire matches with no messages after 7 days';

    public function handle(): void
    {
        UserMatch::whereNull('expires_at')
            ->whereDoesntHave('conversation.messages')
            ->where('matched_at', '<', now()->subDays(7))
            ->update(['expires_at' => now()]);

        UserMatch::whereNull('expires_at')
            ->whereHas('conversation', function ($q) {
                $q->where('last_message_at', '<', now()->subDays(30));
            })
            ->update(['expires_at' => now()]);

        $this->info('Expired inactive matches successfully.');
    }
}
