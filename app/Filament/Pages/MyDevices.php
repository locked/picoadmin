<?php

namespace App\Filament\Pages;

use App\Models\Alarm;
use App\Models\Device;
use App\Models\Metric;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class MyDevices extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationGroup = 'My Devices';

    protected static ?string $title = 'My Devices';

    protected static string $view = 'filament.pages.my-devices';

    public ?int $editingAlarmId = null;

    public ?int $editingDeviceNameId = null;

    public ?string $editingDeviceName = '';

    public ?bool $alarmIsSet = false;

    public bool $alarmMon = false;
    public bool $alarmTue = false;
    public bool $alarmWed = false;
    public bool $alarmThu = false;
    public bool $alarmFri = false;
    public bool $alarmSat = false;
    public bool $alarmSun = false;

    public ?int $alarmHour = 0;

    public ?int $alarmMinute = 0;

    public string $alarmTime = '07:00';

    public string $alarmWeek = 'all';

    public ?string $alarmChime = null;

    const DAY_BITS = [
        'mon' => 0b1000000,
        'tue' => 0b0100000,
        'wed' => 0b0010000,
        'thu' => 0b0001000,
        'fri' => 0b0000100,
        'sat' => 0b0000010,
        'sun' => 0b0000001,
    ];

    public function mount(): void
    {
    }

    public function getDevices()
    {
        $query = Device::with(['deviceModel', 'alarms']);

        if (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        return $query->get();
    }

    public function getMetrics(int $deviceId, ?int $days = 7): array
    {
        $device = Device::find($deviceId);
        if (!$device) return [];

        $type = $device->deviceModel?->type;
        $allowedTypes = Metric::allowedTypesForDevice($type);

        $since = now()->subDays($days);

        return Metric::where('device_id', $deviceId)
            ->whereIn('metric_type', $allowedTypes)
            ->where('metric_date', '>=', $since)
            ->orderBy('metric_date')
            ->get()
            ->groupBy(fn ($m) => Metric::typeLabel($m->metric_type))
            ->map(fn ($entries) => $entries->map(fn ($e) => [
                'date' => $e->metric_date->format('Y-m-d H:i'),
                'value' => $e->metric_value,
            ])->toArray())
            ->toArray();
    }

    public function getPumpHistory(int $deviceId): array
    {
        return Metric::where('device_id', $deviceId)
            ->where('metric_type', Metric::TYPE_PUMP)
            ->where('metric_value', '>', 0)
            ->orderByDesc('metric_date')
            ->limit(10)
            ->get()
            ->map(fn ($m) => [
                'date' => $m->metric_date->format('Y-m-d H:i:s'),
                'duration_ms' => $m->metric_value,
            ])
            ->toArray();
    }

    public function editAlarm(int $alarmId): void
    {
        $alarm = Alarm::findOrFail($alarmId);
        $this->editingAlarmId = $alarmId;
        $this->alarmIsSet = $alarm->isset;
        $this->alarmMon = (bool) ($alarm->weekdays & self::DAY_BITS['mon']);
        $this->alarmTue = (bool) ($alarm->weekdays & self::DAY_BITS['tue']);
        $this->alarmWed = (bool) ($alarm->weekdays & self::DAY_BITS['wed']);
        $this->alarmThu = (bool) ($alarm->weekdays & self::DAY_BITS['thu']);
        $this->alarmFri = (bool) ($alarm->weekdays & self::DAY_BITS['fri']);
        $this->alarmSat = (bool) ($alarm->weekdays & self::DAY_BITS['sat']);
        $this->alarmSun = (bool) ($alarm->weekdays & self::DAY_BITS['sun']);
        $this->alarmHour = $alarm->hour;
        $this->alarmMinute = $alarm->minute;
        $this->alarmTime = sprintf('%02d:%02d', $alarm->hour, $alarm->minute);
        $this->alarmWeek = $alarm->week;
        $this->alarmChime = $alarm->chime;
    }

    public function saveAlarm(): void
    {
        $weekdays = 0;
        if ($this->alarmMon) $weekdays |= self::DAY_BITS['mon'];
        if ($this->alarmTue) $weekdays |= self::DAY_BITS['tue'];
        if ($this->alarmWed) $weekdays |= self::DAY_BITS['wed'];
        if ($this->alarmThu) $weekdays |= self::DAY_BITS['thu'];
        if ($this->alarmFri) $weekdays |= self::DAY_BITS['fri'];
        if ($this->alarmSat) $weekdays |= self::DAY_BITS['sat'];
        if ($this->alarmSun) $weekdays |= self::DAY_BITS['sun'];

        [$hour, $minute] = array_map('intval', explode(':', $this->alarmTime));

        $alarm = Alarm::findOrFail($this->editingAlarmId);
        $alarm->update([
            'isset' => $this->alarmIsSet,
            'weekdays' => $weekdays,
            'hour' => $hour,
            'minute' => $minute,
            'week' => $this->alarmWeek,
            'chime' => $this->alarmChime,
            'modified' => now(),
        ]);

        $this->editingAlarmId = null;

        Notification::make()->title('Alarm updated')->success()->send();
    }

    public function cancelEdit(): void
    {
        $this->editingAlarmId = null;
    }

    public function addAlarm(int $deviceId): void
    {
        $now = now();
        Alarm::create([
            'device_id' => $deviceId,
            'isset' => true,
            'weekdays' => 0b01111100,
            'hour' => 7,
            'minute' => 0,
            'week' => 'all',
            'chime' => null,
            'modified' => $now,
            'created' => $now,
        ]);

        Notification::make()->title('Alarm added')->success()->send();
    }

    public function deleteAlarm(int $alarmId): void
    {
        Alarm::findOrFail($alarmId)->delete();

        Notification::make()->title('Alarm deleted')->success()->send();
    }

    public function editDeviceName(int $deviceId): void
    {
        $device = Device::findOrFail($deviceId);
        $this->editingDeviceNameId = $deviceId;
        $this->editingDeviceName = $device->name;
    }

    public function saveDeviceName(): void
    {
        $device = Device::findOrFail($this->editingDeviceNameId);
        $device->update([
            'name' => $this->editingDeviceName ?: null,
            'modified' => now(),
        ]);

        $this->editingDeviceNameId = null;
        $this->editingDeviceName = '';

        Notification::make()->title('Device name updated')->success()->send();
    }

    public function cancelDeviceNameEdit(): void
    {
        $this->editingDeviceNameId = null;
        $this->editingDeviceName = '';
    }
}
