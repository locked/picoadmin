<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Metric extends Model
{
    public $timestamps = false;

    const TYPE_TEMPERATURE = 0;
    const TYPE_CO2 = 1;
    const TYPE_WATER_LEVEL = 2;
    const TYPE_BATTERY = 3;
    const TYPE_BATTERY2 = 4;
    const TYPE_DISTANCE = 5;
    const TYPE_PUMP = 6;
    const TYPE_HUMIDITY = 7;
    const TYPE_ECO2 = 8;
    const TYPE_SCD43_CO2 = 9;
    const TYPE_STCC4_CO2 = 10;
    const TYPE_TVOC = 11;
    const TYPE_MEM_FREE = 12;

    const TYPE_LABELS = [
        self::TYPE_TEMPERATURE => 'Temperature',
        self::TYPE_CO2 => 'CO2',
        self::TYPE_WATER_LEVEL => 'Water Level',
        self::TYPE_BATTERY => 'Battery',
        self::TYPE_BATTERY2 => 'Battery 2',
        self::TYPE_DISTANCE => 'Distance',
        self::TYPE_PUMP => 'Pump',
        self::TYPE_HUMIDITY => 'Humidity',
        self::TYPE_ECO2 => 'eCO2',
        self::TYPE_SCD43_CO2 => 'SCD43 CO2',
        self::TYPE_STCC4_CO2 => 'STCC4 CO2',
        self::TYPE_TVOC => 'TVOC',
        self::TYPE_MEM_FREE => 'Mem Free',
    ];

    protected $fillable = [
        'device_id',
        'metric_type',
        'metric_value',
        'metric_date',
        'created',
    ];

    protected $casts = [
        'metric_date' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public static function typeLabel(int $type): string
    {
        return self::TYPE_LABELS[$type] ?? 'Unknown';
    }

    public static function allowedTypesForDevice(?string $type): array
    {
        return match ($type) {
            'water' => [self::TYPE_TEMPERATURE, self::TYPE_BATTERY, self::TYPE_BATTERY2, self::TYPE_DISTANCE],
            'clock' => [self::TYPE_TEMPERATURE, self::TYPE_CO2, self::TYPE_ECO2, self::TYPE_SCD43_CO2, self::TYPE_STCC4_CO2, self::TYPE_TVOC],
            default => [],
        };
    }
}
