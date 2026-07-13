# AGENTS.md — PicoAdmin Project Guide

## Stack
- Laravel 13.19 + Filament v3 + MySQL + PHP 8.3
- Google OAuth via Socialite
- Chart.js 4.4.8 + chartjs-plugin-annotation 3.0 + chartjs-adapter-date-fns 3.0 (all in `public/js/vendor/`)

## Project Structure
- `app/Filament/Pages/` — Custom pages: Dashboard, MyDevices, DeviceGraphs, GoogleLogin
- `app/Filament/Resources/` — Admin CRUD: DeviceResource, DeviceModelResource, FirmwareResource, UserResource
- `app/Filament/Widgets/` — DeviceStatsOverview (admin), UserDeviceStats (user-scoped), MetricsChart
- `app/AvatarProviders/LocalInitialsProvider.php` — SVG initials avatar, no external calls
- `app/Models/` — User, Device, DeviceModel, Firmware, Metric, Alarm
- `resources/views/filament/pages/` — Blade templates for MyDevices, DeviceGraphs
- `public/js/vendor/` — Chart.js, annotation plugin, date-fns adapter (all locally hosted)

## Design Decisions

### No external CDNs
All JS/CSS/fonts hosted locally. No requests to ui-avatars.com, fonts.bunny.net, or any CDN. Filament font provider set to `LocalFontProvider` with no URL. Avatar provider uses inline SVG data URIs.

### No Redis/mail/S3
All unused configs stripped. Only MySQL and local filesystem.

### Database
- `modified`/`created` columns: `dateTime DEFAULT NOW()` (not bigint)
- Alarm column is `isset` (not `is_set`) — renamed via migration
- `metric_date` cast to `datetime` in Metric model

### Filament Panel Config (`AdminPanelProvider.php`)
- `discoverPages` is **removed** — caused double-registration of custom Dashboard, leading to Livewire CSRF "page expired" errors on every reload
- `discoverWidgets` is **kept** so widget classes are known to the panel; `Dashboard::getWidgets()` controls which render
- Custom pages registered explicitly in `->pages([...])`
- No `filament:upgrade` composer hook — causes bootstrap cache corruption

### Metric Types
```
TYPE_TEMPERATURE = 0, TYPE_CO2 = 1, TYPE_WATER_LEVEL = 2, TYPE_BATTERY = 3,
TYPE_BATTERY2 = 4, TYPE_DISTANCE = 5, TYPE_PUMP = 6, TYPE_HUMIDITY = 7,
TYPE_ECO2 = 8, TYPE_SCD43_CO2 = 9, TYPE_STCC4_CO2 = 10, TYPE_TVOC = 11,
TYPE_MEM_FREE = 12
```
- Water device: Temperature, Battery, Battery2, Distance (+ Pump for history)
- Clock device: Temperature, CO2, eCO2, SCD43_CO2, STCC4_CO2, TVOC

### Alarm Weekdays Bitmask
Mon=64, Tue=32, Wed=16, Thu=8, Fri=4, Sat=2, Sun=1

### Charts (DeviceGraphs)
- Data points use `{x: timestamp_ms, y: value}` format with Chart.js `type: 'time'` x-axis
- `min`/`max` set to the full selected range so axis spans the entire period
- Time range via URL query parameter `?range=` (24h/3d/7d/14d/30d), full page reload
- CO2/TVOC merged on one graph, Battery/Battery2 merged on one graph
- Normality zones via chartjs-plugin-annotation: Good (0-600 green), Acceptable (600-1000 yellow), Poor (1000-2500 red)
- `@push('scripts')` with `livewire:initialized` for Chart.js init — works on full page load
- Chart.js datasets include `borderColor`/`tension`/`fill`/`pointRadius` directly from PHP (no JS remapping)

### Pump History (MyDevices)
- Table shows last 10 pump triggers where `metric_value > 0`
- Duration formatted as seconds if >= 1000ms, otherwise milliseconds

## Gotchas & Pitfalls

### Filament
- `getFilamentName()` requires `HasName` interface on the model, not just the method
- `HasAvatar` interface requires `getFilamentAvatarUrl()` — Filament checks `avatar_url` attribute by default, not `avatar`
- Filament v3 `RelationManager` namespace is `Filament\Resources\RelationManagers\RelationManager`
- `<x-filament-forms::input>` does NOT exist — use plain HTML `<input>` elements
- `<x-filament::section>` ignores outer `space-y` and `class` attributes — wrap in plain `<div>` for spacing
- heroicon `m-droplet` does not exist — use `m-beaker`

### Livewire
- Removed `InteractsWithForms`/`HasForms`/`$data` from MyDevices — caused `PublicPropertyNotFoundException: Public property [$] not found` (Livewire v3 conflict)
- `@push('scripts')` content only executes once; does not re-run on Livewire re-renders
- `@script` directive causes Blade parse errors with complex `@js()` + Collection chains — avoid

### Chart.js
- Chart.js 4.x requires a date adapter for `type: 'time'` scale — use `chartjs-adapter-date-fns.bundle.min.js` (includes date-fns)
- Category x-axis spaces points evenly regardless of time gaps — always use `type: 'time'` for time series
- Set `min`/`max` on x-axis to fill the full range even with sparse data

### General
- `migrate:fresh` drops all data — use new migrations on live
- `<input type="time">` requires parsing "HH:MM" string to hour/minute int on save
- Filament default font is Inter loaded via `BunnyFontProvider` from `fonts.bunny.net` — overridden with `LocalFontProvider`

## Commands
- `php artisan about` — verify app boots
- `php -l <file>` — syntax check
- No Redis, no queue workers needed
