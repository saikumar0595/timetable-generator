@echo off
setlocal enabledelayedexpansion

echo.
echo ========================================
echo    CHRONOGEN - TIMETABLE GENERATOR
echo ========================================
echo.

REM Check if PHP exists
if not exist "C:\xampp\php\php.exe" (
    echo ERROR: PHP not found at C:\xampp\php\php.exe
    echo Please install XAMPP first
    pause
    exit /b 1
)

REM Change to project directory
cd /d "C:\xampp\htdocs\final year project\saoo"

if errorlevel 1 (
    echo ERROR: Could not change to project directory
    pause
    exit /b 1
)

echo.
echo ✅ Starting PHP Built-in Server...
echo.
echo 📌 Access the application at:
echo    http://localhost:8000/login.php
echo.
echo 🛑 Press Ctrl+C to stop the server
echo.

REM Start PHP server
"C:\xampp\php\php.exe" -S localhost:8000 -t "C:\xampp\htdocs\final year project\saoo"

pause
