<?php

namespace App\Console\Commands;

use App\Models\ProfileBoost;
use Illuminate\Console\Command;

class ExpireBoosts extends Command
{
    protected $signature = 'dating:expire-boosts';

    protected $description = 'Expire profile boosts that have passed their expiration time';

    public function handle(): void
    {
        ProfileBoost::where('is_active', true)
            ->where('expires_at', '<=', now())
            ->update(['is_active' => false]);

        $this->info('Expired boosts successfully.');
    }
}
