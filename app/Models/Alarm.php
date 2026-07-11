<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alarm extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'device_id',
        'is_set',
        'weekdays',
        'hour',
        'minute',
        'week',
        'chime',
        'modified',
        'created',
    ];

    protected $casts = [
        'is_set' => 'boolean',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
