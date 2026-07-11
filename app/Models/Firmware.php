<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Firmware extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'device_model_id',
        'version',
        'data',
        'modified',
        'created',
    ];

    protected $casts = [
        'data' => 'binary',
    ];

    public function deviceModel(): BelongsTo
    {
        return $this->belongsTo(DeviceModel::class);
    }
}
