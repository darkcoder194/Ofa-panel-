# 🚀 OFA PANEL - QUICK REFERENCE

## Installation Commands

### One-Line Installation
```bash
php artisan ofa:install
```

### What This Command Does
```
✅ Checks Pterodactyl Installation
✅ Verifies PHP 8.0+ (8.1+ recommended)
✅ Checks Redis availability
✅ Publishes config files
✅ Runs database migrations
✅ Seeds theme data
✅ Creates admin user (optional)
✅ Clears cache
✅ Displays feature list
```

---

## Manual Installation (Alternative)

```bash
# Step 1: Install via Composer
composer require darkcoder194/ofa-panel

# Step 2: Publish assets
php artisan vendor:publish --provider="DarkCoder\Ofa\OfaServiceProvider"

# Step 3: Run migrations
php artisan migrate

# Step 4: Build frontend
npm install && npm run build

# Step 5: Clear cache
php artisan cache:clear
php artisan config:cache
```

---

## ✨ ALL FEATURES AT A GLANCE

### 🎨 Theme (7 features)
- Dark/Light toggle
- Custom palettes
- Theme preview
- Import/Export
- Preset themes
- Glow effects
- Mobile responsive

### 🧭 Pterodactyl Core (9 features)
- Console + Commands
- File Manager
- Databases
- Backups
- Network/Ports
- Schedules
- Users/Subusers
- Startup Config
- Stats & Power

### 🟩 Minecraft (5 features)
- Config Editor
- Plugin Installer
- Mod Installer
- World Manager
- Player Manager

### 🧩 Addons (4 features)
- Subdomains
- Tickets
- Server Importer
- Reverse Proxy

### 💳 Billing (5 features)
- Store & Plans
- Cart & Checkout
- Orders & Invoices
- Wallet System
- Auto Server Creation

### 💰 Payment Gateways (4 ready)
- Razorpay ✅
- Stripe ✅
- PayPal ✅
- UPI ✅

### 🔐 Admin Dashboard (4 features)
- Revenue Dashboard
- Order Management
- User Management
- Plan Management

---

## 📊 STATISTICS

| Metric | Count |
|--------|-------|
| Controllers | 26 |
| Models | 9 |
| API Routes | 150+ |
| Database Tables | 10+ |
| Migrations | 5 |
| Lines of Code | 2,769+ |
| Features | 50+ |

---

## 🔧 CONFIGURATION FILES

### Main Config
```
config/ofa.php
```

### Environment Variables
```
.env (add these if needed)
RAZORPAY_KEY=
RAZORPAY_SECRET=
STRIPE_KEY=
STRIPE_SECRET=
PAYPAL_CLIENT_ID=
```

### Routes
```
routes/ofa.php
```

### Database
```
database/migrations/ (5 files)
database/seeders/
```

---

## 🌐 ACCESS POINTS

### Admin Dashboard
```
https://your-panel.com/admin/ofa
```

### User Area
```
https://your-panel.com/ofa/
```

### API Endpoints
```
https://your-panel.com/admin/ofa/api/
https://your-panel.com/ofa/api/
```

---

## 🚀 DEPLOYMENT CHECKLIST

Before going live:

- [ ] PHP 8.0+ installed
- [ ] Run `ofa:install` command
- [ ] Create admin user
- [ ] Build frontend assets (`npm run build`)
- [ ] Test admin dashboard access
- [ ] Configure .env with API keys
- [ ] Test theme switching
- [ ] Create test server plan
- [ ] Test Pterodactyl integration
- [ ] Configure payment gateway
- [ ] Test payment flow
- [ ] Set up SSL/HTTPS
- [ ] Configure backups
- [ ] Set up monitoring
- [ ] Create support ticket
- [ ] Document setup for team

---

## 🆘 COMMON COMMANDS

### Install OFA
```bash
php artisan ofa:install
```

### View Logs
```bash
tail -f storage/logs/laravel.log
```

### Clear Cache
```bash
php artisan cache:clear
php artisan config:cache
php artisan route:cache
```

### Database Reset (Development Only)
```bash
php artisan migrate:fresh --seed
```

### Build Assets
```bash
npm run build
npm run dev
```

### Create Admin User Manually
```bash
php artisan tinker
> $user = new \App\Models\User();
> $user->email = 'admin@example.com';
> $user->root_admin = true;
> $user->password = bcrypt('password');
> $user->save();
```

---

## 🔐 SECURITY TIPS

1. **Keep dependencies updated**
   ```bash
   composer update
   npm update
   ```

2. **Set strong APP_KEY**
   ```bash
   php artisan key:generate
   ```

3. **Use HTTPS only**
   ```nginx
   # Redirect HTTP to HTTPS
   server {
       listen 80;
       return 301 https://$server_name$request_uri;
   }
   ```

4. **Backup regularly**
   ```bash
   mysqldump pterodactyl > backup.sql
   ```

5. **Monitor logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

---

## 📞 SUPPORT

- **GitHub**: https://github.com/darkcoder194/ofa-panel
- **Issues**: Report on GitHub
- **Documentation**: See DEPLOYMENT_GUIDE.md & FEATURES.md

---

## 📦 PACKAGE INFO

- **Package**: darkcoder194/ofa-panel
- **Version**: v0.0.2
- **Type**: Laravel Package
- **License**: MIT
- **Author**: Dark Coder (Amrit Yadav)

---

**Last Updated**: January 19, 2026
