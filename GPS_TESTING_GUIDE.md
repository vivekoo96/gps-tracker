# 🔬 GPS Server R&D Testing Guide

## 🚀 Quick Start Testing

### 1. **Check Deployment Readiness**
```bash
php artisan gps:check-deployment
```

### 2. **Start Test Server (Local)**
```bash
# Terminal 1: Start GPS server with debugging
php artisan gps:test-server 5023 --debug --log-raw

# Terminal 2: Start additional ports
php artisan gps:test-server 8082 --debug &
php artisan gps:test-server 5027 --debug &
php artisan gps:test-server 6001 --debug &
```

### 3. **Test Socket Connection**
```bash
# Test local connection
php artisan gps:test-connection localhost 5023

# Test remote server
php artisan gps:test-connection your-domain.com 5023
```

### 4. **Simulate GPS Device**
```bash
# Simulate TK103 device
php artisan gps:simulate localhost 8082 --device=TK103 --imei=123456789012345 --interval=5 --count=20

# Simulate GT06N device  
php artisan gps:simulate localhost 5023 --device=GT06N --imei=987654321098765 --interval=10 --count=10

# Simulate to remote server
php artisan gps:simulate your-domain.com 5023 --device=GT06N --imei=555666777888999
```

## 🧪 Testing Scenarios

### **Scenario 1: Local Development**
```bash
# 1. Start local server
php artisan gps:test-server 5023 --debug

# 2. Test with simulator
php artisan gps:simulate localhost 5023 --device=GT06N --count=5

# 3. Check database
php artisan tinker
>>> App\Models\GpsData::latest()->get()
```

### **Scenario 2: Production Server**
```bash
# 1. Check server readiness
php artisan gps:check-deployment

# 2. Test connection to your server
php artisan gps:test-connection your-domain.com 5023

# 3. Start production servers
nohup php artisan gps:server 5023 > gps-5023.log 2>&1 &
nohup php artisan gps:server 8082 > gps-8082.log 2>&1 &
```

### **Scenario 3: Real GPS Device Testing**
```bash
# 1. Start server with full logging
php artisan gps:test-server 5023 --debug --log-raw

# 2. Configure your GPS device via SMS:
# For GT06N: SERVER,your-domain.com,5023,0#
# For TK103: adminip123456 your-domain.com 5023

# 3. Monitor incoming data in real-time
tail -f storage/logs/laravel.log
```

## 📊 Monitoring & Debugging

### **Real-time Monitoring**
```bash
# Monitor server logs
tail -f storage/logs/laravel.log

# Monitor system resources
htop

# Check network connections
netstat -tlnp | grep :5023
```

### **Database Inspection**
```bash
php artisan tinker

# Check devices
>>> App\Models\Device::all()

# Check latest GPS data
>>> App\Models\GpsData::with('device')->latest()->take(10)->get()

# Check specific device data
>>> App\Models\GpsData::where('device_id', 1)->latest()->first()
```

## 🔧 Troubleshooting

### **Connection Issues**
```bash
# Check if port is open
telnet your-domain.com 5023

# Check firewall
sudo ufw status
sudo ufw allow 5023

# Check if server is running
ps aux | grep "gps:server"
```

### **Data Parsing Issues**
```bash
# Start server with full debugging
php artisan gps:test-server 5023 --debug --log-raw

# Send test data manually
echo "test data" | nc localhost 5023
```

### **Performance Testing**
```bash
# Simulate multiple devices
for i in {1..10}; do
    php artisan gps:simulate localhost 5023 --imei=12345678901234$i --count=100 &
done
```

## 🌐 Production Deployment

### **1. Server Setup**
```bash
# Install dependencies
composer install --no-dev --optimize-autoloader

# Set up environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate --force

# Set permissions
chmod -R 755 storage bootstrap/cache
```

### **2. Start GPS Servers**
```bash
# Using supervisor (recommended)
sudo apt install supervisor

# Create supervisor config for each port
sudo nano /etc/supervisor/conf.d/gps-server-5023.conf
```

**Supervisor Config Example:**
```ini
[program:gps-server-5023]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/app/artisan gps:server 5023
directory=/path/to/your/app
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/gps-server-5023.log
```

### **3. Firewall Configuration**
```bash
# Ubuntu/Debian
sudo ufw allow 5023
sudo ufw allow 8082
sudo ufw allow 5027
sudo ufw allow 6001

# CentOS/RHEL
sudo firewall-cmd --permanent --add-port=5023/tcp
sudo firewall-cmd --reload
```

### **4. SSL/TLS (Optional)**
```bash
# For secure GPS connections (if supported by device)
sudo certbot --nginx -d gps.yourdomain.com
```

## 📱 GPS Device Configuration

### **Common SMS Commands**

**GT06N/Concox Devices:**
```sms
SERVER,your-domain.com,5023,0#
TIMER,60#
GPSON#
```

**TK103 Series:**
```sms
adminip123456 your-domain.com 8082
fix060s***n123456
```

**Teltonika Devices:**
Use Teltonika Configurator software with:
- Server: your-domain.com
- Port: 5027
- Protocol: TCP

## 🎯 Success Indicators

### **✅ Everything Working:**
- ✅ `gps:check-deployment` passes all tests
- ✅ `gps:test-connection` succeeds
- ✅ GPS simulator connects and sends data
- ✅ Real GPS device appears in database
- ✅ Location data is being saved correctly

### **📊 Performance Metrics:**
- Connection time: < 5 seconds
- Data processing: < 100ms per message
- Memory usage: < 50MB per server process
- CPU usage: < 10% under normal load

## 🆘 Support Commands

```bash
# Get server status
php artisan gps:status

# Clear logs
php artisan log:clear

# Restart all GPS servers
php artisan gps:restart

# Export GPS data
php artisan gps:export --device=123456789012345 --from=2024-01-01
```

---

**🎉 Your GPS tracking server is now ready for production!**

For issues or questions, check the logs and use the debugging tools provided.
