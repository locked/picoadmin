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
                        @foreach ($deviceData['chartGroups'] as $group)
                            @php
                                $chartId = 'chart-' . $device->id . '-' . $group['key'];
                                $allLabels = collect();
                                foreach ($group['datasets'] as $ds) {
                                    foreach ($ds['data'] as $point) {
                                        $allLabels->push($point['date']);
                                    }
                                }
                                $labels = $allLabels->unique()->sort()->values()->all();
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
            @endforeach
        </div>

        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
            <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@3"></script>
            <script>
                document.addEventListener('livewire:initialized', function () {
                    @foreach ($deviceMetrics as $deviceData)
                        @php $device = $deviceData['device']; @endphp
                        @foreach ($deviceData['chartGroups'] as $group)
                            @php
                                $chartId = 'chart-' . $device->id . '-' . $group['key'];
                                $allLabels = collect();
                                foreach ($group['datasets'] as $ds) {
                                    foreach ($ds['data'] as $point) {
                                        $allLabels->push($point['date']);
                                    }
                                }
                                $labels = $allLabels->unique()->sort()->values()->all();
                            @endphp
                            (function() {
                                var ctx = document.getElementById('{{ $chartId }}');
                                if (!ctx) return;
                                var labels = @js($labels);
                                var datasets = [
                                    @foreach ($group['datasets'] as $ds)
                                        {
                                            label: @js($ds['label']),
                                            data: labels.map(function(l) {
                                                var found = @js($ds['data']).find(function(p) { return p.date === l; });
                                                return found ? found.value : null;
                                            }),
                                            borderColor: @js($ds['color']),
                                            tension: 0.3,
                                            fill: false,
                                            pointRadius: 2,
                                        },
                                    @endforeach
                                ];
                                var annotations = {};
                                var groupKey = @js($group['key']);
                                if (groupKey === 'co2_tvoc') {
                                    annotations = {
                                        good: {
                                            type: 'box',
                                            yMin: 0,
                                            yMax: 600,
                                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                            borderWidth: 0,
                                            label: {
                                                display: true,
                                                content: 'Good',
                                                position: { x: 'end', y: 'start' },
                                                font: { size: 10 },
                                                color: 'rgba(34, 197, 94, 0.7)'
                                            }
                                        },
                                        acceptable: {
                                            type: 'box',
                                            yMin: 600,
                                            yMax: 1000,
                                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                                            borderWidth: 0,
                                            label: {
                                                display: true,
                                                content: 'Acceptable',
                                                position: { x: 'end', y: 'start' },
                                                font: { size: 10 },
                                                color: 'rgba(245, 158, 11, 0.7)'
                                            }
                                        },
                                        poor: {
                                            type: 'box',
                                            yMin: 1000,
                                            yMax: 2500,
                                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                            borderWidth: 0,
                                            label: {
                                                display: true,
                                                content: 'Poor',
                                                position: { x: 'end', y: 'start' },
                                                font: { size: 10 },
                                                color: 'rgba(239, 68, 68, 0.7)'
                                            }
                                        }
                                    };
                                }
                                new Chart(ctx, {
                                    type: 'line',
                                    data: {
                                        labels: labels,
                                        datasets: datasets
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: { display: datasets.length > 1 },
                                            annotation: { annotations: annotations }
                                        },
                                        scales: {
                                            x: { display: true, ticks: { maxTicksLimit: 8, font: { size: 10 } } },
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
