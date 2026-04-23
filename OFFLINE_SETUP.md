# ChronoGen - Offline & Network Setup Guide

## ✅ Current Status
- **Server**: Running on PHP 8.0.30
- **Port**: 8000
- **Mode**: Demo mode (no database required)
- **Status**: Fully functional and accessible

## 🌐 Access Options

### Local Access
- **URL**: http://localhost:8000/login.php
- **Description**: Access from the same machine
- **Requirements**: None

### Network Access
- **URL**: http://172.27.224.1:8000/login.php
- **Description**: Access from other devices on the same network
- **Requirements**: Devices must be on the same network

## 🚀 Demo Mode Features
The application automatically runs in **Demo Mode** when no database is available:

✅ Full UI and navigation
✅ Sample data auto-populated
✅ Timetable generation and viewing
✅ Teacher and classroom management
✅ Group and subject organization
✅ Real-time notifications (simulated)
✅ Profile management
✅ Chatbot API

## 📦 What's Included (Offline Capable)

### Frontend
- PHP-based web interface (Gentelella Admin theme)
- HTML/CSS/JavaScript
- Real-time dashboard with analytics
- Responsive design

### Backend
- PHP 8.0 server
- Built-in demo data system
- Session-based storage (no external DB required)
- REST APIs

### AI Engine
- Python timetable generator (optional)
- Genetic algorithm for schedule optimization
- Can run locally without internet

## 🔧 Requirements

### Minimum
- PHP 7.4+ (PHP 8.0 recommended)
- Modern web browser
- 50 MB disk space

### Optional
- Python 3.8+ (for AI timetable generation)
- MySQL/MariaDB (to replace demo mode with persistent storage)

## 🛠️ How to Restart Server

### Stop Current Server
```
Stop-Process -Name php -Force
```

### Start Server (Network Accessible)
```
cd "C:\xampp\htdocs\final year project"
C:\xampp\php\php.exe -S 0.0.0.0:8000 -t saoo
```

### Start Server (Local Only)
```
cd "C:\xampp\htdocs\final year project"
C:\xampp\php\php.exe -S localhost:8000 -t saoo
```

## 📋 Demo Data
The system automatically creates demo data on first access:
- 5 Teachers
- 3 Classrooms
- 4 Subjects
- 2 Groups
- Sample timetable

Click "Populate Demo Data" button on the dashboard to refresh demo data.

## 🔌 No External Dependencies
- ✅ No internet connection required
- ✅ No external APIs needed
- ✅ No cloud services required
- ✅ Works completely offline
- ✅ All data stored in session/demo system

## 📱 Browser Compatibility
- Chrome/Chromium (recommended)
- Firefox
- Safari
- Edge
- Any modern browser with JavaScript support

## 📊 Performance
- Page load time: < 1 second (local network)
- Timetable generation: < 30 seconds
- Database-free operation for demo

## 🔐 Login Credentials (Demo Mode)

The application uses role-based authentication. Use any of these formats:

### Student Access
- Username: `STUDENT@1001` (or any number)
- Password: Any value (not validated in demo mode)
- Access: View your timetable

### Faculty Access  
- Username: `STAFF@2001` (or any number)
- Password: Any value (not validated in demo mode)
- Access: View assigned schedules

### Admin Access
- Username: `admin_@admin` (or any text with underscore and @)
- Password: Any value (not validated in demo mode)
- Access: Full dashboard and management

## 🔐 Security Notes for Offline Use
- No real authentication (demo mode)
- Session data not persistent across browser restarts
- All data is demo/sample data
- Safe for testing and development

## 📞 Support
For detailed project information, see README.md in the project root.

---
**Generated**: April 22, 2026
**Server IP**: 172.27.224.1:8000
**Local Access**: localhost:8000
