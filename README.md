# Attendance System

A simple attendance tracking system with separate student and admin interfaces.

## Features

### Student Features
- PIN-based login
- Time-in and time-out tracking
- Daily journal entries
- View attendance history

### Admin Features
- Username/password login
- Monitor student attendance
- Generate reports
- Manage users
- View student journals

## Installation

1. Clone the repository to your web server directory
2. Import the database schema from `database/migrations/`
3. Configure database connection in `config/database.php`
4. Access the system at `http://localhost/attendance-system/`

## Default Admin Credentials

- Username: admin
- Password: admin123

## Default Student Access

Students use a 4-digit PIN to login. Create student accounts through the admin interface.

## Directory Structure

- `app/` - Application core files
  - `controller/` - Controllers
  - `model/` - Data models
  - `view/` - View templates
  - `core/` - Core system files
- `assets/` - CSS, JavaScript, and images
- `config/` - Configuration files
- `database/` - Database migrations
- `public/` - Public entry point
- `storage/` - Logs and uploads