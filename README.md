# PicoAdmin

Admin panel for **picowater** (automatic watering) and **picoclock** (alarm clock) IoT devices.

## Stack

- Laravel 13 + Filament v3
- MySQL
- Google OAuth authentication

## Setup

```bash
cp .env.example .env
composer install
php artisan key:generate
```

Edit `.env` with your database credentials and Google OAuth keys, then:

```bash
php artisan migrate
php artisan db:seed --class=DeviceModelSeeder
```

To create the first admin user:

```bash
php artisan db:seed --class=AdminUserSeeder
```

## Run

```bash
php artisan serve
```

## Features

- Role-based access (admin / user)
- Device and model management (admin)
- Firmware versioning and download (admin)
- Alarm editing per device (weekday checkboxes, chime selection)
- Metrics display and Chart.js graphs per device type
- Dashboard with stats widgets
