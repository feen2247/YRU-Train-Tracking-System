# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

YRU Train Tracking System: an electric-tram (EV) tracking and management web app for Yala Rajabhat University. It's Laravel 12 (PHP 8.2+) with Blade views. Most UI text, comments, role names and status values are in Thai, and the timezone is forced to `Asia/Bangkok` in `AppServiceProvider` (which also shares `$currentThaiFormattedDate`, a Buddhist-era date, with all views). `README.md` (in Thai) has the full setup guide, the test accounts and the page list.

## Running locally

1. `composer install` (or `php composer.phar install`), then `npm install`.
2. `cp .env.example .env`, then `php artisan key:generate`.
3. Set the DB in `.env`. MySQL matches production. For SQLite, set `DB_CONNECTION=sqlite`, remove the other `DB_*` lines and create an empty `database/database.sqlite`. `/api/debug-auth` runs `DESCRIBE`, so it works only on MySQL.
4. `php artisan migrate --seed`. This is required before the first request, because sessions and cache use the `database` driver. The shared-state sync (below) lives in the `cache` table.
5. `php artisan serve`, then open http://localhost:8000. `composer dev` does not work on Windows: `php artisan pail` needs `ext-pcntl`, and `--kill-others` then stops every process. The main pages don't use `@vite` (only `layouts/app.blade.php` does), so Vite isn't needed to run the app.

Seeded users (`database/seeders/UserSeeder.php`) log in with username, email or employee ID. The password is the employee ID. Examples:
- `muhammad`/`69001`: Administrator
- `somchai`/`69002`: Executive
- `asmee`/`69003`: Driver
- `prasan`/`69013`: Mechanic
- `suthin`/`69014`: vehicle_head
- `406665014`: Student

Pages need internet access because Tailwind, Leaflet and the other front-end libraries load from CDNs.

## Commands

```bash
composer setup            # install deps, copy .env, key:generate, migrate, npm install + build (does not seed)
composer dev              # serve + queue:listen + pail logs + vite, run concurrently
composer test             # config:clear then php artisan test
php artisan test --filter=ExampleTest          # single test class/method
php artisan test tests/Feature/ExampleTest.php # single file
vendor/bin/pint           # code style (Laravel Pint)
npm run build             # Vite build (resources/sass/app.scss, resources/js/app.js)
php artisan migrate --seed
```

`composer.phar` is committed, so use `php composer.phar ...` if Composer isn't installed globally. The tests run on in-memory SQLite (`phpunit.xml`). Only the example tests exist. `.env.example` defaults to MySQL, and `config/database.php` falls back to SQLite.

## Architecture

**Most logic lives in `routes/web.php` (~2000 lines), not in controllers.** Page routes and most `/api/*` endpoints are inline closures. These include driver status, EV calls, seat counts, stats, SOS, maintenance, OTP register and password reset. Some endpoints are defined twice, for example `POST /api/update-driver-status`, `GET /api/maintenance/requests` and `POST /api/maintenance/requests/{id}/submit-quotation`. When the method and URI are the same, the definition registered later wins. The controller-backed maintenance routes at the bottom of the file override the closures above them. Before editing an endpoint, grep for every definition of it.

**Views are huge single-file SPAs.** Each role has one Blade page under `resources/views/passenger/<area>/index.blade.php` (admin ~9k lines, tracking ~6k, maintenance ~4.6k, executive, home, vehicle-head, welcome). Each page has its own inline JS and loads Tailwind, Leaflet, Chart.js, SweetAlert2, etc. from CDNs rather than through Vite. Admin sub-sections are in `passenger/admin/pages/*.blade.php`. Area-to-route mapping:
- `/` welcome (login/register)
- `/home` passenger map
- `/tracking` driver
- `/admin-view` admin
- `/executive-view` executive (`ExecutiveController`)
- `/maintenance-system` mechanic
- `/vehicle-head` supervisor

Several alias URLs redirect to these.

**localStorage ↔ server cache sync.** Much of the app state lives in browser `localStorage` under `yru_*` keys, and each page shares it with other users through the server cache. Examples: `yru_trams_v18`, `yru_stops_v2`, `yru_users_v8`, `yru_call_queue`, `yru_maintenance_tickets_v3`, `yru_routes_v1`. Each page includes `<script src="/api/storage/init?v=...">`. That script is generated in `routes/web.php` (`$storageInitHandler`) and does three things:
- hydrates `localStorage` from `Cache` entries named `global_storage_<key>`
- monkey-patches `localStorage.setItem`/`removeItem` so every `yru_*` write is POSTed to `/api/storage/sync`, which stores it with `Cache::forever`
- skips the private auth keys (`yru_user_login`, `remembered_*`, …) in both directions

