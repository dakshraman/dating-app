<div class="space-y-6">
    @php
        $services = $this->getServices();
        $info = $this->getSystemInfo();
        $statuses = collect($services)->mapWithKeys(fn($s) => [$s['key'] => $this->getServiceStatus($s['key'])]);
        $totalCount = count($services);
        $healthyCount = $statuses->filter(fn($s) => in_array($s['status'], ['running', 'configured', 'connected']))->count();
        $allHealthy = $healthyCount === $totalCount;
        $hasIssues = $statuses->some(fn($s) => in_array($s['status'], ['stopped', 'disconnected', 'error', 'misconfigured']));
    @endphp

    {{-- Overall Health Banner --}}
    <div class="rounded-xl p-5 ring-1 transition-all duration-300
        {{ $allHealthy ? 'bg-success-50 ring-success-600/20 dark:bg-success-400/5' : ($hasIssues ? 'bg-danger-50 ring-danger-600/20 dark:bg-danger-400/5' : 'bg-warning-50 ring-warning-600/20 dark:bg-warning-400/5') }}">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="rounded-full p-2.5
                    {{ $allHealthy ? 'bg-success-500' : ($hasIssues ? 'bg-danger-500' : 'bg-warning-500') }}">
                    @if($allHealthy)
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    @endif
                </div>
                <div>
                    <p class="text-base font-semibold
                        {{ $allHealthy ? 'text-success-800 dark:text-success-300' : ($hasIssues ? 'text-danger-800 dark:text-danger-300' : 'text-warning-800 dark:text-warning-300') }}">
                        {{ $allHealthy ? 'All Systems Operational' : ($hasIssues ? 'Service Outage Detected' : 'Partial Outage') }}
                    </p>
                    <p class="text-sm mt-0.5
                        {{ $allHealthy ? 'text-success-600 dark:text-success-400' : ($hasIssues ? 'text-danger-600 dark:text-danger-400' : 'text-warning-600 dark:text-warning-400') }}">
                        {{ $healthyCount }} of {{ $totalCount }} services healthy
                    </p>
                </div>
            </div>
            <x-filament::button
                size="sm"
                color="gray"
                icon="heroicon-o-arrow-path"
                wire:click="$refresh"
                outlined
            >
                Refresh
            </x-filament::button>
        </div>
    </div>

    {{-- System Info Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="rounded-xl bg-white p-4 shadow-xs ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0021 18V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25z" />
                </svg>
                PHP
            </div>
            <p class="mt-2 text-xl font-semibold text-gray-950 dark:text-white tabular-nums">{{ $info['php_version'] }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-xs ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
                </svg>
                Laravel
            </div>
            <p class="mt-2 text-xl font-semibold text-gray-950 dark:text-white tabular-nums">{{ $info['laravel_version'] }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-xs ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Environment
            </div>
            <p class="mt-2 text-xl font-semibold text-gray-950 dark:text-white">{{ ucfirst($info['environment']) }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-xs ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                </svg>
                Supervisor
            </div>
            <p class="mt-2 text-xl font-semibold text-gray-950 dark:text-white">{{ $info['supervisor'] ? 'Available' : 'N/A' }}</p>
        </div>
    </div>

    {{-- Service Cards --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @foreach($services as $service)
            @php $status = $statuses[$service['key']]; @endphp
            <div class="relative overflow-hidden rounded-xl bg-white shadow-xs ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 transition-all duration-200 hover:shadow-sm">
                {{-- Left color accent bar --}}
                <div class="absolute left-0 top-0 bottom-0 w-1
                    {{ match($status['color']) { 'success' => 'bg-success-500', 'warning' => 'bg-warning-500', 'danger' => 'bg-danger-500', default => 'bg-gray-300 dark:bg-gray-600' } }}"></div>

                <div class="p-5 pl-7">
                    {{-- Header row --}}
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3">
                                <h3 class="text-base font-semibold text-gray-950 dark:text-white">{{ $service['name'] }}</h3>
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-0.5 text-xs font-medium ring-1 ring-inset whitespace-nowrap
                                    {{ match($status['color']) {
                                        'success' => 'bg-success-50 text-success-700 ring-success-600/20 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/30',
                                        'warning' => 'bg-warning-50 text-warning-700 ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-400 dark:ring-warning-400/30',
                                        'danger' => 'bg-danger-50 text-danger-700 ring-danger-600/20 dark:bg-danger-400/10 dark:text-danger-400 dark:ring-danger-400/30',
                                        default => 'bg-gray-50 text-gray-700 ring-gray-600/20 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/30',
                                    } }}">
                                    @if($status['color'] === 'success')
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-success-500"></span>
                                        </span>
                                    @elseif($status['color'] === 'danger')
                                        <span class="inline-flex rounded-full h-2 w-2 bg-danger-500"></span>
                                    @elseif($status['color'] === 'warning')
                                        <span class="inline-flex rounded-full h-2 w-2 bg-warning-500"></span>
                                    @else
                                        <span class="inline-flex rounded-full h-2 w-2 bg-gray-400"></span>
                                    @endif
                                    {{ $status['label'] }}
                                </span>
                            </div>
                            <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ $service['description'] }}</p>
                            @if($status['details'])
                                <pre class="mt-2 text-xs text-gray-400 dark:text-gray-500 whitespace-pre-wrap font-mono bg-gray-50 dark:bg-gray-800/50 rounded-lg p-2.5 border border-gray-100 dark:border-gray-700">{{ $status['details'] }}</pre>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-4 flex flex-wrap items-center gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                        @if(in_array('start', $service['supports']))
                            <x-filament::button
                                size="sm"
                                color="success"
                                :disabled="$status['status'] === 'running'"
                                wire:click="start('{{ $service['key'] }}')"
                                wire:loading.attr="disabled"
                                wire:target="start('{{ $service['key'] }}')"
                                icon="heroicon-o-play"
                            >
                                Start
                            </x-filament::button>
                        @endif

                        @if(in_array('stop', $service['supports']))
                            <x-filament::button
                                size="sm"
                                color="danger"
                                :disabled="$status['status'] === 'stopped' || $status['status'] === 'unavailable'"
                                wire:click="stop('{{ $service['key'] }}')"
                                wire:loading.attr="disabled"
                                wire:target="stop('{{ $service['key'] }}')"
                                icon="heroicon-o-stop"
                            >
                                Stop
                            </x-filament::button>
                        @endif

                        @if(in_array('restart', $service['supports']))
                            <x-filament::button
                                size="sm"
                                color="warning"
                                :disabled="$status['status'] === 'unavailable'"
                                wire:click="restart('{{ $service['key'] }}')"
                                wire:loading.attr="disabled"
                                wire:target="restart('{{ $service['key'] }}')"
                                icon="heroicon-o-arrow-path"
                            >
                                Restart
                            </x-filament::button>
                        @endif

                        @if(in_array('test', $service['supports']))
                            <x-filament::button
                                size="sm"
                                color="gray"
                                wire:click="test('{{ $service['key'] }}')"
                                wire:loading.attr="disabled"
                                wire:target="test('{{ $service['key'] }}')"
                                icon="heroicon-o-beaker"
                            >
                                Test
                            </x-filament::button>
                        @endif

                        <div wire:loading.inline-flex wire:target="start('{{ $service['key'] }}'),stop('{{ $service['key'] }}'),restart('{{ $service['key'] }}'),test('{{ $service['key'] }}')" class="ml-auto">
                            <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
