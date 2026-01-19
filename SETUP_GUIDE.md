# 🎯 OFA Panel - Complete Setup Guide

A comprehensive step-by-step guide to get OFA Panel up and running.

---

## 📖 Table of Contents

1. [Before You Start](#before-you-start)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Verification](#verification)
5. [First Steps](#first-steps)
6. [Enable Features](#enable-features)
7. [Customization](#customization)

---

## ✅ Before You Start

### System Requirements

Verify you have all prerequisites installed:

```bash
# Check PHP (need 8.0+, recommend 8.1+)
php --version

# Check MySQL (need 8.0+)
mysql --version

# Check Redis (optional but recommended)
redis-cli --version

# Check Node.js (need 18+)
node --version

# Check npm (need 9+)
npm --version

# Check Composer (latest)
composer --version

# Check Pterodactyl installation
ls /var/www/pterodactyl/artisan
```

### Access Requirements

- SSH access to your server
- Database access credentials
- Pterodactyl admin account

---

## 🚀 Installation

### Step 1: Navigate to Pterodactyl Directory

```bash
cd /var/www/pterodactyl
```

### Step 2: Install OFA Panel

**Option A: Using Composer (Recommended)**

```bash
# Install via Composer
composer require darkcoder194/ofa-panel

# Wait for it to complete...
```

**Option B: Manual Installation**

```bash
# Create directory
mkdir -p vendor/darkcoder194

# Clone repository
cd vendor/darkcoder194
git clone https://github.com/darkcoder194/ofa-panel.git
cd /var/www/pterodactyl

# Update autoloader
composer dump-autoload
```

### Step 3: Run Installation Wizard

```bash
php artisan ofa:install
```

This command will:
- ✅ Check Pterodactyl installation
- ✅ Verify PHP version
- ✅ Check Redis availability
- ✅ Publish config files
- ✅ Run database migrations
- ✅ Seed theme data
- ✅ Clear cache

**Output example:**
```
═══════════════════════════════════════════════════
        🚀 OFA PANEL INSTALLATION WIZARD 🚀
═══════════════════════════════════════════════════

📋 Running pre-installation checks...
✅ Pterodactyl Panel detected
✅ PHP 8.1.0
✅ Redis enabled
✅ Assets published
✅ Migrations completed
✅ Theme data seeded

✨ Installation complete!
```

### Step 4: Build Frontend Assets

```bash
# Install npm dependencies
npm install

# Build assets
npm run build
```

This creates:
- `public/js/ofa-admin.js` - Admin dashboard
- `public/css/ofa-theme.css` - Theme styling

---

## 🔧 Configuration

### Step 1: Review Environment Variables

Edit `/var/www/pterodactyl/.env`:

```env
# Must match your database
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

# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-panel-domain.com
```

### Step 2: Configure OFA Settings

Edit `config/ofa.php`:

```php
<?php

return [
    // Enable/disable features
    'features' => [
        'theme' => true,           // 🎨 Theme manager
        'console' => true,         // 🧭 Server console
        'minecraft' => true,       // 🟩 Minecraft tools
        'addons' => true,          // 🧩 Addons system
        'billing' => false,        // 💳 Billing (set true to enable)
    ],

    // Theme configuration
    'theme' => [
        'default' => 'dark',       // Default theme (dark/light)
        'glow_effects' => true,    // Enable glow animations
        'animations' => true,      // Enable CSS animations
        'rounded_cards' => true,   // Rounded card corners
    ],

    // Billing configuration (if enabled)
    'billing' => [
        'currency' => 'USD',
        'currency_symbol' => '$',
        'tax_rate' => 0.0,
    ],

    // Feature toggles
    'admin_features' => [
        'branding' => true,        // Allow custom branding
        'theme_manager' => true,   // Theme customization
        'api_access' => true,      // API endpoints
    ],
];
```

### Step 3: Set File Permissions

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/pterodactyl

# Set permissions
sudo chmod -R 755 /var/www/pterodactyl
sudo chmod -R 775 /var/www/pterodactyl/storage
sudo chmod -R 775 /var/www/pterodactyl/bootstrap/cache

# Make storage fully writable
sudo chmod -R 777 /var/www/pterodactyl/storage
```

---

## ✔️ Verification

### Step 1: Verify Installation

```bash
# Check migrations ran
php artisan migrate:status

# Should show OFA migrations as ✓ (complete)
```

**Expected output:**
```
database/migrations
  2025_12_18_000000_create_ofa_settings_table ✓
  2025_12_18_000002_create_ofa_theme_palettes_table ✓
  2025_12_19_000000_create_ofa_server_actions_table ✓
  2025_12_20_000000_create_billing_tables ✓
  2025_12_20_000001_create_addon_tables ✓
```

### Step 2: Check Database Tables

```bash
# Connect to database
mysql -u pterodactyl -p pterodactyl

# List OFA tables
SHOW TABLES LIKE 'ofa_%';

# Should show:
# ofa_settings
# ofa_theme_palettes
# ofa_server_actions
```

### Step 3: Verify Routes Are Registered

```bash
# List OFA routes
php artisan route:list | grep ofa

# Should show routes like:
# /admin/ofa/dashboard
# /admin/ofa/themes
# /admin/ofa/settings
```

### Step 4: Test Admin Access

```bash
# Check if you're a root admin
php artisan tinker
>>> auth()->user()->root_admin  # Should output: true
>>> exit
```

---

## 🎬 First Steps

### Step 1: Access Admin Dashboard

1. Open your browser
2. Go to: `https://your-panel-domain.com/admin/ofa`
3. Log in if prompted
4. You should see the OFA admin dashboard

**Dashboard includes:**
- 🎨 Theme Manager
- 🧩 Addons
- 💳 Billing (if enabled)
- 📊 Statistics
- ⚙️ Settings

### Step 2: Customize Theme

1. Click on **Theme Manager**
2. Select a preset theme or customize colors:
   - Primary color
   - Secondary color
   - Accent color
3. Toggle Dark/Light mode
4. Preview theme
5. Click **Save Theme**

### Step 3: Update Branding

1. Click on **Branding**
2. Configure:
   - Panel name
   - Logo/Wallpaper
   - "Powered By" text
   - Footer links
3. Click **Save**

### Step 4: Create Admin User (Optional)

If you need additional admin accounts:

```bash
# Use Tinker
php artisan tinker

# Create admin
>>> $user = new \App\Models\User();
>>> $user->email = 'admin@example.com';
>>> $user->password = bcrypt('SecurePassword123!');
>>> $user->root_admin = true;
>>> $user->save();
>>> exit

# Output: User created successfully
```

---

## 🎚️ Enable Features

### Theme Manager

Edit `config/ofa.php`:

```php
'features' => [
    'theme' => true,  // ✅ Enabled by default
],
```

Features:
- 🌙 Dark/Light mode
- 🎨 Color customization
- 👁️ Preview before applying
- 📤 Import/Export themes
- 🎭 Preset themes

---

### Server Console

Edit `config/ofa.php`:

```php
'features' => [
    'console' => true,  // ✅ Enabled by default
],
```

Features:
- 💻 Server console access
- ⌨️ Command execution
- 📊 Real-time stats

---

### Minecraft Tools

Edit `config/ofa.php`:

```php
'features' => [
    'minecraft' => true,  // ✅ Enabled by default
],
```

Features:
- 🛠️ server.properties editor
- 📥 Plugin installer
- 📦 Mod installer
- 🌍 World manager
- 👥 Player manager

---

### Addons System

Edit `config/ofa.php`:

```php
'features' => [
    'addons' => true,  // ✅ Enabled by default
],
```

Features:
- 🌐 Subdomain manager
- 🎫 Ticket system
- 📥 Server importer
- 🔀 Reverse proxy

---

### Billing System (Advanced)

Edit `config/ofa.php`:

```php
'features' => [
    'billing' => true,  // Set to true
],

'billing' => [
    'currency' => 'USD',
    'currency_symbol' => '$',
    'tax_rate' => 0.0,
],
```

Setup steps:

1. **Configure Payment Gateway:**
   ```php
   'payment_gateway' => 'stripe',  // stripe, paypal, or razorpay
   ```

2. **Set API Keys in .env:**
   ```env
   STRIPE_PUBLIC_KEY=pk_live_...
   STRIPE_SECRET_KEY=sk_live_...
   ```

3. **Create Plans:**
   - Visit `/admin/ofa/billing/plans`
   - Click "New Plan"
   - Set price, resources, duration
   - Save

4. **Enable Store:**
   - Visit `/admin/ofa/billing/store`
   - Enable store
   - Configure settings

---

## 🎨 Customization

### Custom Theme Colors

```php
// In config/ofa.php
'theme' => [
    'default_palette' => [
        'primary' => '#6366F1',      // Indigo
        'secondary' => '#8B5CF6',    // Purple
        'accent' => '#EC4899',       // Pink
        'success' => '#10B981',      // Green
        'warning' => '#F59E0B',      // Orange
        'danger' => '#EF4444',       // Red
        'background' => '#0F172A',   // Dark Blue
        'surface' => '#1E293B',      // Lighter Blue
        'text' => '#F1F5F9',         // Light Gray
    ],
],
```

### Custom CSS

Add to `resources/css/ofa-theme.css`:

```css
/* Your custom styles */
:root {
    --ofa-primary: #6366F1;
    --ofa-secondary: #8B5CF6;
    --ofa-accent: #EC4899;
}
```

### Custom JavaScript

Add to `resources/js/admin/app.js`:

```javascript
// Your custom scripts
console.log('OFA Panel Loaded');
```

Rebuild assets:
```bash
npm run build
```

---

## 🧪 Testing

### Test Admin Dashboard

```bash
curl -I https://your-panel.com/admin/ofa
# Should return: HTTP 200
```

### Test Theme API

```bash
# Get all themes
curl -H "Authorization: Bearer YOUR_API_TOKEN" \
  https://your-panel.com/api/ofa/themes

# Should return JSON array of themes
```

### Test Database Connection

```bash
php artisan tinker
>>> DB::connection()->getDatabaseName()
# Should output: pterodactyl
>>> exit
```

---

## 🆘 Troubleshooting

### Dashboard Returns 404

**Solution:**

```bash
# Clear routes and cache
php artisan route:clear
php artisan cache:clear
php artisan config:cache

# Verify authentication
php artisan tinker
>>> auth()->user()->root_admin
```

### CSS/JS Not Loading

**Solution:**

```bash
# Rebuild assets
npm install
npm run build

# Clear browser cache
# Use Ctrl+Shift+Delete to clear cache
# Or open in incognito mode
```

### Database Errors

**Solution:**

```bash
# Check migrations
php artisan migrate:status

# Retry migrations
php artisan migrate --force

# Check database
mysql -u pterodactyl -p pterodactyl
SHOW TABLES LIKE 'ofa_%';
```

For more help, see [TROUBLESHOOTING.md](TROUBLESHOOTING.md)

---

## 📚 Next Steps

1. **Read Documentation:** [FEATURES.md](FEATURES.md)
2. **Explore Dashboard:** Visit `/admin/ofa`
3. **Configure Features:** Customize in `config/ofa.php`
4. **Customize Theme:** Use Theme Manager
5. **Setup Billing (Optional):** Configure payment gateway
6. **Test Features:** Try each feature module

---

## 📞 Support

- **GitHub Issues:** https://github.com/darkcoder194/ofa-panel/issues
- **Documentation:** [INSTALL.md](INSTALL.md)
- **Quick Start:** [QUICK_START.md](QUICK_START.md)
- **Troubleshooting:** [TROUBLESHOOTING.md](TROUBLESHOOTING.md)

---

**Version:** v1.0.5  
**Last Updated:** January 19, 2026  
**Status:** ✅ Production Ready
