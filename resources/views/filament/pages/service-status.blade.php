<div class="space-y-6">
    {{-- System Info Bar --}}
    @php $info = $this->getSystemInfo(); @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">PHP Version</p>
            <p class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">{{ $info['php_version'] }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Laravel Version</p>
            <p class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">{{ $info['laravel_version'] }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Environment</p>
            <p class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">{{ ucfirst($info['environment']) }}</p>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Supervisor</p>
            <p class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">{{ $info['supervisor'] ? 'Available' : 'N/A' }}</p>
        </div>
    </div>

    {{-- Service Cards --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @foreach($this->getServices() as $service)
            @php $status = $this->getServiceStatus($service['key']); @endphp
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3">
                                <h3 class="text-base font-semibold text-gray-950 dark:text-white">{{ $service['name'] }}</h3>
                                <span class="inline-flex items-center gap-x-1.5 rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset
                                    {{ match($status['color']) {
                                        'success' => 'bg-success-50 text-success-700 ring-success-600/20 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/30',
                                        'warning' => 'bg-warning-50 text-warning-700 ring-warning-600/20 dark:bg-warning-400/10 dark:text-warning-400 dark:ring-warning-400/30',
                                        'danger' => 'bg-danger-50 text-danger-700 ring-danger-600/20 dark:bg-danger-400/10 dark:text-danger-400 dark:ring-danger-400/30',
                                        default => 'bg-gray-50 text-gray-700 ring-gray-600/20 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/30',
                                    } }}">
                                    {{ $status['label'] }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $service['description'] }}</p>
                            @if($status['details'])
                                <pre class="mt-2 text-xs text-gray-400 dark:text-gray-500 whitespace-pre-wrap font-mono">{{ $status['details'] }}</pre>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        @if(in_array('start', $service['supports']))
                            <x-filament::button
                                size="sm"
                                color="success"
                                :disabled="$status['status'] === 'running'"
                                wire:click="start('{{ $service['key'] }}')"
                                wire:loading.attr="disabled"
                                wire:target="start('{{ $service['key'] }}')"
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
                            >
                                Test {{ $service['name'] }}
                            </x-filament::button>
                        @endif

                        <div wire:loading.inline-flex wire:target="start('{{ $service['key'] }}'),stop('{{ $service['key'] }}'),restart('{{ $service['key'] }}'),test('{{ $service['key'] }}')" class="ml-2">
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
