# 🔧 OFA Panel - Troubleshooting Guide

This guide helps you resolve common issues when installing or using OFA Panel.

---

## ❌ Installation Issues

### 1. "Command 'ofa:install' not found"

**Cause:** Service provider is not registered or autoloader is not updated.

**Solutions:**

```bash
# Solution 1: Update autoloader
composer dump-autoload
php artisan clear-compiled

# Solution 2: Clear all caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache

# Solution 3: Check if service provider exists
php artisan tinker
>>> config('app.providers')
```

**Should include:** `DarkCoder\Ofa\OfaServiceProvider`

If not present, manually add to `config/app.php`:
```php
'providers' => [
    // ... other providers
    DarkCoder\Ofa\OfaServiceProvider::class,
],
```

---

### 2. Database Migration Errors

**Cause:** Database connection issues or table conflicts.

**Solutions:**

```bash
# Check database connection
php artisan tinker
>>> DB::connection()->getPDO();

# Check migration status
php artisan migrate:status

# Run migrations with verbose output
php artisan migrate --verbose

# Rollback and retry (DEVELOPMENT ONLY!)
php artisan migrate:rollback
php artisan migrate
```

**If still failing:**

```bash
# Check if tables already exist
mysql -u pterodactyl -p pterodactyl
SHOW TABLES LIKE 'ofa_%';

# Drop and recreate (DEVELOPMENT ONLY!)
php artisan migrate:fresh --seed
```

---

### 3. Composer "Package Not Found" Error

**Cause:** Package not on Packagist or authentication issues.

**Solutions:**

```bash
# Clear composer cache
composer clear-cache

# Increase memory for composer
composer install -vvv --memory-limit=-1

# Update repositories
composer update darkcoder194/ofa-panel

# Manual installation
cd /var/www/pterodactyl/vendor
mkdir -p darkcoder194
cd darkcoder194
git clone https://github.com/darkcoder194/ofa-panel.git
cd /var/www/pterodactyl
composer dump-autoload
```

---

### 4. Node.js / npm Errors During Asset Build

**Cause:** Missing Node.js, npm version issues, or corrupted node_modules.

**Solutions:**

```bash
# Check versions
node --version      # Should be 18+
npm --version       # Should be 9+

# Clear npm cache
npm cache clean --force

# Delete and reinstall
rm -rf node_modules package-lock.json
npm install

# Build with verbose output
npm run build -- --debug

# If still failing, try yarn
npm install -g yarn
yarn install
yarn build
```

---

### 5. Assets Not Publishing

**Cause:** Vendor publish command failed or path issues.

**Solutions:**

```bash
# Publish with verbose output
php artisan vendor:publish --provider="DarkCoder\Ofa\OfaServiceProvider" --verbose

# Force republish
php artisan vendor:publish --provider="DarkCoder\Ofa\OfaServiceProvider" --force

# Check published assets
ls -la public/vendor/ofa/
ls -la config/ofa.php

# Manual copy (if publish fails)
cp -r vendor/darkcoder194/ofa-panel/resources/views resources/views/vendor/ofa
cp -r vendor/darkcoder194/ofa-panel/resources/css resources/css/vendor/ofa
cp -r vendor/darkcoder194/ofa-panel/resources/js resources/js/vendor/ofa
```

---

## ❌ Runtime Issues

### 6. Admin Dashboard Not Accessible (404 Error)

**Cause:** Routes not registered or auth middleware issues.

**Solutions:**

```bash
# Verify routes are loaded
php artisan route:list | grep ofa

# Clear route cache
php artisan route:cache
php artisan route:clear

# Check authentication
php artisan tinker
>>> auth()->user()  # Should show your user

# Verify root_admin status
>>> auth()->user()->root_admin  # Should be true

# Test the route directly
php artisan tinker
>>> Route::getRoutes()->getByName('admin.ofa.dashboard')
```

**If still failing:**

- Ensure you're logged in as a **Root Admin**
- Check browser console for errors (F12 → Console)
- Check server logs: `/var/www/pterodactyl/storage/logs/laravel.log`

---

### 7. CSS/JavaScript Not Loading (Styling Issues)

**Cause:** Assets not built, cache issues, or incorrect paths.

**Solutions:**

```bash
# Clear all caches
php artisan cache:clear
php artisan config:cache
php artisan view:cache

# Rebuild assets
npm run build

# Check asset paths
php artisan tinker
>>> asset('js/ofa-admin.js')

# Hard refresh browser (Ctrl+Shift+Delete)
# Or use incognito mode
```

**If assets missing:**

