# 🚀 GPS Server Setup & Device Connection Guide

**Complete step-by-step guide to set up your GPS tracking server and connect real GPS devices**

---

## 📋 Table of Contents

1. [Prerequisites](#prerequisites)
2. [Local Development Setup](#local-development-setup)
3. [Production Server Setup](#production-server-setup)
4. [GPS Device Configuration](#gps-device-configuration)
5. [Testing & Verification](#testing--verification)
6. [Troubleshooting](#troubleshooting)

---

## 🔧 Prerequisites

### **Required Software**
- ✅ PHP 8.2 or higher
- ✅ MySQL 8.0 or higher
- ✅ Composer
- ✅ Node.js & NPM
- ✅ Web server (Apache/Nginx) for web interface

### **Required PHP Extensions**
```bash
php -m | grep -E 'sockets|pcntl|posix|pdo_mysql'
```

Should show:
- ✅ sockets
- ✅ pcntl
- ✅ posix
- ✅ pdo_mysql

### **Install Missing Extensions (if needed)**

**On Ubuntu/Debian:**
```bash
sudo apt update
sudo apt install php8.2-sockets php8.2-mysql php8.2-pcntl
```

**On macOS (with Homebrew):**
```bash
brew install php@8.2
# Sockets and pcntl are usually included
```

---

## 💻 Local Development Setup

### **Step 1: Clone & Install Dependencies**

```bash
# Navigate to your project directory
cd /Users/vivekpatel/Herd/gps-tracker

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Build assets
npm run build
```

### **Step 2: Configure Environment**

```bash
# Copy environment file (if not already done)
cp .env.example .env

# Generate application key
php artisan key:generate
```

**Edit `.env` file:**
```env
APP_NAME="GPS Tracker"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gps_tracker
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### **Step 3: Database Setup**

```bash
# Create database
mysql -u root -p
```

In MySQL:
```sql
CREATE DATABASE gps_tracker;
EXIT;
```

Back in terminal:
```bash
# Run migrations
php artisan migrate

# (Optional) Seed with test data
php artisan db:seed
```

### **Step 4: Check System Readiness**

```bash
php artisan gps:check-deployment
```

You should see:
```
✅ PHP Extensions: All required extensions loaded
✅ Database Connection: Database connected and tables exist
✅ Required Directories: All directories accessible
✅ Environment Configuration: Environment configured
✅ ReactPHP Dependencies: ReactPHP installed
✅ All GPS ports available
✅ File permissions OK

🎉 Your server is ready for GPS deployment!
```

### **Step 5: Start GPS Server (Local Testing)**

**Terminal 1 - Start web server:**
```bash
php artisan serve
# Server runs at http://localhost:8000
```

**Terminal 2 - Start GPS server:**
```bash
php artisan gps:test-server 5023 --debug --log-raw
```

You'll see:
```
🚀 GPS R&D Test Server Started
📡 Port: 5023
🐛 Debug Mode: ON
📝 Raw Logging: ON
🔍 Waiting for GPS devices to connect...
```

### **Step 6: Test with Simulator**

**Terminal 3 - Run simulator:**
```bash
# Simulate GT06N device
php artisan gps:simulate localhost 5023 --device=GT06N --imei=123456789012345 --count=10

# Or simulate TK103 device
php artisan gps:simulate localhost 8082 --device=TK103 --imei=987654321098765 --count=5
```

### **Step 7: Verify Data in Database**

```bash
php artisan tinker
```

In tinker:
```php
// Check devices
App\Models\Device::all();

// Check latest GPS data
App\Models\GpsData::latest()->take(10)->get();

// Check specific device with GPS data
App\Models\Device::with('gpsData')->first();
```

Or view in web interface:
- Open browser: `http://localhost:8000`
- Login (if authentication is set up)
- Navigate to GPS Dashboard: `http://localhost:8000/admin/gps/dashboard`

---

## 🌐 Production Server Setup

### **Step 1: Server Requirements**

**Minimum Server Specs:**
- 2 CPU cores
- 2GB RAM
- 20GB disk space
- Ubuntu 20.04+ or similar Linux distribution

### **Step 2: Install Required Software**

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Add Ondrej PHP PPA (Required for PHP 8.2 on older Ubuntu versions)
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.2
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-sockets php8.2-mbstring php8.2-xml php8.2-curl -y

# Install MySQL
sudo apt install mysql-server -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Nginx (or Apache)
sudo apt install nginx -y

# Install Supervisor (for keeping GPS servers running)
sudo apt install supervisor -y
```

### **Step 3: Deploy Application**

```bash
# Create directory
sudo mkdir -p /var/www/gps-tracker
sudo chown $USER:$USER /var/www/gps-tracker

# Upload your code (or clone from git)
cd /var/www/gps-tracker
git clone <your-repo-url> .

# Install dependencies
composer install --no-dev --optimize-autoloader

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### **Step 4: Configure Environment**

```bash
cp .env.example .env
nano .env
```

**Production `.env` settings:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=gps_tracker_prod
DB_USERNAME=gps_user
DB_PASSWORD=secure_password_here
```

```bash
# Generate key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **Step 5: Configure Firewall**

```bash
# Allow HTTP/HTTPS
sudo ufw allow 80
sudo ufw allow 443

# Allow GPS ports
sudo ufw allow 5023/tcp comment "GPS GT06N"
sudo ufw allow 8082/tcp comment "GPS TK103"
sudo ufw allow 5027/tcp comment "GPS Teltonika"
sudo ufw allow 6001/tcp comment "GPS Queclink"

# Enable firewall
sudo ufw enable
```

### **Step 6: Set Up Supervisor (Keep GPS Servers Running)**

Create supervisor configuration files:

**GT06N Server (Port 5023):**
```bash
sudo nano /etc/supervisor/conf.d/gps-server-5023.conf
```

```ini
[program:gps-server-5023]
process_name=%(program_name)s
command=php /var/www/gps-tracker/artisan gps:server 5023
directory=/var/www/gps-tracker
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/supervisor/gps-server-5023.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=3
```

**TK103 Server (Port 8082):**
```bash
sudo nano /etc/supervisor/conf.d/gps-server-8082.conf
```

```ini
[program:gps-server-8082]
process_name=%(program_name)s
command=php /var/www/gps-tracker/artisan gps:server 8082
directory=/var/www/gps-tracker
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/supervisor/gps-server-8082.log
```

**Teltonika Server (Port 5027):**
```bash
sudo nano /etc/supervisor/conf.d/gps-server-5027.conf
```

```ini
[program:gps-server-5027]
process_name=%(program_name)s
command=php /var/www/gps-tracker/artisan gps:server 5027
directory=/var/www/gps-tracker
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/supervisor/gps-server-5027.log
```

**Apply supervisor configuration:**
```bash
# Reload supervisor
sudo supervisorctl reread
sudo supervisorctl update

# Start all GPS servers
sudo supervisorctl start gps-server-5023
sudo supervisorctl start gps-server-8082
sudo supervisorctl start gps-server-5027

# Check status
sudo supervisorctl status
```

You should see:
```
gps-server-5023          RUNNING   pid 12345, uptime 0:00:10
gps-server-8082          RUNNING   pid 12346, uptime 0:00:10
gps-server-5027          RUNNING   pid 12347, uptime 0:00:10
```

### **Step 7: Configure Nginx (Web Interface)**

```bash
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
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
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

# Test configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

### **Step 8: Install SSL Certificate (Optional but Recommended)**

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Get SSL certificate
sudo certbot --nginx -d your-domain.com

# Auto-renewal is set up automatically
```

---

## 📱 GPS Device Configuration

### **Option 1: GT06N / Concox Devices**

**Connection Details:**
- Protocol: GT06N
- Port: 5023
- Connection Type: TCP

**Configure via SMS:**

1. **Set Server Address:**
```sms
SERVER,your-domain.com,5023,0#
```
Or with IP:
```sms
SERVER,123.45.67.89,5023,0#
```

2. **Set Update Interval (60 seconds):**
```sms
TIMER,60#
```

3. **Enable GPS:**
```sms
GPSON#
```

4. **Check Settings:**
```sms
STATUS#
```

**Device will respond with confirmation SMS**

---

### **Option 2: TK103 Series Devices**

**Connection Details:**
- Protocol: TK103
- Port: 8082
- Connection Type: TCP

**Configure via SMS:**

1. **Set Server (replace 123456 with your device password):**
```sms
adminip123456 your-domain.com 8082
```

2. **Set Update Interval:**
```sms
fix060s***n123456
```
(Updates every 60 seconds)

3. **Check Settings:**
```sms
check123456
```

---

### **Option 3: Teltonika Devices**

**Connection Details:**
- Protocol: Teltonika
- Port: 5027
- Connection Type: TCP

**Configure via Teltonika Configurator Software:**

1. Download Teltonika Configurator from official website
2. Connect device via USB or Bluetooth
3. Open configurator and navigate to **GPRS Settings**
4. Set the following:
   - **Server Domain:** `your-domain.com`
   - **Server Port:** `5027`
   - **Protocol:** `TCP`
   - **Data Sending Interval:** `60 seconds`
5. Click **Save to Device**

---

### **Option 4: Queclink Devices**

**Connection Details:**
- Protocol: Queclink
- Port: 6001
- Connection Type: TCP

**Configure via SMS or AT Commands:**

```sms
AT+GTBSI=gv500,1,0,0,1,1,your-domain.com,6001,,,,,,0000$
```

---

## ✅ Testing & Verification

### **Step 1: Test Server Connection**

From your local machine:
```bash
# Test if port is accessible
telnet your-domain.com 5023

# Or use the built-in tester
php artisan gps:test-connection your-domain.com 5023
```

### **Step 2: Monitor Server Logs**

```bash
# Watch GPS server logs
sudo tail -f /var/log/supervisor/gps-server-5023.log

# Watch Laravel logs
tail -f /var/www/gps-tracker/storage/logs/laravel.log

# Watch all GPS servers
sudo tail -f /var/log/supervisor/gps-server-*.log
```

### **Step 3: Check Device Connection**

After configuring your device, within 1-2 minutes you should see:

**In server logs:**
```
🔗 NEW CONNECTION
   From: 123.45.67.89:54321
   
📨 MESSAGE RECEIVED
   Protocol: GT06N
   ✅ PARSED SUCCESSFULLY
   💾 Database: SAVED
```

**In database:**
```bash
php artisan tinker
```

```php
// Check if device appeared
App\Models\Device::latest()->first();

// Check GPS data
App\Models\GpsData::latest()->first();
```

**In web interface:**
- Navigate to: `https://your-domain.com/admin/gps/dashboard`
- You should see your device on the map
- Device status should show "🟢 Online"

### **Step 4: Verify Real-time Tracking**

1. Move the GPS device (or drive with it)
2. Wait 60 seconds (or your configured interval)
3. Refresh the dashboard
4. Device location should update on the map

---

## 🔍 Troubleshooting

### **Problem: Device Not Connecting**

**Check 1: Firewall**
```bash
# Check if port is open
sudo ufw status
sudo netstat -tulpn | grep :5023
```

**Check 2: Server Running**
```bash
sudo supervisorctl status gps-server-5023
```

**Check 3: Device Configuration**
- Send `STATUS#` (GT06N) or `check123456` (TK103) to verify settings
- Make sure server address and port are correct
- Check device has active SIM card with data plan

**Check 4: Network**
```bash
# Try from external network
telnet your-domain.com 5023
```

---

### **Problem: Device Connects but No GPS Data**

**Check 1: GPS Signal**
- Device must be outdoors or near window
- Wait 2-5 minutes for GPS fix
- Check device LED indicators

**Check 2: Server Logs**
```bash
sudo tail -f /var/log/supervisor/gps-server-5023.log
```
Look for parsing errors

**Check 3: Database**
```bash
php artisan tinker
```
```php
// Check if data is being received
App\Models\GpsData::where('device_id', 1)->latest()->take(10)->get();
```

---

### **Problem: Server Crashes or Stops**

**Check supervisor status:**
```bash
sudo supervisorctl status
sudo supervisorctl tail gps-server-5023
```

**Restart GPS servers:**
```bash
sudo supervisorctl restart gps-server-5023
sudo supervisorctl restart gps-server-8082
```

**Check for errors:**
```bash
tail -f /var/www/gps-tracker/storage/logs/laravel.log
```

---

### **Problem: Wrong Coordinates (0.0, 0.0)**

This was fixed in the latest update. If still seeing this:

1. Make sure you pulled the latest code
2. Check device has GPS signal (usually takes 2-5 min for first fix)
3. Verify device is sending location packets (not just login packets)

---

## 📊 Server Management Commands

```bash
# Check server status
sudo supervisorctl status

# Restart all GPS servers
sudo supervisorctl restart all

# Stop GPS servers
sudo supervisorctl stop gps-server-5023

# View logs
sudo supervisorctl tail -f gps-server-5023

# Check deployment readiness
php artisan gps:check-deployment

# Test connection
php artisan gps:test-connection your-domain.com 5023

# Simulate device (testing)
php artisan gps:simulate your-domain.com 5023 --device=GT06N
```

---

## 🎯 Quick Reference

### **Server Ports**
- **5023** - GT06N/Concox devices
- **8082** - TK103 series devices
- **5027** - Teltonika devices
- **6001** - Queclink devices

### **SMS Commands (GT06N)**
- `SERVER,domain.com,5023,0#` - Set server
- `TIMER,60#` - Set 60 second interval
- `GPSON#` - Enable GPS
- `STATUS#` - Check status
- `RESET#` - Reset device

### **SMS Commands (TK103)**
- `adminip123456 domain.com 8082` - Set server
- `fix060s***n123456` - 60 second updates
- `check123456` - Check status

### **Important Logs**
- GPS Servers: `/var/log/supervisor/gps-server-*.log`
- Laravel: `/var/www/gps-tracker/storage/logs/laravel.log`
- Nginx: `/var/log/nginx/error.log`

---

## ✅ Final Checklist

- [ ] PHP 8.2+ installed with required extensions
- [ ] MySQL database created and migrated
- [ ] ReactPHP dependencies installed
- [ ] Firewall configured (ports 5023, 8082, 5027, 6001)
- [ ] GPS servers running via Supervisor
- [ ] Web interface accessible
- [ ] SSL certificate installed (production)
- [ ] GPS device configured with correct server address
- [ ] Device visible in dashboard
- [ ] GPS data updating in real-time

---

## 🎉 Success!

If you completed all steps:
- ✅ Your GPS server is running
- ✅ Devices can connect and send data
- ✅ You can track devices on the map
- ✅ System is production-ready

**Need help?** Check the troubleshooting section or review server logs for errors.

**Happy Tracking! 🚗📍**
