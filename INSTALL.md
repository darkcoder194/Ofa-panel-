# 📦 OFA Panel - Installation Guide

**OFA (One For All)** is an advanced extension for Pterodactyl that adds powerful features for server management, theming, and billing.

---

## 🚀 Quick Installation (3 Commands)

```bash
# 1. Install via Composer
composer require darkcoder194/ofa-panel

# 2. Run the installation wizard
php artisan ofa:install

# 3. Build frontend assets
npm install && npm run build
```

**Done!** Visit `https://your-panel.com/admin/ofa` to access OFA Panel.

---

## ✅ Prerequisites

Before installing, ensure you have:

- ✅ **Pterodactyl Panel** (Latest stable version)
- ✅ **PHP 8.0+** (8.1+ recommended)
- ✅ **MySQL 8.0+** or PostgreSQL 12+
- ✅ **Redis 6.0+** (for caching)
- ✅ **Node.js 18+** (for building assets)
- ✅ **Composer** (latest version)
- ✅ **npm** (with Node.js)

**Check your versions:**
```bash
php --version
mysql --version
redis-cli --version
node --version
npm --version
composer --version
```

---

## 📋 Installation Methods

### Method 1: Composer Installation (Recommended)

**Best for:** Production environments, package management

```bash
# Navigate to your Pterodactyl directory
cd /var/www/pterodactyl

# Install OFA Panel via Composer
composer require darkcoder194/ofa-panel

# Run installation
php artisan ofa:install

# Build frontend
npm install && npm run build
```

### Method 2: Manual Installation

**Best for:** Development, custom configurations

```bash
# Navigate to Pterodactyl directory
cd /var/www/pterodactyl

# Step 1: Publish config files
php artisan vendor:publish --provider="DarkCoder\Ofa\OfaServiceProvider" --tag=config

# Step 2: Publish assets
php artisan vendor:publish --provider="DarkCoder\Ofa\OfaServiceProvider" --tag=ofa-assets --force

# Step 3: Run migrations
php artisan migrate

# Step 4: Seed theme data
php artisan db:seed --class="DarkCoder\Ofa\Database\Seeders\OfaThemeSeeder"

# Step 5: Build assets
npm install
npm run build

# Step 6: Clear cache
php artisan cache:clear
php artisan config:cache
```

### Method 3: Git Clone (Development)

**Best for:** Contributing, debugging, development

```bash
# Navigate to Pterodactyl vendor directory
cd /var/www/pterodactyl/vendor
mkdir -p darkcoder194
cd darkcoder194

# Clone the repository
git clone https://github.com/darkcoder194/ofa-panel.git

# Back to Pterodactyl root
cd /var/www/pterodactyl

# Dump autoloader
composer dump-autoload

# Follow manual installation steps (see Method 2)
```

---

## 🔧 Configuration

### 1. Environment Variables (.env)

OFA uses your existing Pterodactyl `.env` file. Key variables:

```env
# Database (must be same as Pterodactyl)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pterodactyl
DB_USERNAME=pterodactyl
DB_PASSWORD=your_secure_password

# Redis (highly recommended)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# App Settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-panel.com
```

### 2. OFA Configuration (config/ofa.php)

After publishing assets, edit `config/ofa.php`:

```php
<?php

return [
    // Features
    'features' => [
        'theme' => true,           // Theme manager
        'console' => true,         // Server console
        'minecraft' => true,       // Minecraft tools
        'addons' => true,          // Addons system
        'billing' => false,        // Billing (enable as needed)
    ],

    // Theme defaults
    'theme' => [
        'default' => 'dark',       // dark or light
        'glow_effects' => true,
        'animations' => true,
    ],

    // Billing configuration (if enabled)
    'billing' => [
        'currency' => 'USD',
        'tax_rate' => 0.0,
    ],
];
```

---

## 🎯 Post-Installation

### 1. Verify Installation

Check that everything was installed correctly:

```bash
# Check migrations
php artisan migrate:status

# Check if OFA tables exist
mysql -u pterodactyl -p pterodactyl -e "SHOW TABLES LIKE 'ofa_%';"

# Test the admin route
curl -I https://your-panel.com/admin/ofa
```

### 2. Access Admin Dashboard

1. Go to: `https://your-panel.com/admin/ofa`
2. You must be logged in as a **Root Admin**
3. Features available:
   - 🎨 Theme Manager
   - 🧩 Addons
   - 💳 Billing (if enabled)
   - 🔐 Server Actions
   - 📊 Analytics

