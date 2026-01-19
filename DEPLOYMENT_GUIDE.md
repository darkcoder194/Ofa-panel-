# 🚀 OFA PANEL - DEPLOYMENT GUIDE

Complete installation and deployment guide for OFA Panel on Pterodactyl.

---

## 📋 SYSTEM REQUIREMENTS

### Server Requirements
- **OS**: Ubuntu 20.04 LTS / 22.04 LTS (or compatible)
- **PHP**: 8.0+ (8.1+ recommended)
- **MySQL**: 8.0+ or PostgreSQL 12+
- **Redis**: 6.0+ (for caching)
- **Node.js**: 18+ (for asset building)
- **Pterodactyl Panel**: Latest stable version

### Disk Space
- OFA Installation: ~50MB
- Database: ~100MB (base, grows with usage)
- Assets & Cache: ~200MB

---

## ⚡ QUICK START DEPLOYMENT

### Option 1: Package Installation (Recommended)

```bash
# 1. Install via Composer
cd /var/www/pterodactyl
composer require darkcoder194/ofa-panel

# 2. Run installation command
php artisan ofa:install

# 3. Build frontend assets
npm install
npm run build

# 4. Access admin dashboard
# Visit: https://your-panel.com/admin/ofa
```

### Option 2: Manual Installation

```bash
# 1. Clone or copy OFA files
cd /var/www/pterodactyl
git clone https://github.com/darkcoder194/ofa-panel vendor/darkcoder194/ofa-panel

# 2. Dump autoloader
composer dump-autoload

# 3. Publish assets
php artisan vendor:publish --provider="DarkCoder\Ofa\OfaServiceProvider" --tag=config
php artisan vendor:publish --provider="DarkCoder\Ofa\OfaServiceProvider" --tag=ofa-assets

# 4. Run migrations
php artisan migrate

# 5. Seed data
php artisan db:seed --class="DarkCoder\Ofa\Database\Seeders\OfaThemeSeeder"

# 6. Build frontend
npm install && npm run build

# 7. Clear cache
php artisan cache:clear
php artisan config:cache
```

---

## 🔧 CONFIGURATION

### 1. Environment Setup (.env)

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pterodactyl
DB_USERNAME=pterodactyl
DB_PASSWORD=your_password

# Redis (Optional but recommended)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# App Settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-panel.com
```

### 2. OFA Configuration (config/ofa.php)

```php
return [
    'enabled' => true,
    
    'branding' => [
        'panel_name' => 'Your Panel Name',
        'powered_by' => 'Pterodactyl®',
        'copyright' => '© Your Company. All Rights Reserved.',
    ],
    
    'features' => [
        'plugin_installer' => true,
        'mod_installer' => true,
        'world_manager' => true,
        'billing_enabled' => false,
        'addons_enabled' => true,
    ],
    
    'payment_gateways' => [
        'razorpay' => [
            'enabled' => false,
            'key' => env('RAZORPAY_KEY'),
            'secret' => env('RAZORPAY_SECRET'),
        ],
        'stripe' => [
            'enabled' => false,
            'key' => env('STRIPE_KEY'),
            'secret' => env('STRIPE_SECRET'),
        ],
    ],
];
```

### 3. Nginx Configuration

Add to your Pterodactyl Nginx config:

```nginx
location /admin/ofa {
    try_files $uri $uri/ /index.php?$query_string;
}

