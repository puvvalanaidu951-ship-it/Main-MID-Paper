# College Question Paper Generation & Examination Management System

## Project Overview
A secure PHP/MySQL application for automating faculty login, question bank management, approval workflows, random question paper generation, PDF production, email notification, analytics, history, and audit logs.

## Folder Structure
- `admin/` - administrator pages
- `faculty/` - faculty pages
- `hod/` - HOD pages
- `principal/` - principal pages
- `coe/` - COE pages
- `ajax/` - AJAX endpoints
- `assets/` - CSS, JS, images
- `config/` - application configuration
- `database/` - SQL schema and migrations
- `includes/` - shared PHP utilities
- `uploads/` - uploaded files and assets

## Deployment Instructions (XAMPP)
1. Copy project directory to `C:\xampp\htdocs\midexamportal`.
2. Start Apache and MySQL via XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin`.
4. Create database `midexamportal` if needed.
5. Import `database/schema.sql`.
6. Update `config/config.php` with your MySQL credentials.
7. Ensure `uploads/` and `logs/` folders are writable by Apache.
8. Open `http://localhost/midexamportal/login.php`.

## Default Admin User
- Email: `admin@college.edu`
- Password: `Admin@123`

> Update the administrator password immediately after first login.

## Notes
- This prototype uses Bootstrap 5 for modern UI.
- All database calls use prepared statements for security.
- CSRF protection is implemented for form submissions.
