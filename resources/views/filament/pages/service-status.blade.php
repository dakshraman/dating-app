<div class="flex flex-col gap-8">
    @php
        $services = $this->getServices();
        $info = $this->getSystemInfo();
        $statuses = collect($services)->mapWithKeys(fn($s) => [$s['key'] => $this->getServiceStatus($s['key'])]);
        $totalCount = count($services);
        $healthyCount = $statuses->filter(fn($s) => in_array($s['status'], ['running', 'configured', 'connected']))->count();
        $allHealthy = $healthyCount === $totalCount;
        $hasIssues = $statuses->some(fn($s) => in_array($s['status'], ['stopped', 'disconnected', 'error', 'misconfigured']));
    @endphp

    {{-- System Status Banner --}}
    <div @class([
        'fi-section rounded-xl shadow-sm ring-1 flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 transition-all duration-300',
        'bg-success-50 ring-success-600/20 dark:bg-success-400/10 dark:ring-success-400/20' => $allHealthy,
        'bg-danger-50 ring-danger-600/20 dark:bg-danger-400/10 dark:ring-danger-400/20' => $hasIssues,
        'bg-warning-50 ring-warning-600/20 dark:bg-warning-400/10 dark:ring-warning-400/20' => ! $allHealthy && ! $hasIssues,
    ])>
        <div class="flex items-center gap-5">
            <div @class([
                'rounded-full p-3 flex items-center justify-center ring-4',
                'bg-success-500 text-white ring-success-500/30' => $allHealthy,
                'bg-danger-500 text-white ring-danger-500/30' => $hasIssues,
                'bg-warning-500 text-white ring-warning-500/30' => ! $allHealthy && ! $hasIssues,
            ])>
                @if($allHealthy)
                    <x-heroicon-s-check-circle class="w-8 h-8" />
                @else
                    <x-heroicon-s-exclamation-triangle class="w-8 h-8" />
                @endif
            </div>
            <div>
                <h2 @class([
                    'text-xl font-bold tracking-tight',
                    'text-success-800 dark:text-success-300' => $allHealthy,
                    'text-danger-800 dark:text-danger-300' => $hasIssues,
                    'text-warning-800 dark:text-warning-300' => ! $allHealthy && ! $hasIssues,
                ])>
                    {{ $allHealthy ? 'All Systems Operational' : ($hasIssues ? 'Service Outage Detected' : 'Partial Outage') }}
                </h2>
                <p @class([
                    'text-sm font-medium mt-1',
                    'text-success-600/80 dark:text-success-400/80' => $allHealthy,
                    'text-danger-600/80 dark:text-danger-400/80' => $hasIssues,
                    'text-warning-600/80 dark:text-warning-400/80' => ! $allHealthy && ! $hasIssues,
                ])>
                    {{ $healthyCount }} out of {{ $totalCount }} services are running smoothly.
                </p>
            </div>
        </div>
    </div>

    {{-- System Info Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach ([
            ['label' => 'PHP Version', 'value' => $info['php_version'], 'icon' => 'heroicon-o-command-line'],
            ['label' => 'Laravel Version', 'value' => $info['laravel_version'], 'icon' => 'heroicon-o-cube'],
            ['label' => 'Environment', 'value' => ucfirst($info['environment']), 'icon' => 'heroicon-o-server'],
            ['label' => 'Supervisor', 'value' => $info['supervisor'] ? 'Available' : 'N/A', 'icon' => 'heroicon-o-cpu-chip'],
        ] as $stat)
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6 flex flex-col justify-center transition-all hover:shadow-md">
                <div class="flex items-center gap-3 text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    <x-dynamic-component :component="$stat['icon']" class="w-5 h-5 text-primary-500 dark:text-primary-400" />
                    {{ $stat['label'] }}
                </div>
                <div class="mt-3 text-3xl font-bold text-gray-950 dark:text-white tabular-nums tracking-tight">
                    {{ $stat['value'] }}
                </div>
            </div>
        @endforeach
    </div>

    {{-- Services Grid --}}
    <div>
        <div class="flex items-center gap-3 mb-6">
            <div class="p-2 bg-primary-50 dark:bg-primary-500/10 rounded-lg">
                <x-heroicon-o-server-stack class="w-6 h-6 text-primary-500 dark:text-primary-400" />
            </div>
            <h2 class="text-xl font-bold text-gray-950 dark:text-white">Core Services</h2>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($services as $service)
                @php $status = $statuses[$service['key']]; @endphp
                <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 flex flex-col overflow-hidden relative transition-all hover:shadow-md group">
                    <div @class([
                        'absolute top-0 left-0 w-1.5 h-full transition-colors',
                        'bg-success-500' => $status['color'] === 'success',
                        'bg-danger-500' => $status['color'] === 'danger',
                        'bg-warning-500' => $status['color'] === 'warning',
                        'bg-gray-400' => ! in_array($status['color'], ['success', 'danger', 'warning']),
                    ])></div>
                    
                    <div class="p-6 pl-8 flex-grow">
                        <div class="flex justify-between items-start gap-4">
                            <div class="flex-grow">
                                <h3 class="text-lg font-bold text-gray-950 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">{{ $service['name'] }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 mb-4 leading-relaxed">
                                    {{ $service['description'] }}
                                </p>
                            </div>
                            <x-filament::badge :color="$status['color']" size="sm" class="shrink-0 flex items-center gap-1.5 px-3 py-1 text-xs font-semibold shadow-sm">
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
                            </x-filament::badge>
                        </div>

                        @if($status['details'])
                            <div class="mt-4 p-3.5 bg-gray-50 dark:bg-white/5 rounded-lg border border-gray-100 dark:border-white/5 shadow-inner">
                                <pre class="text-xs text-gray-600 dark:text-gray-300 whitespace-pre-wrap font-mono">{{ $status['details'] }}</pre>
                            </div>
                        @endif
                    </div>

                    <div class="px-6 pl-8 py-4 bg-gray-50/50 dark:bg-white/5 border-t border-gray-100 dark:border-white/10 flex flex-wrap items-center gap-3 mt-auto">
                        @if(in_array('start', $service['supports']))
                            <x-filament::button
                                size="sm"
                                color="success"
                                :disabled="$status['status'] === 'running'"
                                wire:click="start('{{ $service['key'] }}')"
                                wire:loading.attr="disabled"
                                wire:target="start('{{ $service['key'] }}')"
                                icon="heroicon-m-play"
                            >
                                Start
                            </x-filament::button>
                        @endif

                        @if(in_array('stop', $service['supports']))
                            <x-filament::button
                                size="sm"
                                color="danger"
                                :disabled="in_array($status['status'], ['stopped', 'unavailable'])"
                                wire:click="stop('{{ $service['key'] }}')"
                                wire:loading.attr="disabled"
                                wire:target="stop('{{ $service['key'] }}')"
                                icon="heroicon-m-stop"
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
                                icon="heroicon-m-arrow-path"
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
                                icon="heroicon-m-beaker"
                            >
                                Test
                            </x-filament::button>
                        @endif

                        <div wire:loading.inline-flex wire:target="start('{{ $service['key'] }}'),stop('{{ $service['key'] }}'),restart('{{ $service['key'] }}'),test('{{ $service['key'] }}')" class="ml-auto">
                            <x-filament::loading-indicator class="h-5 w-5 text-primary-500" />
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
