<x-filament-panels::page>
    @php
        $deviceMetrics = $this->getDeviceMetrics();
    @endphp

    @if (empty($deviceMetrics))
        <div class="flex flex-col items-center justify-center py-12">
            <x-heroicon-o-chart-bar class="w-12 h-12 text-gray-400" />
            <p class="mt-4 text-gray-500 dark:text-gray-400">No metrics recorded in the last 7 days.</p>
        </div>
    @else
        <div class="space-y-8">
            @foreach ($deviceMetrics as $deviceData)
                @php
                    $device = $deviceData['device'];
                @endphp
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2">
                            <span class="fi-badge fi-badge-size-sm {{ $device->deviceModel?->type === 'water' ? 'fi-badge-color-info' : 'fi-badge-color-success' }}">
                                {{ ucfirst($device->deviceModel?->type ?? 'Unknown') }}
                            </span>
                            <span>{{ $device->serialnumber }}</span>
                        </div>
                    </x-slot>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-4">
                        @foreach ($deviceData['metrics'] as $metric)
                            @php
                                $chartId = 'chart-' . $device->id . '-' . $metric['type'];
                                $labels = collect($metric['entries'])->pluck('date')->values()->all();
                                $values = collect($metric['entries'])->pluck('value')->values()->all();
                            @endphp
                            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    {{ $metric['label'] }}
                                </h4>
                                <div style="height: 200px;">
                                    <canvas id="{{ $chartId }}"></canvas>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-filament::section>
            @endforeach
        </div>

        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
            <script>
                document.addEventListener('livewire:initialized', function () {
                    @foreach ($deviceMetrics as $deviceData)
                        @php $device = $deviceData['device']; @endphp
                        @foreach ($deviceData['metrics'] as $metric)
                            @php
                                $chartId = 'chart-' . $device->id . '-' . $metric['type'];
                                $labels = collect($metric['entries'])->pluck('date')->values()->all();
                                $values = collect($metric['entries'])->pluck('value')->values()->all();
                            @endphp
                            (function() {
                                var ctx = document.getElementById('{{ $chartId }}');
                                if (!ctx) return;
                                new Chart(ctx, {
                                    type: 'line',
                                    data: {
                                        labels: @js($labels),
                                        datasets: [{
                                            label: @js($metric['label']),
                                            data: @js($values),
                                            borderColor: '#3b82f6',
                                            tension: 0.3,
                                            fill: false,
                                            pointRadius: 2,
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: { legend: { display: false } },
                                        scales: {
                                            x: { display: true, ticks: { maxTicksLimit: 8, font: { size: 10 } } },
                                            y: { display: true, ticks: { font: { size: 10 } } }
                                        }
                                    }
                                });
                            })();
                        @endforeach
                    @endforeach
                });
            </script>
        @endpush
    @endif
</x-filament-panels::page>