Consequences:
- A key bump like `yru_trams_v18` changes the shared schema. Update `$defaultKeys` and `/clear-all-caches` to match.
- Some state exists only in the cache and never reaches DB tables. `/clear-all-caches` resets it, along with per-vehicle `current_driver_status_<n>`, `global_storage_yru_car_status_EV-XX` and `admin_vehicle_lock_EV-XX`.
- `/api/system/sync-db-users` reconciles cached users with the `users` table.

**Auth & roles.** `users.user_role` is a free-text string. Code compares it against both English and Thai values, for example `driver`/`พนักงานขับรถ` and `admin`/`administrator`/`ผู้ดูแลระบบ`. Most page routes do their own inline `Auth::check()` plus role checks. The `role:` middleware (`CheckRole`, an exact-match `in_array`) is used only by the older `auth` group near the end of `web.php`. Login goes through AJAX `POST /api/login-submit` (`AuthController`). CSRF is disabled for `api/*` in `bootstrap/app.php`.

**ESP32 GPS** (`GpsController`, `GpsDevice`, `gps_devices` table, firmware in `firmware/esp32_gps_tracker/`). ESP32s POST to `/api/gps/report` with an `X-GPS-Key` header that must match `GPS_DEVICE_KEY`. Unknown `device_id`s auto-register. Admins bind each device to a vehicle (`vehicle_id` = tram id like `EV-01`, unique, so 1 GPS = 1 vehicle) on the admin `gps` page (`passenger/admin/pages/gps.blade.php`). Unlike most app state, this lives in the DB, not in the localStorage/cache sync. `/home` polls `/api/gps/positions` and overwrites in-memory `tram.coords` for vehicles whose GPS is online (`GPS_STALE_SECONDS`) via `applyLiveGps()`. Never write those live coords back to `yru_trams_v18`: every viewer would sync them to the server. Tests are in `tests/Feature/GpsTest.php`.

*Many devices.* `GPS_DEVICE_KEY` is one fleet-wide shared secret, not a per-device credential: every board is flashed with the same value. Boards are told apart by `device_id`, which `makeDeviceId()` derives from the ESP32's MAC, so leaving `DEVICE_ID = ""` in the sketch gives each board a unique id and the *same* sketch can be flashed to the whole fleet. Adding a board therefore needs no code or `.env` change — flash, read the `DEVICE_ID` off the serial monitor, bind it on the admin page. Nothing binds a key to a `device_id`, so any board holding the key can report as any `device_id`; per-device keys would mean a `device_key` column on `gps_devices` plus a lookup in `report()`. A comma-separated list of keys in `.env` is *not* supported — `config('services.gps.device_key')` reads a single value, and `report()` does one `hash_equals` against it.

*LAN testing.* `php artisan serve` binds to `127.0.0.1` only, so an ESP32 on the same Wi-Fi needs `--host=0.0.0.0` and `SERVER_URL` set to the PC's LAN IP. On Windows, also check the firewall: clicking Cancel on the first Windows Defender prompt creates *Block* inbound rules (named `CLI` for `php.exe`, `Apache HTTP Server` for `httpd.exe`) that silently drop LAN connections while `localhost` keeps working, which looks exactly like a firmware bug. Inspect with `netsh advfirewall firewall show rule name="CLI" verbose` and flip them to Allow from an elevated shell or `wf.msc`. Those rules match on the program, not the port, so fixing them once covers every later board.

**Maintenance workflow** (`MaintenanceController`, `MaintenanceRequest` model): driver request → supervisor verify → quotation → director (executive) approve → complete.

**Domain models:** `ElectricTrain`, `Route`/`RouteStop` (`route_code`, `route_color`), `Station`, `Schedule`, `TrainLocation`, `TravelHistory`, `Maintenance`, `MaintenanceRequest`, `Notification`, `RolePermission`. The field-level reference is `Data_Dictionary_YRU_Train_Tracking_System.docx`, generated by `_scripts/generate_data_dictionary_doc.mjs`.

## Repo layout notes

- The root `index.php` + `.htaccess` rewrite everything into `public/`, so the app can run from a shared-hosting web root that isn't `public/`.
- `_archives/` holds old full-project backups. `scratch/` holds one-off debug/probe scripts, some of which hit the live server. Neither is app code: don't edit or search them when changing the app, and don't treat them as a source of truth.
- `_scripts/` holds ad-hoc maintenance scripts: patchers (`fix_*.cjs`), data sync/migration to the live host, and FTP deployment in `_scripts/deployment/*.ps1`.

## Deployment

Production is shared hosting reached over FTP (no CI). `AGENTS.md` and `.agents/rules/auto_ftp_upload.md` ask agents to FTP-upload changed files after every edit, but they name **different** hosts: `host.site.yru.ac.th` vs `ftp.student.yru.ac.th`. `_scripts/deployment/sync_changed.ps1` uses `host.site.yru.ac.th` with a hardcoded file list and local path. Uploading changes the live site, so ask the user before deploying and confirm which host to use.
