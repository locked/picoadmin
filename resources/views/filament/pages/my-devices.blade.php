<x-filament-panels::page>
    @php
        $devices = $this->getDevices();
    @endphp

    @if ($devices->isEmpty())
        <div class="flex flex-col items-center justify-center py-12">
            <x-heroicon-o-cpu-chip class="w-12 h-12 text-gray-400" />
            <p class="mt-4 text-gray-500 dark:text-gray-400">No devices are currently assigned to your account.</p>
        </div>
    @else
        <div class="space-y-6">
            @foreach ($devices as $device)
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2">
                            <span class="fi-badge fi-badge-size-sm {{ $device->deviceModel?->type === 'water' ? 'fi-badge-color-info' : 'fi-badge-color-success' }}">
                                {{ ucfirst($device->deviceModel?->type ?? 'Unknown') }}
                            </span>
                            @if ($this->editingDeviceNameId === $device->id)
                                <div class="flex items-center gap-1">
                                    <input type="text" wire:model="editingDeviceName" placeholder="Device name" class="fi-input w-48 rounded-lg bg-white dark:bg-white/5 border border-gray-300 dark:border-white/10 px-2 py-1 text-sm" />
                                    <x-filament::icon-button icon="heroicon-o-check" size="sm" wire:click="saveDeviceName" />
                                    <x-filament::icon-button icon="heroicon-o-x-mark" size="sm" color="gray" wire:click="cancelDeviceNameEdit" />
                                </div>
                            @else
                                <span>{{ $device->name ?? $device->serialnumber }}</span>
                                @if ($device->name)
                                    <span class="text-xs text-gray-400 font-mono">{{ $device->serialnumber }}</span>
                                @endif
                                <x-filament::icon-button icon="heroicon-o-pencil" size="sm" color="gray" wire:click="editDeviceName({{ $device->id }})" />
                            @endif
                            <span class="text-sm text-gray-500">v{{ $device->current_firmware ?? 'N/A' }}</span>
                        </div>
                    </x-slot>

                    {{-- Alarms Section (for clock devices) --}}
                    @if ($device->deviceModel?->type === 'clock')
                        <div class="mt-4">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-lg font-semibold">Alarms</h3>
                                <x-filament::button size="sm" wire:click="addAlarm({{ $device->id }})">
                                    Add Alarm
                                </x-filament::button>
                            </div>
                            <div class="space-y-3">
                                @php
                                    $daysMap = [64=>'Mon',32=>'Tue',16=>'Wed',8=>'Thu',4=>'Fri',2=>'Sat',1=>'Sun'];
                                @endphp
                                @foreach ($device->alarms as $alarm)
                                    @if ($this->editingAlarmId === $alarm->id)
                                        {{-- Editing mode --}}
                                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                            <div class="flex items-center gap-3 mb-3">
                                                <label class="text-xs text-gray-500">Enabled</label>
                                                <input type="checkbox" wire:model="alarmIsSet" class="rounded border-gray-300" />
                                            </div>

                                            <div class="grid grid-cols-3 md:grid-cols-4 gap-3 items-end mb-3">
                                                <div>
                                                    <label class="text-xs text-gray-500 block mb-1">Time</label>
                                                    <input type="time" wire:model="alarmTime" class="fi-input w-full rounded-lg bg-white dark:bg-white/5 border border-gray-300 dark:border-white/10 px-3 py-2 text-sm" />
                                                </div>
                                                <div>
                                                    <label class="text-xs text-gray-500 block mb-1">Week</label>
                                                    <select wire:model="alarmWeek" class="fi-input w-full rounded-lg bg-white dark:bg-white/5 border border-gray-300 dark:border-white/10 px-3 py-2 text-sm">
                                                        <option value="all">All</option>
                                                        <option value="odd">Odd</option>
                                                        <option value="even">Even</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="text-xs text-gray-500 block mb-1">Chime</label>
                                                    <select wire:model="alarmChime" class="fi-input w-full rounded-lg bg-white dark:bg-white/5 border border-gray-300 dark:border-white/10 px-3 py-2 text-sm">
                                                        <option value="">None</option>
                                                        <option value="Tellement.wav">Tellement.wav</option>
                                                        <option value="fleurdelune.wav">fleurdelune.wav</option>
                                                        <option value="santiano.wav">santiano.wav</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="text-xs text-gray-500 block mb-2">Days</label>
                                                <div class="flex flex-wrap gap-3">
                                                    @foreach (['Mon' => 'alarmMon', 'Tue' => 'alarmTue', 'Wed' => 'alarmWed', 'Thu' => 'alarmThu', 'Fri' => 'alarmFri', 'Sat' => 'alarmSat', 'Sun' => 'alarmSun'] as $label => $prop)
                                                        <label class="flex items-center gap-1.5 text-sm">
                                                            <input type="checkbox" wire:model="{{ $prop }}" class="rounded border-gray-300" />
                                                            {{ $label }}
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div class="flex gap-2 mt-4">
                                                <x-filament::button type="button" size="sm" wire:click="saveAlarm">Save</x-filament::button>
                                                <x-filament::button type="button" size="sm" color="gray" wire:click="cancelEdit">Cancel</x-filament::button>
                                            </div>
                                        </div>
                                    @else
                                        {{-- Display mode --}}
                                        <div class="flex items-center gap-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                            <div class="flex items-center gap-2">
                                                @if ($alarm->isset)
                                                    <span class="inline-block w-3 h-3 bg-green-500 rounded-full"></span>
                                                @else
                                                    <span class="inline-block w-3 h-3 bg-gray-300 rounded-full"></span>
                                                @endif
                                                <span class="font-mono text-lg">
                                                    {{ sprintf('%02d:%02d', $alarm->hour, $alarm->minute) }}
                                                </span>
                                            </div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400 flex-1">
                                                @php
                                                    $days = [];
                                                    foreach ($daysMap as $bit => $day) {
                                                        if ($alarm->weekdays & $bit) $days[] = $day;
                                                    }
                                                @endphp
                                                {{ implode(', ', $days) ?: 'None' }}
                                                &middot; {{ ucfirst($alarm->week) }} weeks
                                                @if ($alarm->chime)
                                                    &middot; {{ $alarm->chime }}
                                                @endif
                                            </div>
                                            <div class="flex gap-1">
                                                <x-filament::icon-button icon="heroicon-o-pencil" size="sm" wire:click="editAlarm({{ $alarm->id }})" />
                                                <x-filament::icon-button icon="heroicon-o-trash" size="sm" color="danger" wire:click="deleteAlarm({{ $alarm->id }})" wire:confirm="Delete this alarm?" />
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                                @if ($device->alarms->isEmpty())
                                    <p class="text-sm text-gray-500">No alarms configured.</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Metrics Section --}}
                    @php
                        $metrics = $this->getMetrics($device->id);
                    @endphp
                    @if (count($metrics) > 0)
                        <div class="mt-4">
                            <h3 class="text-lg font-semibold mb-3">Sensor Data (Last 7 Days)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach ($metrics as $type => $entries)
                                    <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ $type }}</h4>
                                        <div class="text-2xl font-bold">
                                            {{ end($entries)['value'] ?? 'N/A' }}
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ count($entries) }} readings
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Pump History (for water devices) --}}
                    @if ($device->deviceModel?->type === 'water')
                        @php $pumpHistory = $this->getPumpHistory($device->id); @endphp
                        <div class="mt-4">
                            <h3 class="text-lg font-semibold mb-3">Pump History</h3>
                            @if (count($pumpHistory) > 0)
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="border-b border-gray-200 dark:border-white/10">
                                                <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400">Date</th>
                                                <th class="text-right py-2 px-3 font-medium text-gray-600 dark:text-gray-400">Duration</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($pumpHistory as $entry)
                                                <tr class="border-b border-gray-100 dark:border-white/5">
                                                    <td class="py-1.5 px-3 text-gray-700 dark:text-gray-300">{{ $entry['date'] }}</td>
                                                    <td class="py-1.5 px-3 text-right font-mono">
                                                        @php
                                                            $ms = (int) $entry['duration_ms'];
                                                            if ($ms >= 1000) {
                                                                $s = number_format($ms / 1000, 1);
                                                                echo $s . ' s';
                                                            } else {
                                                                echo $ms . ' ms';
                                                            }
                                                        @endphp
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-gray-500">No pump triggers recorded.</p>
                            @endif
                        </div>
                    @endif
                </x-filament::section>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
