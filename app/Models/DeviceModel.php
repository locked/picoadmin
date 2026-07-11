<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeviceModel extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'type',
        'modified',
        'created',
    ];

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function firmwares(): HasMany
    {
        return $this->hasMany(Firmware::class);
    }
}
