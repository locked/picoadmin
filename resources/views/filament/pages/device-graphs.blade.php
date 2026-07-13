<x-filament-panels::page>
    @php
        $deviceMetrics = $this->getDeviceMetrics();
        $currentRange = $this->timeRange;
    @endphp

    <div class="flex items-center gap-2 mb-4">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Range:</label>
        <div class="flex gap-1">
            @foreach (\App\Filament\Pages\DeviceGraphs::TIME_RANGE_OPTIONS as $value => $label)
                <a href="{{ url()->current() }}?range={{ $value }}"
                   class="px-3 py-1.5 text-sm rounded-lg border {{ $currentRange === $value ? 'bg-blue-500 text-white border-blue-500' : 'bg-white dark:bg-white/5 border-gray-300 dark:border-white/10 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/10' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    @if (empty($deviceMetrics))
        <div class="flex flex-col items-center justify-center py-12">
            <x-heroicon-o-chart-bar class="w-12 h-12 text-gray-400" />
            <p class="mt-4 text-gray-500 dark:text-gray-400">No metrics recorded for this period.</p>
        </div>
    @else
        <div class="space-y-6">
            @foreach ($deviceMetrics as $deviceData)
                @php
                    $device = $deviceData['device'];
                @endphp
                <div>
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2">
                            <span class="fi-badge fi-badge-size-sm {{ $device->deviceModel?->type === 'water' ? 'fi-badge-color-info' : 'fi-badge-color-success' }}">
                                {{ ucfirst($device->deviceModel?->type ?? 'Unknown') }}
                            </span>
                            <span>{{ $device->name ?? $device->serialnumber }}</span>
                            @if ($device->name)
                                <span class="text-xs text-gray-400 font-mono">{{ $device->serialnumber }}</span>
                            @endif
                        </div>
                    </x-slot>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-4">
                        @foreach ($deviceData['chartGroups'] as $group)
                            @php
                                $chartId = 'chart-' . $device->id . '-' . $group['key'];
                            @endphp
                            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    {{ $group['label'] }}
                                </h4>
                                <div style="height: 200px;">
                                    <canvas id="{{ $chartId }}"></canvas>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-filament::section>
                </div>
            @endforeach
        </div>

        @push('scripts')
            <script src="/js/vendor/chart.umd.min.js"></script>
            <script src="/js/vendor/chartjs-plugin-annotation.min.js"></script>
            <script src="/js/vendor/chartjs-adapter-date-fns.bundle.min.js"></script>
            <script>
                document.addEventListener('livewire:initialized', function () {
                    @foreach ($deviceMetrics as $deviceData)
                        @php $device = $deviceData['device']; @endphp
                        @foreach ($deviceData['chartGroups'] as $group)
                            @php
                                $chartId = 'chart-' . $device->id . '-' . $group['key'];
                            @endphp
                            (function() {
                                var ctx = document.getElementById('{{ $chartId }}');
                                if (!ctx) return;
                                var chartDatasets = @js($group['datasets']);
                                var chartMin = @js($deviceData['min']);
                                var chartMax = @js($deviceData['max']);
                                var annotations = {};
                                var groupKey = @js($group['key']);
                                if (groupKey === 'co2_tvoc') {
                                    annotations = {
                                        good: {
                                            type: 'box', yMin: 0, yMax: 600,
                                            backgroundColor: 'rgba(34, 197, 94, 0.1)', borderWidth: 0,
                                            label: { display: true, content: 'Good', position: { x: 'end', y: 'start' }, font: { size: 10 }, color: 'rgba(34, 197, 94, 0.7)' }
                                        },
                                        acceptable: {
                                            type: 'box', yMin: 600, yMax: 1000,
                                            backgroundColor: 'rgba(245, 158, 11, 0.1)', borderWidth: 0,
                                            label: { display: true, content: 'Acceptable', position: { x: 'end', y: 'start' }, font: { size: 10 }, color: 'rgba(245, 158, 11, 0.7)' }
                                        },
                                        poor: {
                                            type: 'box', yMin: 1000, yMax: 2500,
                                            backgroundColor: 'rgba(239, 68, 68, 0.1)', borderWidth: 0,
                                            label: { display: true, content: 'Poor', position: { x: 'end', y: 'start' }, font: { size: 10 }, color: 'rgba(239, 68, 68, 0.7)' }
                                        }
                                    };
                                }
                                new Chart(ctx, {
                                    type: 'line',
                                    data: {
                                        datasets: chartDatasets
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: { display: chartDatasets.length > 1 },
                                            annotation: { annotations: annotations }
                                        },
                                        scales: {
                                            x: {
                                                type: 'time',
                                                min: chartMin,
                                                max: chartMax,
                                                time: {
                                                    tooltipFormat: 'dd MMM yyyy HH:mm',
                                                    displayFormats: {
                                                        hour: 'dd MMM HH:mm',
                                                        day: 'dd MMM',
                                                    }
                                                },
                                                ticks: { maxTicksLimit: 8, font: { size: 10 } }
                                            },
                                            y: { display: true, ticks: { font: { size: 10 } }, beginAtZero: true }
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