```bash
# Verify built files exist
ls -la public/js/ofa-admin*.js
ls -la public/css/ofa-theme*.css

# Rebuild if missing
npm install
npm run build

# Check permissions
ls -la public/
chmod 755 public/*
```

---

### 8. Theme Not Applying

**Cause:** Cache issues, database problems, or CSS conflicts.

**Solutions:**

```bash
# Clear caches
php artisan cache:clear
php artisan config:cache

# Verify theme palette exists in database
php artisan tinker
>>> DB::table('ofa_theme_palettes')->get()

# Reset to default theme
>>> DB::table('ofa_settings')->where('key', 'active_theme')->update(['value' => 'default'])

# Clear browser cache
# Use Incognito mode or hard refresh (Ctrl+Shift+Delete)
```

**If database empty:**

```bash
# Reseed themes
php artisan db:seed --class="DarkCoder\Ofa\Database\Seeders\OfaThemeSeeder"
```

---

### 9. 500 Internal Server Error

**Cause:** Code error, permission issue, or undefined method.

**Solutions:**

```bash
# Check error logs
tail -f /var/www/pterodactyl/storage/logs/laravel.log

# Enable debug mode (DEVELOPMENT ONLY)
# Edit .env: APP_DEBUG=true
nano /var/www/pterodactyl/.env
# Set: APP_DEBUG=true

# Clear all caches
php artisan cache:clear
php artisan config:cache
php artisan view:cache

# Check permissions
sudo chown -R www-data:www-data /var/www/pterodactyl
sudo chmod -R 755 /var/www/pterodactyl/storage
sudo chmod -R 755 /var/www/pterodactyl/bootstrap/cache
```

**Common 500 errors:**

| Error | Solution |
|-------|----------|
| `Class not found` | Run `composer dump-autoload` |
| `Table doesn't exist` | Run `php artisan migrate` |
| `Permission denied` | Fix permissions with `sudo chown` |
| `Model not defined` | Check `src/Models/` files exist |

---

### 10. Redis Connection Errors

**Cause:** Redis not running or misconfigured.

**Solutions:**

```bash
# Check if Redis is running
redis-cli ping  # Should return PONG

# Start Redis
sudo service redis-server start

# Check Redis configuration
cat /etc/redis/redis.conf | grep -E "^port|^bind"

# Test connection
redis-cli -h 127.0.0.1 -p 6379 ping

# If failing, disable Redis in .env (not recommended)
# Comment out these lines:
# REDIS_HOST=127.0.0.1
# REDIS_PASSWORD=null
# REDIS_PORT=6379

# Clear Redis cache
redis-cli FLUSHALL
```

---

### 11. Permission Denied Errors

**Cause:** Incorrect file ownership or permissions.

**Solutions:**

```bash
# Set correct ownership
sudo chown -R www-data:www-data /var/www/pterodactyl

# Set correct permissions
sudo chmod -R 755 /var/www/pterodactyl
sudo chmod -R 775 /var/www/pterodactyl/storage
sudo chmod -R 775 /var/www/pterodactyl/bootstrap/cache

# Make storage directory writable
sudo chmod -R 777 /var/www/pterodactyl/storage
sudo chmod -R 777 /var/www/pterodactyl/bootstrap/cache

# Check permissions
ls -la /var/www/pterodactyl/storage
ls -la /var/www/pterodactyl/bootstrap/cache
```

---

## ❌ Feature-Specific Issues

### 12. Billing Features Not Working

**Cause:** Billing config not set or payment gateway not configured.

**Solutions:**

```bash
# Check if billing is enabled
php artisan tinker
>>> config('ofa.features.billing')  # Should be true

# Enable billing
# Edit config/ofa.php:
return [
    'features' => [
        'billing' => true,  // Set to true
    ],
];

# Check billing tables
php artisan tinker
>>> DB::table('plans')->get()
>>> DB::table('orders')->get()

# Configure payment gateway
# Edit config/ofa.php:
'payment_gateway' => 'stripe',  // or 'paypal', 'razorpay'
'stripe_key' => env('STRIPE_KEY'),
'stripe_secret' => env('STRIPE_SECRET'),
```

---

### 13. Minecraft Tools Not Working

**Cause:** Server not connected or Pterodactyl API error.

**Solutions:**

```bash
# Check Pterodactyl API connectivity
php artisan tinker
>>> app('Pterodactyl\Services\Http\Client')->get('/api/servers')

# Verify server ID
>>> Pterodactyl\Models\Server::first()

# Test console command
>>> php artisan ofa:test-minecraft

# Check if server executable exists
# On Wings node:
ls -la /var/lib/pterodactyl/volumes/server-uuid/
```

