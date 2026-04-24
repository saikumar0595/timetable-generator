@echo off
REM Alert Daemon Starter - Windows
REM This script starts the alert daemon as a background process

setlocal enabledelayedexpansion

set PHP_PATH=C:\xampp\php\php.exe
set ALERT_DAEMON=C:\xampp\htdocs\final year project\saoo\alert_daemon.php
set LOG_FILE=C:\xampp\htdocs\final year project\logs\daemon.log

REM Create logs directory if it doesn't exist
if not exist "C:\xampp\htdocs\final year project\logs" (
    mkdir "C:\xampp\htdocs\final year project\logs"
)

echo.
echo ========================================
echo   Alert Daemon Starter
echo ========================================
echo.

REM Check if PHP is available
if not exist "%PHP_PATH%" (
    echo [ERROR] PHP not found at: %PHP_PATH%
    echo Please update the PHP_PATH in this script
    pause
    exit /b 1
)

echo [INFO] PHP Location: %PHP_PATH%
echo [INFO] Daemon Script: %ALERT_DAEMON%
echo [INFO] Log File: %LOG_FILE%
echo.

REM Start the daemon in a hidden window
echo [INFO] Starting alert daemon...
start /B %PHP_PATH% "%ALERT_DAEMON%" >> "%LOG_FILE%" 2>&1

REM Give it a moment to start
timeout /T 2 /NOBREAK

REM Check if process is running
tasklist | find /I "php.exe" >nul
if errorlevel 1 (
    echo [ERROR] Failed to start daemon
    echo Check the log file: %LOG_FILE%
    pause
    exit /b 1
)

echo [SUCCESS] Alert daemon started successfully!
echo.
echo [INFO] The daemon will monitor for classes ending soon
echo [INFO] Alerts will be sent 5 minutes before class ends
echo.
echo [INFO] Log file: %LOG_FILE%
echo [INFO] To stop the daemon, close the PHP process or use Task Manager
echo.
pause
