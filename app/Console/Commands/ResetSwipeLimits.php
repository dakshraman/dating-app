<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

class ResetSwipeLimits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swipes:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset daily swipe and super like limits for all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \App\Models\User::query()->update([
            'remaining_swipes' => 10,
            'remaining_super_likes' => 5,
        ]);
        
        $this->info('Swipe limits reset successfully.');
    }
}