---

### 14. Cannot Create Server (Billing)

**Cause:** API rate limits, insufficient resources, or config error.

**Solutions:**

```bash
# Check Pterodactyl logs
tail -f /var/www/pterodactyl/storage/logs/laravel.log

# Check Wings logs
tail -f /var/lib/pterodactyl/logs/wings.log

# Verify API token
php artisan tinker
>>> config('services.pterodactyl.key')
>>> config('services.pterodactyl.url')

# Test Pterodactyl API
curl -H "Authorization: Bearer YOUR_API_TOKEN" \
  https://your-panel.com/api/servers

# Check resource limits
php artisan tinker
>>> Pterodactyl\Models\Node::first()  # Check available RAM/Disk
```

---

## ❌ Performance Issues

### 15. Slow Dashboard Load

**Cause:** Missing Redis, large database, or N+1 queries.

**Solutions:**

```bash
# Ensure Redis is configured and running
redis-cli ping

# Check database query performance
# Enable query logging (DEVELOPMENT):
// In config/database.php or .env
DB_SLOW_QUERIES=true

# Run database optimization
php artisan tinker
>>> DB::statement('OPTIMIZE TABLE ofa_settings;')
>>> DB::statement('OPTIMIZE TABLE ofa_theme_palettes;')

# Clear and cache config
php artisan cache:clear
php artisan config:cache

# Use query profiler
# Edit .env: APP_DEBUG=true
# Use Laravel Debugbar
composer require barryvdh/laravel-debugbar --dev
```

---

### 16. Memory Exhaustion Error

**Cause:** Large file uploads, infinite loops, or memory leak.

**Solutions:**

```bash
# Increase PHP memory limit
# Edit /etc/php/8.1/fpm/php.ini:
memory_limit = 512M  # Increase from 128M

# Or set in .env/.htaccess:
php_value memory_limit 512M

# Check current limit
php -r "echo ini_get('memory_limit');"

# Restart PHP-FPM
sudo systemctl restart php8.1-fpm

# Check Laravel memory usage
php artisan tinker
>>> memory_get_usage(true) / 1024 / 1024  # MB

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

---

## 🔍 Diagnostic Commands

### Get System Information

```bash
# PHP version and modules
php -v
php -m | grep -E "redis|mysql|pdo"

# Node/npm versions
node -v && npm -v

# Database status
mysql -u pterodactyl -p pterodactyl -e "SELECT VERSION();"

# Redis status
redis-cli info server | grep redis_version

# Disk space
df -h /var/www/pterodactyl
du -sh /var/www/pterodactyl

# Service status
sudo systemctl status php-fpm
sudo systemctl status mysql
sudo systemctl status redis-server
sudo systemctl status nginx
```

### Check Laravel Configuration

```bash
php artisan env
php artisan config:show
php artisan route:list | grep ofa
php artisan migrate:status
php artisan package:discover
```

### View Application Logs

```bash
# Real-time log
tail -f /var/www/pterodactyl/storage/logs/laravel.log

# Last 50 lines
tail -50 /var/www/pterodactyl/storage/logs/laravel.log

# Search for errors
grep ERROR /var/www/pterodactyl/storage/logs/laravel.log
grep Exception /var/www/pterodactyl/storage/logs/laravel.log

# Check system logs
sudo journalctl -u php8.1-fpm -20  # Last 20 lines
sudo tail -f /var/log/nginx/error.log
```

---

## 📞 Getting Help

If you've tried all solutions above:

1. **Gather information:**
   ```bash
   # Create a diagnostic report
   php artisan ofa:diagnose > ofa_diagnostics.txt
   ```

2. **Check GitHub Issues:**
   - https://github.com/darkcoder194/ofa-panel/issues

3. **Contact Support:**
   - Check Discord/Forum (if available)
   - Open GitHub Issue with diagnostics

4. **Share diagnostics:**
   - Include `ofa_diagnostics.txt`
   - Include Laravel logs (sensitive data redacted)
   - Include error messages
   - Include your OS version

---

## 📚 Additional Resources

- **Installation Guide:** [INSTALL.md](INSTALL.md)
- **Features:** [FEATURES.md](FEATURES.md)
- **Deployment:** [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)
- **Quick Start:** [QUICK_START.md](QUICK_START.md)
- **Pterodactyl Docs:** https://pterodactylproject.org/docs
- **Laravel Docs:** https://laravel.com/docs

---

**Last Updated:** January 19, 2026  
**OFA Panel Version:** v1.0.5
