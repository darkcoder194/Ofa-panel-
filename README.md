# OFA Panel — One For All (Dark Coder)

🧩 **OFA (One For All)** — Advanced Extension & Theme System for the Pterodactyl panel.

**Version:** v0.0.1
**Panel Name:** Dark Coder
**Base Platform:** Powered by Pterodactyl®

---

## 1️⃣ Project Overview 🔧

OFA is a Pterodactyl extension that provides an advanced theme and extension framework focused on secure admin-first customization. It adds server tools, plugin/mod installers, a recycle bin, AI suggestions, blueprint installation, and strict admin–user separation — all while keeping the Pterodactyl core unchanged.

---

## 2️⃣ System Requirements (v0.0.1) ⚙️

- OS: Ubuntu 20.04 / 22.04
- PHP: 8.1+
- MySQL: 8.0+
- Redis: Enabled
- Node.js: 18+
- Pterodactyl Panel: Latest stable
- Wings: Latest

---

## 3️⃣ Installation (Blueprint) 🚀

1. Upload OFA files to: `/var/www/pterodactyl`
2. Run: `php artisan blueprint:install ofa`
3. The installer validates dependencies and activates OFA modules.

### Installing as a Laravel/Pterodactyl extension

OFA is distributed as a PHP package that can be included in your Pterodactyl panel codebase. To register it:

- Add the package to your project (or copy the files into `/var/www/pterodactyl`).
- Run `composer dump-autoload` to register PSR-4 autoloading if you copied files manually.
- If installed via Composer, package discovery will automatically register `DarkCoder\\Ofa\\OfaServiceProvider`.
- Otherwise, register the service provider manually in `config/app.php`:

```php
'providers' => [
    // Other service providers...
    DarkCoder\\Ofa\\OfaServiceProvider::class,
],
```

Then run:

```bash
php artisan vendor:publish --tag=config
php artisan vendor:publish --tag=ofa-assets --force
php artisan migrate
php artisan db:seed --class=OfaThemeSeeder
php artisan blueprint:install ofa
```

This will publish the `config/ofa.php` file, the views, css and JS (`ofa-assets`), and migrations. The `blueprint:install ofa` command is a placeholder installer that validates environment requirements and prints next steps.

---

### Using the theme in your panel

Include the OFA blade partial in your panel layout (for example in `layouts/app.blade.php`) so CSS variables for the active palette are injected and the OFA stylesheet is loaded:

```blade
@include('vendor.ofa.partials.ofa-theme')
```

Compile the admin JS for the theme manager (simple example using Vite):

- Add an entry to your `resources/js/app.js` or Vite config that imports `resources/js/admin/ThemePage.vue` and mounts it on `#ofa-theme-app`.
- Build assets: run `npm install` then `npm run dev` (for development) or `npm run build` (for production). The Vite config includes a named entry `ofaAdmin` which will produce `public/js/ofa-admin.[hash].js`.

Note: this package provides a prototype admin theme manager Vue component at `resources/js/admin/ThemePage.vue`. It is a functional prototype and should be adapted to Pterodactyl's UI structure (Inertia/Blade) as needed. If you prefer not to use Vite, a small fallback file `public/js/ofa-admin.js` is included which logs instructions when loaded.

### Theme Preview

Admins can preview a palette without making it the default. Use the **Preview** button in the Theme Manager to apply a palette for your session. To clear a preview and return to the default palette, use the **Clear Preview** button. These actions call `POST /admin/ofa/themes/{id}/preview` and `POST /admin/ofa/preview/clear` respectively and require an authenticated admin session.

---

## 4️⃣ Admin Capabilities 🔐

**Full system control:**
- Access OFA Admin Dashboard
- Enable/disable OFA features
- View system health, logs and AI suggestions

**Theme & Branding (Admin only):**
- Change panel theme and select preset colours
- Admin colour picker, custom palettes, favourites
- Change panel name, "Powered By" text, logo, wallpaper

**Security & Management:**
- Manage domains and reverse proxy
- Monitor traffic, receive DDoS alerts, view audit logs
- Control backups, Google Drive integration, and limits

---

## 5️⃣ User Capabilities 👥

**Server management:**
- View server resource usage and status
- Start, stop, restart servers
- Change server software version (if allowed) — automatic backup + confirmation
- Request egg changes (admin-controlled)

**Installers & Managers:**
- Plugin installer (CurseForge API): search, install, update, remove
- Mod installer (Fabric/Forge) with uploads
- World manager (upload, switch, soft-delete, restore)
- Recycle Bin to restore deleted files
- Backup creation/restore/download (admin-controlled permissions)
- Add sub-users with limited permissions

---

## 6️⃣ User Restrictions ❌

Users cannot change panel-level settings: theme, panel name, logo, copyright or access admin tools.

---

## 7️⃣ UI & Rights Notice (Always Visible)

> © Dark Coder (Amrit Yadav). All Rights Reserved.
> Powered by Pterodactyl®
> Powered by <Admin Defined Name>

This notice cannot be removed or edited by users and applies to all themes.

---

## 8️⃣ Features in v0.0.1 ✅

- Blueprint installation
- Theme & colour system + admin colour picker
- Branding controls
- Server version & egg change systems
- Server splitter (resource blocks)
- Plugin & Mod installers
- World manager, Recycle Bin
- Backup system
- OFA AI suggestion system
- Strict admin–user separation

---

## 9️⃣ Short exam answer (3 lines)

> OFA is a Pterodactyl extension providing admin-controlled UI customization, plugin/mod installers, server management tools, backup and recovery features, AI-assisted suggestions, and strict separation of admin/user permissions while keeping the core platform secure.

---

## Contributing & License

Please see `CONTRIBUTING.md` for contribution guidelines and `LICENSE` for licensing details.

## Development & Testing 🧪

Quick commands to run locally (to verify / continue):

- Install PHP + Composer (Ubuntu):
  - `sudo apt-get update && sudo apt-get install -y php php-cli php-xml php-mbstring php-zip php-sqlite3 php-curl php-mysql php-redis unzip curl git`
  - `php -v`
  - Install Composer: `curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer`

- Install dev dependencies and run tests:
  - `composer install --prefer-dist --no-interaction`
  - `php artisan migrate`
  - `php artisan db:seed --class=OfaThemeSeeder`
  - `./vendor/bin/phpunit` or `./vendor/bin/phpunit --filter ThemeControllerTest`

Notes:
- This repository is a Laravel package/extension; tests use Orchestra Testbench. After `composer install`, the test environment will be available.
- A `phpunit.xml` is included and configures an in-memory SQLite database for tests by default.

### Real-time preview (optional)

OFA can broadcast palette preview changes to other admin sessions using Laravel broadcasting (Pusher, Redis + Socket.io, etc.). To enable real-time preview:

1. Configure your broadcasting driver in `config/broadcasting.php` and set appropriate env vars (`BROADCAST_DRIVER`, `PUSHER_APP_ID`, etc.).
2. Ensure client-side Echo is loaded in your admin JS and configured with your driver.
3. The server will broadcast `DarkCoder\Ofa\Events\PalettePreviewed` on the private channel `ofa.admin` when previews are applied or cleared.

This is optional — if broadcasting is not configured, preview actions remain session-scoped only.