### 3. Create an Admin User (Optional)

If you need to create a new admin user:

```bash
php artisan tinker
```

```php
>>> $user = new \App\Models\User();
>>> $user->email = 'admin@example.com';
>>> $user->password = bcrypt('secure_password');
>>> $user->root_admin = true;
>>> $user->save();
```

### 4. Enable Features

Edit `config/ofa.php` to enable/disable features:

```php
'features' => [
    'theme' => true,        // ✅ Enable theme manager
    'console' => true,      // ✅ Enable console access
    'minecraft' => true,    // ✅ Enable Minecraft tools
    'addons' => true,       // ✅ Enable addons
    'billing' => true,      // ✅ Enable billing (optional)
],
```

---

## 🛠️ Troubleshooting

### Issue: "Command 'ofa:install' not found"

**Solution:** The service provider wasn't registered. Run:

```bash
composer dump-autoload
php artisan clear-compiled
php artisan cache:clear
```

### Issue: Database migrations failed

**Solution:** Check that your database connection works:

```bash
php artisan migrate:fresh --seed
```

⚠️ **Warning:** This will reset your database. Use only in development!

### Issue: Assets not loading (CSS/JS)

**Solution:** Rebuild frontend assets:

```bash
npm install
npm run build
php artisan cache:clear
```

### Issue: Redis not connected

**Solution:** Redis is optional but recommended. If not needed, comment out in `.env`:

```env
# REDIS_HOST=127.0.0.1
# REDIS_PASSWORD=null
# REDIS_PORT=6379
```

### Issue: Permissions denied

**Solution:** Set correct permissions:

```bash
# Run from Pterodactyl directory
sudo chown -R www-data:www-data /var/www/pterodactyl
sudo chmod -R 755 /var/www/pterodactyl
sudo chmod -R 755 /var/www/pterodactyl/storage
sudo chmod -R 755 /var/www/pterodactyl/bootstrap/cache
```

### Issue: Theme not applying

**Solution:** Clear browser cache and Pterodactyl cache:

```bash
# Clear application cache
php artisan cache:clear
php artisan config:cache

# Clear browser cache (Ctrl+Shift+Delete) or use incognito mode
```

---

## 📚 Next Steps

After successful installation:

1. **Customize Theme** → Visit `/admin/ofa/themes`
2. **Configure Billing** (optional) → Visit `/admin/ofa/billing`
3. **Enable Addons** → Visit `/admin/ofa/addons`
4. **Read Documentation** → See [FEATURES.md](FEATURES.md)

---

## 🔗 Resources

- **GitHub Repository:** [darkcoder194/ofa-panel](https://github.com/darkcoder194/ofa-panel)
- **Documentation:** [FEATURES.md](FEATURES.md)
- **Quick Start:** [QUICK_START.md](QUICK_START.md)
- **Pterodactyl Panel:** [pterodactylproject.org](https://pterodactylproject.org)

---

## 💬 Support

Having issues? Try:

1. Check [Troubleshooting](#-troubleshooting) section above
2. Review [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)
3. Check Pterodactyl logs: `/var/www/pterodactyl/storage/logs/`
4. Check OFA logs: Same location

---

## ✨ Features After Installation

### 🎨 Theming (7 features)
- Dark/Light mode toggle
- Custom color palettes
- Theme preview before applying
- Import/Export themes
- Preset themes
- Glow effects & animations
- Mobile responsive

### 🧭 Pterodactyl Core (9 features)
- Console & Commands
- File Manager
- Database Management
- Backups
- Network/Allocations
- Schedules
- User/Subuser Management
- Startup Configuration
- Server Stats & Power Controls

### 🟩 Minecraft Tools (5+ features)
- server.properties editor
- Plugin Installer (Spigot/Hangar)
- Mod Installer (CurseForge/Modrinth)
- World Manager
- Player Manager

### 🧩 Addons (4+ features)
- Subdomain Manager
- Staff Tickets
- Server Importer
- Reverse Proxy

### 💳 Billing System (5 features)
- Store & Plans
- Shopping Cart
- Auto Server Creation
- Wallet System
- Invoices & Renewals

---

## 📄 License

OFA Panel is open source and released under the **MIT License**. See [LICENSE](LICENSE) file for details.

---

**Version:** v1.0.5  
**Last Updated:** January 19, 2026  
**Status:** ✅ Production Ready
