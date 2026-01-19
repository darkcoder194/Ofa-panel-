#!/bin/bash

#############################################
# OFA PANEL - INSTALLATION SCRIPT
# Usage: bash install.sh
#############################################

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Functions
print_header() {
    echo -e "\n${BLUE}═══════════════════════════════════════════════════${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}═══════════════════════════════════════════════════${NC}\n"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Check if running as root in production
if [ "$EUID" -ne 0 ]; then
   print_warning "Not running as root. Some operations may fail."
fi

# Start installation
print_header "🚀 OFA PANEL INSTALLATION WIZARD"

# Step 1: Check Prerequisites
print_info "Checking prerequisites..."

# Check PHP
if ! command -v php &> /dev/null; then
    print_error "PHP is not installed"
    exit 1
fi
PHP_VERSION=$(php -v | head -n 1 | grep -oE '[0-9]+\.[0-9]+')
print_success "PHP $PHP_VERSION found"

# Check Composer
if ! command -v composer &> /dev/null; then
    print_error "Composer is not installed"
    exit 1
fi
print_success "Composer found"

# Check Node.js
if ! command -v node &> /dev/null; then
    print_warning "Node.js not found. You'll need it for building assets."
else
    NODE_VERSION=$(node -v)
    print_success "Node.js $NODE_VERSION found"
fi

# Check npm
if ! command -v npm &> /dev/null; then
    print_warning "npm not found. You'll need it for building assets."
else
    NPM_VERSION=$(npm -v)
    print_success "npm $NPM_VERSION found"
fi

# Check MySQL/MariaDB
if ! command -v mysql &> /dev/null; then
    print_warning "MySQL/MariaDB client not found (optional)"
else
    print_success "MySQL/MariaDB client found"
fi

# Check Redis
if command -v redis-cli &> /dev/null; then
    print_success "Redis found (highly recommended)"
else
    print_warning "Redis not found (optional but recommended)"
fi

print_info "\n"

# Step 2: Check Pterodactyl Installation
print_info "Checking Pterodactyl installation..."

if [ ! -f "artisan" ]; then
    print_error "Not in Pterodactyl directory. Please run this from /var/www/pterodactyl"
    exit 1
fi

if ! grep -q "Pterodactyl" composer.json; then
    print_warning "This doesn't appear to be a Pterodactyl installation"
fi

print_success "Pterodactyl Panel detected"

# Step 3: Get installation method
print_info ""
print_info "Select installation method:"
echo "1) Composer (Recommended)"
echo "2) Manual"
echo "3) Exit"
read -p "Enter your choice (1-3): " METHOD

case $METHOD in
    1)
        print_info "Installing via Composer..."
        composer require darkcoder194/ofa-panel
        ;;
    2)
        print_info "Manual installation selected. You'll need to copy files manually."
        print_info "After copying files, run: composer dump-autoload"
        ;;
    3)
        print_info "Installation cancelled."
        exit 0
        ;;
    *)
        print_error "Invalid option"
        exit 1
        ;;
esac

print_success "Composer requirements added"

# Step 4: Publish Assets
print_info ""
print_info "Publishing OFA assets..."

php artisan vendor:publish --provider="DarkCoder\Ofa\OfaServiceProvider" --tag=config --force
php artisan vendor:publish --provider="DarkCoder\Ofa\OfaServiceProvider" --tag=ofa-assets --force

print_success "Assets published"

# Step 5: Run Migrations
print_info ""
print_info "Running database migrations..."

php artisan migrate --force

print_success "Database migrations completed"

# Step 6: Seed Data
print_info ""
print_info "Seeding theme data..."

php artisan db:seed --class="DarkCoder\Ofa\Database\Seeders\OfaThemeSeeder"

print_success "Theme data seeded"

# Step 7: Build Assets
print_info ""
if command -v npm &> /dev/null; then
    read -p "Build frontend assets now? (y/n): " BUILD_ASSETS
    if [ "$BUILD_ASSETS" = "y" ] || [ "$BUILD_ASSETS" = "Y" ]; then
        print_info "Installing npm dependencies..."
        npm install
        
        print_info "Building assets..."
        npm run build
        
        print_success "Assets built successfully"
    fi
else
    print_warning "npm not found. Skipping asset build."
    print_info "Build manually later with: npm install && npm run build"
fi

# Step 8: Clear Cache
print_info ""
print_info "Clearing application cache..."

php artisan cache:clear
php artisan config:cache

print_success "Cache cleared"

# Step 9: Set Permissions
print_info ""
print_info "Setting correct permissions..."

if [ "$EUID" -eq 0 ]; then
    chown -R www-data:www-data . > /dev/null 2>&1
    chmod -R 755 . > /dev/null 2>&1
    chmod -R 755 storage > /dev/null 2>&1
    chmod -R 755 bootstrap/cache > /dev/null 2>&1
    print_success "Permissions set"
else
    print_warning "Run the following with sudo to set permissions:"
    echo "    sudo chown -R www-data:www-data $(pwd)"
    echo "    sudo chmod -R 755 $(pwd)/storage"
    echo "    sudo chmod -R 755 $(pwd)/bootstrap/cache"
fi

# Completion
print_header "✨ INSTALLATION COMPLETE!"

echo -e "${GREEN}OFA Panel has been successfully installed!${NC}"
echo ""
echo -e "${BLUE}📍 Next Steps:${NC}"
echo "1. Access admin dashboard: https://your-panel.com/admin/ofa"
echo "2. Log in as a Root Admin user"
echo "3. Go to Theme Manager to customize the panel"
echo "4. Enable features in config/ofa.php as needed"
echo ""
echo -e "${BLUE}📚 Documentation:${NC}"
echo "- Full guide: $(pwd)/INSTALL.md"
echo "- Features: $(pwd)/FEATURES.md"
echo "- Quick start: $(pwd)/QUICK_START.md"
echo ""
echo -e "${BLUE}🔗 Resources:${NC}"
echo "- GitHub: https://github.com/darkcoder194/ofa-panel"
echo "- Pterodactyl: https://pterodactylproject.org"
echo ""
echo -e "${GREEN}Happy configuring! 🎉${NC}\n"
