<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasName, HasAvatar
{
    use HasFactory, Notifiable;

    public $timestamps = false;

    protected $fillable = [
        'email',
        'firstname',
        'lastname',
        'google_id',
        'avatar',
        'role',
        'modified',
        'created',
    ];

    protected $hidden = [
        'google_id',
        'remember_token',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function getFilamentName(): string
    {
        return trim(($this->firstname ?? '') . ' ' . ($this->lastname ?? '')) ?: $this->email;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar;
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    protected function casts(): array
    {
        return [];
    }
}
