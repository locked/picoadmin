<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'device_model_id',
        'serialnumber',
        'target_firmware',
        'current_firmware',
        'user_id',
        'modified',
        'created',
    ];

    public function deviceModel(): BelongsTo
    {
        return $this->belongsTo(DeviceModel::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(Metric::class);
    }

    public function alarms(): HasMany
    {
        return $this->hasMany(Alarm::class);
    }

    public function isWaterType(): bool
    {
        return $this->deviceModel?->type === 'water';
    }

    public function isClockType(): bool
    {
        return $this->deviceModel?->type === 'clock';
    }
}
