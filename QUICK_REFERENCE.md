# 🚀 GPS Tracker - Quick Reference Guide

## 📋 **Daily Operations**

### **🔗 Important URLs**
```
Main Dashboard:     /dashboard
GPS Dashboard:      /admin/gps/dashboard
Device Management:  /admin/devices
User Management:    /admin/users
User Roles:         /admin/users/roles
Add Test Data:      /admin/gps/add-test-data
```

### **⚡ Quick Commands**
```bash
# Start GPS server
php artisan gps:test-server 5023 --debug

# Add test GPS data
php artisan gps:add-test-data

# Check system status
php artisan gps:check-deployment

# Simulate GPS device
php artisan gps:simulate localhost 5023 --device=GT06N --count=5
```

## 🎯 **Current System Status**
- ✅ **Users**: 2 registered
- ✅ **Devices**: 6 configured
- ✅ **GPS Data**: 40 location points
- ✅ **Roles**: admin, user, manager
- ✅ **All Routes**: Working properly

## 🔧 **Common Tasks**

### **Add New GPS Device**
1. Go to `/admin/devices/create`
2. Fill device information
3. Configure GPS settings
4. Save and test

### **View Live GPS Tracking**
1. Go to `/admin/gps/dashboard`
2. See all devices on map
3. Click device for details
4. View real-time updates

### **Manage Users**
1. Go to `/admin/users`
2. Add/Edit/Delete users
3. Assign roles (admin/user/manager)
4. Manage permissions

### **Add Test Data**
1. Click "🧪 Test Data" in sidebar
2. Or visit `/admin/gps/add-test-data`
3. Sample data will be added automatically

## 🚨 **Troubleshooting**

### **GPS Not Showing**
- Check if GPS server is running
- Verify device configuration
- Add test data to verify system

### **Permission Errors**
- Check user roles
- Verify admin access
- Clear cache if needed

### **Map Not Loading**
- Check internet connection
- Verify GPS data exists
- Check browser console

## 📱 **GPS Device Setup**

### **GT06N Devices**
SMS: `SERVER,your-domain.com,5023,0#`

### **TK103 Devices**
SMS: `adminip123456 your-domain.com 8082`

## 🎉 **System Ready!**
Everything is working and tested. Ready for production use!
