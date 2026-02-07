# ⚡ GPS Tracker - Quick Start Guide

> Get up and running in **5 minutes**!

---

## 🎯 Prerequisites

- ✅ PHP 8.2+
- ✅ Composer
- ✅ Node.js & NPM
- ✅ Laravel Herd (Windows) OR PHP built-in server

---

## 🚀 Quick Setup (Development)

### 1. Install Dependencies (2 minutes)

```bash
# Navigate to project
cd c:\Users\LENOVO\Herd\gps

# Install PHP packages
composer install

# Install Node packages
npm install
```

### 2. Configure Environment (1 minute)

```bash
# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate
```

### 3. Setup Database (1 minute)

```bash
# Create SQLite database
type nul > database\database.sqlite

# Run migrations and seed data
php artisan migrate --seed
```

**This creates:**
- Admin user: `admin@example.com` / `password`
- Demo user: `user@example.com` / `password`
- 6 sample GPS devices
- 40+ location records

### 4. Start the Application (1 minute)

```bash
# Option 1: Use Composer dev script (recommended)
composer run dev

# Option 2: Manual start
# Terminal 1:
php artisan serve

# Terminal 2:
npm run dev

# Terminal 3:
php artisan gps:tcp-server --port=5010
```

### 5. Access the System

🌐 **Open browser:** http://localhost:8000

🔐 **Login:**
- Email: `admin@example.com`
- Password: `password`

---

## 🧪 Test GPS Functionality

### Start GPS Server

```bash
# Terminal 1: GPS Server
php artisan gps:tcp-server --port=5010
```

### Simulate GPS Device

```bash
# Terminal 2: Simulator
php artisan gps:simulate localhost 5010 --device=GT06N --count=5
```

### View on Dashboard

1. Go to: http://localhost:8000/admin/gps/dashboard
2. See devices on the map
3. Click device for details

---

## 📍 Key URLs

| Page | URL |
|------|-----|
| **Dashboard** | http://localhost:8000/dashboard |
| **GPS Dashboard** | http://localhost:8000/admin/gps/dashboard |
| **Devices** | http://localhost:8000/admin/devices |
| **Geofences** | http://localhost:8000/admin/geofences |
| **Users** | http://localhost:8000/admin/users |
| **Live Tracking** | http://localhost:8000/tracking/live |

---

## 🔧 Common Commands

```bash
# Clear all caches
php artisan optimize:clear

# Reset database
php artisan migrate:fresh --seed

# Check GPS server status
php artisan gps:check-deployment

# Run tests
php artisan test
```

---

## ⚠️ Troubleshooting

### Port Already in Use

```bash
# Find process using port 5010
netstat -ano | findstr :5010

# Kill process
taskkill /PID <process_id> /F
```

### Database Locked (SQLite)

```bash
# Stop all Laravel processes
# Delete database file
del database\database.sqlite

# Recreate
type nul > database\database.sqlite
php artisan migrate --seed
```

### Assets Not Loading

```bash
# Rebuild assets
npm run build

# Or use dev mode
npm run dev
```

---

## 📚 Next Steps

✅ **Read full documentation:** [COMPLETE_SETUP_GUIDE.md](COMPLETE_SETUP_GUIDE.md)  
✅ **Configure real GPS device:** See GPS device configuration section  
✅ **Set up geofences:** Create monitoring zones  
✅ **Add users:** Manage team access  

---

## 🎉 You're Ready!

Your GPS Tracker is now running locally. Start tracking devices! 🚗📍

For production deployment, see the [Complete Setup Guide](COMPLETE_SETUP_GUIDE.md).
