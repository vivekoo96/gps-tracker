# 🚀 GPS Tracker - Complete Setup Guide

> **Last Updated:** February 7, 2026  
> **Version:** 1.0  
> **Laravel:** 12.x | **PHP:** 8.2+ | **Database:** MySQL/SQLite

---

## 📋 Table of Contents

1. [System Requirements](#-system-requirements)
2. [Fresh Installation](#-fresh-installation)
3. [Database Setup](#-database-setup)
4. [GPS Server Configuration](#-gps-server-configuration)
5. [Testing the System](#-testing-the-system)
6. [Production Deployment](#-production-deployment)
7. [Troubleshooting](#-troubleshooting)

---

## 💻 System Requirements

### Minimum Requirements

```
✅ PHP 8.2 or higher
✅ Composer 2.x
✅ Node.js 18+ and NPM
✅ MySQL 8.0+ OR SQLite 3.x
✅ Redis (optional, for caching and real-time features)
✅ 2GB RAM minimum (4GB recommended)
✅ 10GB disk space
```

### Required PHP Extensions

```bash
# Check installed extensions
php -m

# Required extensions:
- PDO
- mbstring
- openssl
- tokenizer
- xml
- ctype
- json
- bcmath
- sockets (for GPS TCP server)
- redis (optional)
```

### Windows-Specific (Laravel Herd)

```
✅ Laravel Herd installed
✅ Windows 10/11
✅ WSL2 (optional, for better performance)
```

---

## 🔧 Fresh Installation

### Step 1: Clone or Download Project

```bash
# If using Git
git clone <your-repository-url> gps-tracker
cd gps-tracker

# OR if you have the project folder
cd c:\Users\LENOVO\Herd\gps
```

### Step 2: Install PHP Dependencies

```bash
# Install all Composer packages
composer install

# This will install:
# - Laravel Framework 12.x
# - Spatie Laravel Permission (role management)
# - ReactPHP (GPS server)
# - Razorpay (payment integration)
# - Redis client
```

**Expected output:**
```
Generating optimized autoload files
> Illuminate\Foundation\ComposerScripts::postAutoloadDump
> @php artisan package:discover --ansi
Discovered Package: laravel/breeze
Discovered Package: laravel/tinker
...
```

### Step 3: Install Node.js Dependencies

```bash
# Install NPM packages
npm install

# This will install:
# - Vite (build tool)
# - Tailwind CSS (styling)
# - Alpine.js (interactivity)
# - Axios (HTTP client)
```

### Step 4: Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 5: Configure `.env` File

Open `.env` and configure:

```env
# Application
APP_NAME="GPS Tracker"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://gps.test  # Or your Herd domain

# Database - Option 1: SQLite (Easiest for development)
DB_CONNECTION=sqlite
# No other DB settings needed for SQLite

# Database - Option 2: MySQL (Production)
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=gps_tracker
# DB_USERNAME=root
# DB_PASSWORD=your_password

# Queue (for background jobs)
QUEUE_CONNECTION=database

# Cache
CACHE_STORE=database

# Session
SESSION_DRIVER=database

# Redis (Optional - for real-time features)
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="noreply@gpstracker.com"
MAIL_FROM_NAME="${APP_NAME}"

# Broadcasting (for real-time updates)
BROADCAST_CONNECTION=log  # Change to 'reverb' for WebSockets
```

---

## 🗄️ Database Setup

### Option 1: SQLite (Recommended for Development)

```bash
# Create SQLite database file
touch database/database.sqlite

# Or on Windows
type nul > database\database.sqlite

# Run migrations
php artisan migrate

# Seed the database with sample data
php artisan db:seed
```

### Option 2: MySQL (Production)

```bash
# 1. Create database
mysql -u root -p
CREATE DATABASE gps_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# 2. Update .env with MySQL credentials
# DB_CONNECTION=mysql
# DB_DATABASE=gps_tracker
# DB_USERNAME=root
# DB_PASSWORD=your_password

# 3. Run migrations
php artisan migrate

# 4. Seed the database
php artisan db:seed
```

### What Gets Seeded?

The database seeder creates:

✅ **Default Users:**
- **Super Admin:** `admin@example.com` / `password`
- **Demo User:** `user@example.com` / `password`

✅ **Roles & Permissions:**
- `super_admin` - Full system access
- `vendor_admin` - Tenant admin access
- `user` - Limited user access

✅ **Sample Devices:** 6 GPS devices with different configurations

✅ **Sample GPS Data:** 40+ location records for testing

✅ **Subscription Plans:** Basic and Pro plans

### Verify Database Setup

```bash
# Check database tables
php artisan tinker
>>> DB::table('users')->count();
>>> DB::table('devices')->count();
>>> DB::table('positions')->count();
>>> exit
```

---

## 📡 GPS Server Configuration

### Available GPS Commands

```bash
# 1. TCP Server (Main Production Server)
php artisan gps:tcp-server --host=0.0.0.0 --port=5010

# 2. Multi-Protocol Server (Test/Development)
php artisan gps:test-server 5023 --debug --log-raw

# 3. Deployment Check
php artisan gps:check-deployment

# 4. GPS Simulator (Testing)
php artisan gps:simulate localhost 5010 --device=GT06N --count=10

# 5. Connection Test
php artisan gps:test-connection localhost 5010
```

### GPS Server Ports

| Protocol | Port | Description |
|----------|------|-------------|
| **GT06/GT06N** | 5010 | Main protocol (Concox devices) |
| **TK103** | 8082 | TK103 series trackers |
| **Teltonika** | 5027 | Teltonika devices |
| **Queclink** | 6001 | Queclink trackers |

### Start GPS Server (Development)

```bash
# Terminal 1: Start the GPS TCP server
php artisan gps:tcp-server --port=5010

# Terminal 2: Start the web application
php artisan serve

# Terminal 3: Build frontend assets
npm run dev
```

### Start GPS Server (Production)

See [Production Deployment](#-production-deployment) section below.

---

## 🧪 Testing the System

### 1. Access the Application

```bash
# Start the development server
php artisan serve

# Open browser
http://localhost:8000
```

**Login with:**
- Email: `admin@example.com`
- Password: `password`

### 2. Test GPS Data Reception

```bash
# Terminal 1: Start GPS server with debug
php artisan gps:tcp-server --port=5010

# Terminal 2: Simulate GPS device
php artisan gps:simulate localhost 5010 --device=GT06N --count=5
```

**Expected output:**
```
Simulating GPS Connection for IMEI: 869727072514837
Connecting to localhost:5010...
Sending Login Packet...
SUCCESS: Server accepted the login!
Sending Location Packet...
Location sent: 17.444, 78.333
```

### 3. Verify GPS Data in Dashboard

1. Navigate to: `http://localhost:8000/admin/gps/dashboard`
2. You should see devices on the map
3. Click on a device to see details
4. Check device history: `/admin/gps/device/{id}/history`

### 4. Test Real GPS Device

```bash
# 1. Start GPS server on public IP/domain
php artisan gps:tcp-server --host=0.0.0.0 --port=5010

# 2. Configure your GPS device via SMS:
SERVER,your-domain.com,5010,0#
TIMER,60#
GPSON#

# 3. Monitor server logs
tail -f storage/logs/laravel.log
```

### 5. Run Automated Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --filter=GpsDataTest
```

---

## 🌐 Production Deployment

### Prerequisites

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring \
    php8.2-curl php8.2-zip php8.2-bcmath php8.2-sockets nginx mysql-server \
    redis-server supervisor git composer
```

### Step 1: Deploy Application

```bash
# 1. Clone repository
cd /var/www
git clone <your-repo> gps-tracker
cd gps-tracker

# 2. Install dependencies (production mode)
composer install --no-dev --optimize-autoloader
npm install
npm run build

# 3. Set permissions
sudo chown -R www-data:www-data /var/www/gps-tracker
sudo chmod -R 755 /var/www/gps-tracker/storage
sudo chmod -R 755 /var/www/gps-tracker/bootstrap/cache

# 4. Configure environment
cp .env.example .env
php artisan key:generate
nano .env  # Edit with production settings
```

### Step 2: Production `.env` Configuration

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=gps_tracker
DB_USERNAME=gps_user
DB_PASSWORD=secure_password

QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Step 3: Database Setup (Production)

```bash
# Create database
mysql -u root -p
CREATE DATABASE gps_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'gps_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON gps_tracker.* TO 'gps_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Run migrations
php artisan migrate --force
php artisan db:seed --force

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 4: Configure Nginx

```bash
# Create Nginx config
sudo nano /etc/nginx/sites-available/gps-tracker
```

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/gps-tracker/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/gps-tracker /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Step 5: Configure GPS Server with Supervisor

```bash
# Create supervisor config
sudo nano /etc/supervisor/conf.d/gps-server.conf
```

```ini
[program:gps-tcp-5010]
process_name=%(program_name)s
command=php /var/www/gps-tracker/artisan gps:tcp-server --port=5010
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/gps-tracker/storage/logs/gps-server-5010.log
stopwaitsecs=3600

[program:queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/gps-tracker/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/gps-tracker/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Start services
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
sudo supervisorctl status
```

### Step 6: Configure Firewall

```bash
# Allow necessary ports
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 5010/tcp  # GPS Server
sudo ufw allow 8082/tcp  # TK103 (if needed)
sudo ufw allow 5027/tcp  # Teltonika (if needed)
sudo ufw enable
sudo ufw status
```

### Step 7: SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Get certificate
sudo certbot --nginx -d your-domain.com

# Auto-renewal is configured automatically
sudo certbot renew --dry-run
```

---

## 🔧 Troubleshooting

### GPS Server Not Starting

```bash
# Check if port is already in use
sudo netstat -tulpn | grep 5010

# Kill existing process
sudo kill -9 <PID>

# Check supervisor logs
sudo tail -f /var/www/gps-tracker/storage/logs/gps-server-5010.log
```

### Devices Not Connecting

```bash
# 1. Verify firewall
sudo ufw status

# 2. Test port accessibility
telnet your-domain.com 5010

# 3. Check server logs
tail -f storage/logs/laravel.log

# 4. Verify device configuration
# Send SMS to device: STATUS#
```

### Database Connection Issues

```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check MySQL status
sudo systemctl status mysql

# Reset database
php artisan migrate:fresh --seed
```

### Permission Errors

```bash
# Fix storage permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Map Not Loading

```bash
# 1. Check if GPS data exists
php artisan tinker
>>> App\Models\Position::count();

# 2. Clear browser cache
# 3. Check browser console for JavaScript errors
# 4. Verify internet connection (for map tiles)
```

---

## 📞 Quick Reference Commands

### Development

```bash
# Start everything
composer run dev  # Starts server, queue, logs, and vite

# Or manually:
php artisan serve
npm run dev
php artisan queue:work
php artisan gps:tcp-server --port=5010
```

### Database

```bash
# Fresh migration
php artisan migrate:fresh --seed

# Rollback
php artisan migrate:rollback

# Check status
php artisan migrate:status
```

### GPS Testing

```bash
# Simulate device
php artisan gps:simulate localhost 5010 --count=10

# Check deployment
php artisan gps:check-deployment

# Test connection
php artisan gps:test-connection your-domain.com 5010
```

### Maintenance

```bash
# Clear all caches
php artisan optimize:clear

# Optimize for production
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Check logs
tail -f storage/logs/laravel.log
```

---

## ✅ Post-Installation Checklist

- [ ] Application accessible via browser
- [ ] Can login with default credentials
- [ ] Database has sample data
- [ ] GPS server starts without errors
- [ ] GPS simulator successfully connects
- [ ] Devices visible on map dashboard
- [ ] Real-time updates working
- [ ] Geofence alerts configured
- [ ] Email notifications working (if configured)
- [ ] SSL certificate installed (production)
- [ ] Firewall configured correctly
- [ ] Supervisor services running
- [ ] Backups configured

---

## 🎉 Success!

Your GPS Tracker system is now fully set up and ready to use!

**Next Steps:**
1. Configure your real GPS devices
2. Set up geofences for monitoring
3. Configure notification preferences
4. Add users and assign roles
5. Monitor system performance

**Support:**
- Check logs: `storage/logs/laravel.log`
- Run diagnostics: `php artisan gps:check-deployment`
- Review documentation in project root

---

**Version:** 1.0 | **Last Updated:** February 7, 2026
