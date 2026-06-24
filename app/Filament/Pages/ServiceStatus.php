<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\HtmlString;
use UnitEnum;

class ServiceStatus extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-server-stack';

    protected static ?string $navigationLabel = 'Service Status';

    protected static string|UnitEnum|null $navigationGroup = 'System';

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                new HtmlString(view('filament.pages.service-status')->render()),
            ]);
    }

    public function getServices(): array
    {
        return [
            [
                'key' => 'dating-reverb',
                'name' => 'Reverb (WebSockets)',
                'description' => 'Real-time WebSocket server for chat, typing indicators, and live updates. Runs on port 8091.',
                'type' => 'supervisor',
                'supports' => ['start', 'stop', 'restart'],
            ],
            [
                'key' => 'dating-worker',
                'name' => 'Queue Worker',
                'description' => 'Processes queued jobs such as FCM push notifications and email delivery.',
                'type' => 'supervisor',
                'supports' => ['start', 'stop', 'restart'],
            ],
            [
                'key' => 'fcm',
                'name' => 'FCM Push Notifications',
                'description' => 'Firebase Cloud Messaging — sends push notifications to mobile devices for matches and messages.',
                'type' => 'service',
                'supports' => ['test'],
            ],
            [
                'key' => 'database',
                'name' => 'Database',
                'description' => 'MySQL database connection for the application.',
                'type' => 'service',
                'supports' => ['test'],
            ],
            [
                'key' => 'cache',
                'name' => 'Cache',
                'description' => 'Application cache driver used for settings and rate limiting.',
                'type' => 'service',
                'supports' => ['test'],
            ],
            [
                'key' => 'queue',
                'name' => 'Queue System',
                'description' => 'Database-backed queue for deferred job processing.',
                'type' => 'service',
                'supports' => ['test'],
            ],
        ];
    }

    public function getServiceStatus(string $key): array
    {
        return match ($key) {
            'dating-reverb', 'dating-worker' => $this->checkSupervisor($key),
            'fcm' => $this->checkFcm(),
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
            default => ['status' => 'unknown', 'label' => 'Unknown', 'color' => 'gray'],
        };
    }

    protected function checkSupervisor(string $name): array
    {
        $result = $this->runSupervisorCtl('status', $name);

        if ($result === null) {
            return $this->statusResult('unavailable', 'N/A', 'gray');
        }

        if (! $result['success']) {
            return $this->statusResult('error', 'Error', 'danger');
        }

        $output = trim($result['output']);
        $lines = array_filter(explode("\n", $output));

        if (empty($lines)) {
            return $this->statusResult('unknown', 'Unknown', 'gray');
        }

        $allRunning = true;
        $allStopped = true;
        $details = [];

        foreach ($lines as $line) {
            if (preg_match('/^(\S+)\s+(\S+)\s*(.*)$/', $line, $m)) {
                $processName = $m[1];
                $processStatus = $m[2];
                $processDetails = trim($m[3]);

                $details[] = "{$processName}: {$processStatus} {$processDetails}";

                if ($processStatus !== 'RUNNING') {
                    $allRunning = false;
                }
                if ($processStatus !== 'STOPPED') {
                    $allStopped = false;
                }
            }
        }

        if ($allRunning) {
            return $this->statusResult('running', 'Running', 'success', implode("\n", $details));
        }

        if ($allStopped) {
            return $this->statusResult('stopped', 'Stopped', 'danger', implode("\n", $details));
        }

        return $this->statusResult('partial', 'Partial', 'warning', implode("\n", $details));
    }

    protected function checkFcm(): array
    {
        $path = storage_path('app/firebase-credentials.json');

        if (! file_exists($path)) {
            return $this->statusResult('misconfigured', 'Missing Credentials', 'danger');
        }

        if (! is_readable($path)) {
            return $this->statusResult('error', 'Unreadable', 'danger', 'Check file permissions');
        }

        $content = file_get_contents($path);
        $json = json_decode($content, true);

        if ($json === null || ! isset($json['project_id'])) {
            return $this->statusResult('misconfigured', 'Invalid Credentials', 'danger');
        }

        return $this->statusResult('configured', 'Configured', 'success', 'Project: '.$json['project_id']);
    }

    protected function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return $this->statusResult('connected', 'Connected', 'success');
        } catch (\Exception $e) {
            return $this->statusResult('disconnected', 'Disconnected', 'danger', $e->getMessage());
        }
    }

    protected function checkCache(): array
    {
        try {
            cache()->store()->has('health-check-key');
            $driver = config('cache.default');

            return $this->statusResult('connected', 'Connected', 'success', "Driver: {$driver}");
        } catch (\Exception $e) {
            return $this->statusResult('disconnected', 'Disconnected', 'danger', $e->getMessage());
        }
    }

    protected function checkQueue(): array
    {
        try {
            $pending = DB::table('jobs')->count();
            $failed = DB::table('failed_jobs')->count();

            $details = "Pending jobs: {$pending}";

            if ($failed > 0) {
                $details .= ", Failed: {$failed}";
            }

            return $this->statusResult('connected', 'Connected', 'success', $details);
        } catch (\Exception $e) {
            return $this->statusResult('disconnected', 'Disconnected', 'danger', $e->getMessage());
        }
    }

    protected function runSupervisorCtl(string $action, string $name): ?array
    {
        $process = Process::run("supervisorctl {$action} {$name} 2>&1");

        if ($process->successful()) {
            return [
                'success' => true,
                'output' => $process->output(),
            ];
        }

        $process = Process::run("sudo supervisorctl {$action} {$name} 2>&1");

        if ($process->successful()) {
            return [
                'success' => true,
                'output' => $process->output(),
            ];
        }

        $exitCode = $process->exitCode();
        $error = $process->errorOutput();

        $unavailablePatterns = [
            'command not found',
            'no such file',
            'permission denied',
            'connection refused',
            'unix:///var/run/supervisor.sock',
            'cannot connect',
            'refused',
            'no socket',
            'http://localhost',
        ];

        foreach ($unavailablePatterns as $pattern) {
            if (str_contains(strtolower($error), $pattern)) {
                return null;
            }
        }

        if ($exitCode === 127 || $exitCode === 126) {
            return null;
        }

        return [
            'success' => false,
            'output' => $error,
        ];
    }

    protected function statusResult(string $status, string $label, string $color, ?string $details = null): array
    {
        return [
            'status' => $status,
            'label' => $label,
            'color' => $color,
            'details' => $details,
        ];
    }

    public function start(string $name): void
    {
        $this->supervisorAction('start', $name);
    }

    public function stop(string $name): void
    {
        $this->supervisorAction('stop', $name);
    }

    public function restart(string $name): void
    {
        $this->supervisorAction('restart', $name);
    }

    protected function supervisorAction(string $action, string $name): void
    {
        $result = $this->runSupervisorCtl($action, $name);

        if ($result === null) {
            Notification::make()
                ->title('supervisorctl Not Available')
                ->body('Install supervisor or check the web user has permission to run supervisorctl.')
                ->danger()
                ->send();

            return;
        }

        if ($result['success']) {
            Notification::make()
                ->title(ucfirst($action).'d '.$name.' successfully')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Failed to '.$action.' '.$name)
                ->body($result['output'])
                ->danger()
                ->send();
        }
    }

    public function test(string $service): void
    {
        match ($service) {
            'fcm' => $this->testFcm(),
            'database' => $this->testDatabaseConnection(),
            'cache' => $this->testCacheConnection(),
            'queue' => $this->testQueueSystem(),
            default => null,
        };
    }

    public function testFcm(): void
    {
        $path = storage_path('app/firebase-credentials.json');

        if (! file_exists($path)) {
            Notification::make()
                ->title('FCM Test Failed')
                ->body('Firebase credentials file not found at storage/app/firebase-credentials.json.')
                ->danger()
                ->send();

            return;
        }

        if (! is_readable($path)) {
            Notification::make()
                ->title('FCM Test Failed')
                ->body('Firebase credentials file exists but is not readable by the web server. Fix permissions.')
                ->danger()
                ->send();

            return;
        }

        $json = json_decode(file_get_contents($path), true);

        if (! $json || ! isset($json['project_id'], $json['client_email'], $json['private_key'])) {
            Notification::make()
                ->title('FCM Test Failed')
                ->body('Firebase credentials file exists but is invalid or incomplete.')
                ->danger()
                ->send();

            return;
        }

        $user = auth()->user();
        $tokenCount = is_array($user->fcm_tokens) ? count($user->fcm_tokens) : 0;

        Notification::make()
            ->title('FCM Configuration OK')
            ->body("Project: {$json['project_id']}. Admin device tokens: {$tokenCount}.")
            ->success()
            ->send();
    }

    public function testDatabaseConnection(): void
    {
        try {
            DB::connection()->getPdo();
            $name = DB::connection()->getDatabaseName();

            Notification::make()
                ->title('Database Connection OK')
                ->body("Connected to database: {$name}")
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Database Connection Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function testCacheConnection(): void
    {
        try {
            $driver = config('cache.default');
            $key = 'health-test-'.now()->timestamp;
            cache()->put($key, true, 1);
            $value = cache()->get($key);
            cache()->forget($key);

            if ($value === true) {
                Notification::make()
                    ->title('Cache Test OK')
                    ->body("Cache driver ({$driver}): read/write successful")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Cache Test Inconclusive')
                    ->body("Cache driver ({$driver}): read/write mismatch")
                    ->warning()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Cache Test Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function testQueueSystem(): void
    {
        try {
            $pending = DB::table('jobs')->count();
            $failed = DB::table('failed_jobs')->count();

            $parts = [];

            if ($pending > 0) {
                $parts[] = "{$pending} pending job(s)";
            } else {
                $parts[] = 'No pending jobs';
            }

            if ($failed > 0) {
                $parts[] = "{$failed} failed job(s) — check failed_jobs table";
            }

            Notification::make()
                ->title('Queue System Check')
                ->body(implode('. ', $parts).'.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Queue System Check Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function testAll(): void
    {
        $this->testDatabaseConnection();
        $this->testCacheConnection();
        $this->testQueueSystem();
        $this->testFcm();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->action(fn () => null),
            Action::make('testAll')
                ->label('Test All')
                ->icon('heroicon-o-beaker')
                ->action('testAll'),
        ];
    }

    public function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'supervisor' => $this->isSupervisorAvailable(),
        ];
    }

    protected function isSupervisorAvailable(): bool
    {
        $result = $this->runSupervisorCtl('status', 'all');

        return $result !== null;
    }
}
