<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestAdminPages extends Command
{
    protected $signature = 'test:admin';
    protected $description = 'Test all admin pages for errors';

    public function handle()
    {
        $user = \App\Models\User::where('email', 'admin@gmail.com')->first();
        Auth::login($user);

        $pages = [
            '/admin' => 'Dashboard',
            '/admin/users' => 'Users list',
            '/admin/users/1/edit' => 'Edit user',
            '/admin/matches/user-matches' => 'Matches',
            '/admin/conversations' => 'Conversations',
            '/admin/conversations/1/edit' => 'Edit conversation',
            '/admin/reports' => 'Reports',
            '/admin/interests' => 'Interests',
            '/admin/subscription-plans' => 'Subscription plans',
            '/admin/user-photos' => 'User photos',
            '/admin/profile-boosts' => 'Profile boosts',
            '/admin/user-subscriptions' => 'User subscriptions',
            '/admin/dating-settings' => 'Dating settings',
        ];

        $ok = 0;
        $fail = 0;

        foreach ($pages as $path => $label) {
            try {
                $response = $this->call('GET', $path);
                $status = $response->getStatusCode();
                if ($status === 200) {
                    $this->info("  OK: $label ($path)");
                    $ok++;
                } else {
                    $this->error("  FAIL($status): $label ($path)");
                    $fail++;
                }
            } catch (\Throwable $e) {
                $this->error("  ERROR: $label ($path) - " . $e->getMessage());
                $fail++;
            }
        }

        $this->newLine();
        $this->line("Results: $ok passed, $fail failed");
        return $fail === 0 ? 0 : 1;
    }
}
