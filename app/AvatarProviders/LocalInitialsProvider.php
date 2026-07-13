<?php

namespace App\AvatarProviders;

use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class LocalInitialsProvider implements \Filament\AvatarProviders\Contracts\AvatarProvider
{
    public function get(Model|Authenticatable $record): string
    {
        $name = str(Filament::getNameForDefaultAvatar($record))
            ->trim()
            ->explode(' ')
            ->map(fn (string $segment): string => filled($segment) ? mb_substr($segment, 0, 1) : '')
            ->join(' ');

        $initials = strtoupper($name);
        $initials = mb_substr($initials, 0, 2);

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 128 128">'
            . '<rect width="128" height="128" rx="128" fill="#111827"/>'
            . '<text x="64" y="64" text-anchor="middle" dominant-baseline="central" '
            . 'font-family="sans-serif" font-size="52" font-weight="600" fill="#FFFFFF">'
            . e($initials)
            . '</text></svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
