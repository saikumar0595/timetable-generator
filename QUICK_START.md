# ChronoGen - Quick Start Guide

## ✅ Status: Fully Operational

The ChronoGen timetable management system is running and ready to use!

---

## 🚀 How to Access

### Option 1: Local Access (Same Computer)
Open your browser and visit:
```
http://localhost:8000/login.php
```

### Option 2: Network Access (Other Devices)
From any device on the same network, visit:
```
http://172.27.224.1:8000/login.php
```

### Option 3: Status Dashboard
See system status:
```
http://localhost:8000/status.html
```

---

## 🔐 Login Credentials

The system uses role-based demo credentials (no actual password validation):

### 👨‍💼 Administrator
- **Username:** `admin_@admin`
- **Password:** Any value (e.g., "123")
- **Access:** Full dashboard, manage all data, analytics

### 👨‍🎓 Student
- **Username:** `STUDENT@1001`
- **Password:** Any value (e.g., "123")
- **Access:** View personal timetable, browse schedules

### 👨‍🏫 Faculty
- **Username:** `STAFF@2001`
- **Password:** Any value (e.g., "123")
- **Access:** View assigned classes, manage schedules

---

## 📋 What You Can Do

Once logged in, you have access to:

- ✅ **Timetable Management** - View and manage class schedules
- ✅ **Teacher Directory** - Browse 124 university faculty members
- ✅ **Classroom Allocation** - Manage 9 classrooms across campus
- ✅ **Subject Management** - Organize 50+ courses
- ✅ **Group Management** - Create and manage student groups
- ✅ **Analytics Dashboard** - Real-time statistics and reports
- ✅ **Notification System** - Automated alerts for schedule changes
- ✅ **AI Timetable Generator** - Use genetic algorithms to optimize schedules

---

## 🎯 Getting Started Steps

1. **Open Login Page**
   - Go to: http://localhost:8000/login.php

2. **Choose a Role**
   - Admin: `admin_@admin` → Enter any password → Click "Verify & Enter"
   - OR Student: `STUDENT@1001` → Enter any password → Click "Verify & Enter"
   - OR Faculty: `STAFF@2001` → Enter any password → Click "Verify & Enter"

3. **Explore the Dashboard**
   - Dashboard shows statistics and recent activity
   - Use sidebar menu to navigate

4. **Try Key Features**
   - View → **Manage Teachers** to see all faculty
   - View → **Manage Subjects** to see all courses
   - View → **View Timetable** to see schedules
   - Admin → **Populate Demo Data** to refresh sample data

---

## 💡 Demo Features

The application comes with pre-loaded demo data:
- **124 Teachers** across 9 departments
- **50+ Subjects** across all academic years
- **9 Classrooms** with different capacities
- **Sample Schedules** for the entire week
- **Real-time Analytics** showing utilization rates

No database setup needed - everything works immediately!

---

## 🌐 Offline Access

ChronoGen requires **NO internet connection**:
- ✅ Server runs completely offline
- ✅ No external API calls
- ✅ No cloud dependencies
- ✅ All data is local
- ✅ Works on any network or standalone

---

## 🆘 Troubleshooting

### "Page won't load"
- Check the server is running on port 8000
- Try: http://localhost:8000/status.html
- Ensure no other application is using port 8000

### "Login page appears but form doesn't work"
- Make sure you're using the correct username format:
  - Admin: `admin_@admin` (contains underscore and @)
  - Student: `STUDENT@` followed by numbers
  - Faculty: `STAFF@` followed by numbers

### "Demo data not showing"
- Click "Populate Demo Data" in the dashboard
- Or refresh the page and log in again

### "Styles look broken"
- Ensure JavaScript is enabled in your browser
- Clear browser cache and refresh (Ctrl+F5 or Cmd+Shift+R)

---

## 📱 Browser Compatibility

Works with any modern browser:
- ✅ Chrome/Chromium
- ✅ Firefox
- ✅ Safari
- ✅ Edge
- ✅ Opera

---

## 🔧 Server Management

### To Stop the Server
```powershell
Stop-Process -Name php -Force
```

### To Restart the Server
```powershell
cd "C:\xampp\htdocs\final year project"
C:\xampp\php\php.exe -S 0.0.0.0:8000 -t saoo
```

### To Change Port (e.g., to 9000)
```powershell
cd "C:\xampp\htdocs\final year project"
C:\xampp\php\php.exe -S 0.0.0.0:9000 -t saoo
```

---

## 📚 Documentation

For more detailed information, see:
- **OFFLINE_SETUP.md** - Offline access and feature details
- **DEPLOYMENT_SUMMARY.md** - Deployment and server details
- **README.md** - Project overview and architecture

---

## ✨ Key Technologies

- **Frontend:** PHP 8.0, HTML5, CSS3, JavaScript (Tailwind CSS)
- **UI Framework:** Gentelella Admin Dashboard
- **Algorithm:** Genetic Algorithm (1+1 Evolutionary Strategy)
- **Demo Data:** Audisankara University institutional dataset

---

**Last Updated:** April 22, 2026
**Status:** ✅ Production Ready (Demo Mode)
**Server:** Running on PHP 8.0.30
**Port:** 8000
**Accessibility:** Local + Network
