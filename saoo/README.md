# ChronoGen: Intelligent Timetable Generator

## Overview
A web-based **Automatic Timetable Management System** designed to solve the complex "Course Timetabling Problem." It uses a **(1+1) Evolutionary Strategy** with **Simulated Hardening** to generate conflict-free schedules in seconds.

## Key Features
*   **AI Scheduling Engine**: Python-based genetic algorithm that optimizes constraints.
*   **Adaptive Mutation**: Automatically adjusts algorithm "temperature" for faster convergence.
*   **Modern Dashboard**: Glassmorphism UI with real-time **Chart.js** workload analytics.
*   **One-Click Demo Mode**: Runs perfectly without a database (using session storage).
*   **University Data Pack**: Instantly load 10+ famous scientists and courses for impressive demos.
*   **Print-Ready Output**: Generate professional PDF-style timetables.

## Requirements
*   **PHP 7.4+** (XAMPP Recommended)
*   **Python 3.x** (Standard installation)
*   No external Python libraries required (uses `json`, `random`, `math`).

## Quick Start (Windows)
1.  Navigate to the `saoo` folder.
2.  Double-click **`run.bat`**.
3.  The browser will open to **http://localhost:8000/login.php**.
4.  Login with **any username/password** (e.g., `admin` / `admin`).
5.  Click **"Reset Data"** (amber button) on the dashboard to load sample university data.
6.  Go to **"Timetable"** and click **"Generate Schedule"**.

## Project Structure
*   **Frontend (PHP)**: Handles user interaction, data management, and visualization.
*   **Backend (Python)**: Executes the genetic algorithm and returns optimized schedules via JSON.
*   **Database (MySQL)**: Optional. The system defaults to **Demo Mode** if no DB is connected.

## Documentation Included
*   `REPORT_CONCLUSION.txt`: Draft conclusion for your final report.
*   `PERFORMANCE_STRATEGY.txt`: Detailed explanation of the optimization algorithms used.
*   `Project_Structure.txt`: Full file list for appendix.

---
**Status**: ✅ Final Submission Ready