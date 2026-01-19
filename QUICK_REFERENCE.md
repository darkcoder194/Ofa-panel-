# 📋 OFA Panel - Installation Quick Reference

**Print this or bookmark for quick access during installation**

---

## ⚡ 60-Second Installation

```bash
cd /var/www/pterodactyl
composer require darkcoder194/ofa-panel
php artisan ofa:install
npm install && npm run build
```

✅ Done! Visit: `https://your-panel.com/admin/ofa`

---

## ✅ Prerequisites

```bash
php --version              # Need 8.0+ (8.1+ recommended)
composer --version         # Need latest
node --version             # Need 18+
npm --version              # Need 9+
mysql --version            # Need 8.0+
redis-cli --version        # Optional but recommended
```

---

## 🚀 Installation Methods

| Method | Time | Best For |
|--------|------|----------|
| **Composer** | 2 min | Production |
| **Script** | 3 min | Beginners |
| **Manual** | 10 min | Custom setup |
| **Git** | 5 min | Development |

---

## 🎯 Quick Setup

### Step 1: Install Package
```bash
composer require darkcoder194/ofa-panel
```

### Step 2: Run Installer
```bash
php artisan ofa:install
```

### Step 3: Build Assets
```bash
npm install && npm run build
```

### Step 4: Access Panel
```
https://your-panel.com/admin/ofa
```

---

## 🧪 Verify Installation

```bash
# Check migrations
php artisan migrate:status

# Check routes
php artisan route:list | grep ofa

# Test database
php artisan tinker
>>> DB::table('ofa_settings')->first()
>>> exit
```

---

## 🆘 Common Issues & Fixes

| Issue | Fix |
|-------|-----|
| Command not found | `composer dump-autoload` |
| Database error | `php artisan migrate --force` |
| Assets missing | `npm install && npm run build` |
| Permission denied | `sudo chown -R www-data:www-data .` |
| 404 error | `php artisan route:cache` then `php artisan route:clear` |

---

## 📁 Key Files

| File | Purpose |
|------|---------|
| `config/ofa.php` | Configuration |
| `routes/ofa.php` | Routes |
| `resources/js/admin/` | Frontend |
| `resources/css/ofa-theme.css` | Styling |
| `.env` | Environment |

---

## 🔗 Documentation

| Document | Purpose |
|----------|---------|
| [INSTALL.md](INSTALL.md) | Full installation guide |
| [SETUP_GUIDE.md](SETUP_GUIDE.md) | Step-by-step walkthrough |
| [TROUBLESHOOTING.md](TROUBLESHOOTING.md) | Problem solving |
| [QUICK_START.md](QUICK_START.md) | Quick commands |
| [FEATURES.md](FEATURES.md) | Features list |

---

## 💾 Database Tables Created

- ✅ `ofa_settings` - Settings storage
- ✅ `ofa_theme_palettes` - Theme colors
- ✅ `ofa_server_actions` - Server actions
- ✅ `plans` - Billing plans
- ✅ `orders` - Billing orders
- ✅ `invoices` - Billing invoices
- ✅ `wallets` - User wallets
- ✅ `wallet_transactions` - Transactions

---

## ⚙️ Configuration (config/ofa.php)

```php
'features' => [
    'theme' => true,           // Theme manager
    'console' => true,         // Server console
    'minecraft' => true,       // Minecraft tools
    'addons' => true,          // Addons system
    'billing' => false,        // Billing (set to true to enable)
],
```

---

## 🎨 First Time Setup

1. **Access Dashboard**
   ```
   https://your-panel.com/admin/ofa
   ```

2. **Configure Theme**
   - Click "Theme Manager"
   - Select colors
   - Test preview
   - Save

3. **Update Branding**
   - Click "Branding"
   - Set logo, name, footer
   - Save

4. **Enable Features**
   - Edit `config/ofa.php`
   - Set `'billing' => true` (if needed)
   - Save and rebuild

---

## 📞 Support Resources

**Having issues?**

1. Check [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
2. See diagnostic commands below
3. Check Laravel logs: `storage/logs/laravel.log`

**Diagnostic commands:**
```bash
# System info
php -v && node -v && npm -v

# Database check
php artisan migrate:status

# Cache status
php artisan cache:clear && php artisan config:cache

# Route check
php artisan route:list | grep ofa

# Log tail
tail -f storage/logs/laravel.log
```

---

## 🔄 Update Process

```bash
# Update via Composer
composer update darkcoder194/ofa-panel

# Run migrations
php artisan migrate

# Clear cache
php artisan cache:clear

# Rebuild assets (if needed)
npm install && npm run build
```

---

## 🔐 Permissions

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/pterodactyl

# Set permissions
sudo chmod -R 755 /var/www/pterodactyl
sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache
```

---

## 🌐 Routes Created

- `GET /admin/ofa` - Dashboard
- `GET /admin/ofa/themes` - Theme manager
- `GET /admin/ofa/addons` - Addons
- `GET /admin/ofa/billing` - Billing (if enabled)
- `GET /admin/ofa/settings` - Settings
- `GET /store` - Public store (if billing enabled)

---

## 💡 Tips

- ✅ Use **bash install.sh** for interactive setup
- ✅ Enable **Redis** for better performance
- ✅ Keep **Node.js** updated (18+)
- ✅ Test theme **before** deploying
- ✅ Backup **database** before updates
- ✅ Use **HTTPS** in production
- ✅ Set **APP_ENV=production** in .env

---

## 📊 Post-Installation Checklist

- [ ] Installed via Composer/Script/Manual
- [ ] Ran `php artisan ofa:install`
- [ ] Built assets with `npm run build`
- [ ] Verified `/admin/ofa` accessible
- [ ] Configured theme colors
- [ ] Updated branding
- [ ] Enabled required features
- [ ] Created admin users (if needed)
- [ ] Backed up database
- [ ] Tested all features
- [ ] Reviewed FEATURES.md

---

## 🎉 Success!

If you see the OFA dashboard at `/admin/ofa`, installation is complete!

**Next:** Customize your theme and enable features.

---

**Version:** v1.0.5 | **Updated:** January 19, 2026