location /ofa/api {
    try_files $uri $uri/ /index.php?$query_string;
}
```

---

## 📦 INSTALLATION COMMAND

Run the interactive installation:

```bash
php artisan ofa:install
```

This command will:
- ✅ Check Pterodactyl installation
- ✅ Verify PHP version (8.0+)
- ✅ Check Redis availability
- ✅ Publish configuration files
- ✅ Run database migrations
- ✅ Seed theme data
- ✅ Build frontend assets
- ✅ Create admin user (optional)
- ✅ Clear caches

---

## 🎯 POST-INSTALLATION

### 1. Access Admin Dashboard
```
https://your-panel.com/admin/ofa
```

### 2. Configure Settings
1. Go to **Branding** tab
2. Upload company logo
3. Set panel colors & theme
4. Configure payment gateways

### 3. Create Server Plans (Billing)
1. Navigate to **Billing Admin**
2. Click **Create Plan**
3. Set resources (CPU, RAM, Disk)
4. Assign nodes
5. Set pricing

### 4. Enable Addons
1. Go to **Settings → Addons**
2. Toggle features on/off
3. Configure API keys (Cloudflare, etc.)

---

## 🚀 DEPLOYMENT CHECKLIST

- [ ] PHP 8.0+ installed
- [ ] Pterodactyl Panel running
- [ ] Redis installed & configured
- [ ] MySQL/PostgreSQL available
- [ ] Composer installed
- [ ] Node.js 18+ installed
- [ ] Git clone or package install
- [ ] Run `ofa:install` command
- [ ] Build frontend assets
- [ ] Create admin user
- [ ] Configure .env file
- [ ] Set payment gateway keys
- [ ] Test admin dashboard access
- [ ] Create test server plan
- [ ] Test billing checkout
- [ ] SSL certificate configured
- [ ] Firewall rules updated

---

## 📊 FEATURES ENABLED BY DEFAULT

### ✅ Activated
- Theme system (dark/light)
- Pterodactyl core integration
- Minecraft tools
- Admin dashboard
- Ticketing system

### ⚙️ Configure to Enable
- Billing system (toggle in config)
- Payment gateways (add API keys)
- Subdomain manager (add Cloudflare key)
- Reverse proxy (requires Nginx access)

---

## 🔐 SECURITY SETTINGS

### 1. Environment Variables
```bash
# Generate app key
php artisan key:generate

# Set strong APP_KEY
APP_KEY=base64:your_generated_key_here
```

### 2. File Permissions
```bash
chmod 755 /var/www/pterodactyl
chmod 755 /var/www/pterodactyl/storage
chmod 755 /var/www/pterodactyl/bootstrap/cache
```

### 3. Database Backup
```bash
# Create backup
mysqldump -u pterodactyl -p pterodactyl > backup.sql

# Restore backup
mysql -u pterodactyl -p pterodactyl < backup.sql
```

---

## 🐛 TROUBLESHOOTING

### Problem: "Command not found: ofa:install"
**Solution**: Run `php artisan optimize` to rebuild service container

### Problem: "Class not found" errors
**Solution**: Run `composer dump-autoload`

### Problem: Assets not loading
**Solution**: Run `npm run build` and check `public/` folder

### Problem: Database errors
**Solution**: 
```bash
php artisan migrate:fresh  # CAUTION: This resets database
php artisan migrate
```

### Problem: Permission denied errors
**Solution**: 
```bash
sudo chown -R www-data:www-data /var/www/pterodactyl
sudo chmod -R 755 /var/www/pterodactyl
```

---

## 📈 SCALING & OPTIMIZATION

### For High Traffic

```bash
# Enable query caching
php artisan config:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Enable Redis caching
CACHE_DRIVER=redis

# Use database for sessions
SESSION_DRIVER=database
```

### Database Optimization
```sql
-- Add indexes for performance
ALTER TABLE ofa_server_actions ADD INDEX (server_id);
ALTER TABLE ofa_billing_orders ADD INDEX (user_id);
ALTER TABLE ofa_tickets ADD INDEX (user_id);
```

---

## 🔄 UPDATES & MAINTENANCE

### Check for Updates
```bash
composer update darkcoder194/ofa-panel
```

### Backup Before Update
```bash
# Database backup
mysqldump -u pterodactyl -p pterodactyl > backup_$(date +%Y%m%d).sql

# Files backup
cp -r /var/www/pterodactyl /backup/pterodactyl_$(date +%Y%m%d)
```

### Apply Updates
```bash
php artisan migrate
php artisan cache:clear
npm run build
```

---

## 📞 SUPPORT & DOCUMENTATION

- **GitHub**: https://github.com/darkcoder194/ofa-panel
- **Issues**: Report bugs on GitHub Issues
- **Documentation**: Full docs in `docs/` folder
- **Email**: support@yourpanel.com

---

## 📄 LICENSE

OFA Panel is licensed under the MIT License. See LICENSE file for details.

---

**Version**: v0.0.2  
**Last Updated**: January 19, 2026  
**Author**: Dark Coder (Amrit Yadav)
