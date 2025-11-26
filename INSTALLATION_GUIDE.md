# 🚀 Loan Tracking System - Installation Guide

## 📋 Requirements

- **PHP 7.4+** (Tested on PHP 8.2+)
- **MySQL 5.7+** or **MariaDB**
- **Web Server** (Apache/Nginx)
- **WAMP/XAMPP/LAMP** or similar local server environment

---

## 🛠️ Quick Installation (5 Minutes)

### Step 1: Extract Files
Extract the project to your web server directory:
- **WAMP**: `C:\wamp64\www\loan-tracking-system`
- **XAMPP**: `C:\xampp\htdocs\loan-tracking-system`
- **LAMP**: `/var/www/html/loan-tracking-system`

### Step 2: Create Database
Open **phpMyAdmin** or MySQL command line and run:

```sql
CREATE DATABASE loan_tracking_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 3: Import Database Schema
Import the database structure:

**Option A - phpMyAdmin:**
1. Select the `loan_tracking_system` database
2. Click "Import" tab
3. Choose `includes/schema.sql`
4. Click "Go"

**Option B - Command Line:**
```bash
mysql -u root -p loan_tracking_system < includes/schema.sql
```

### Step 4: Configure Database Connection
Edit `includes/config.php` and update:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'loan_tracking_system');
define('DB_USER', 'root');          // Your MySQL username
define('DB_PASS', '');              // Your MySQL password
define('SITE_URL', 'http://localhost/loan-tracking-system');
```

### Step 5: Set Permissions (Linux/Mac only)
```bash
chmod 755 loan-tracking-system/
chmod 777 loan-tracking-system/logs/
```

### Step 6: Access the System
Open your browser and navigate to:
```
http://localhost/loan-tracking-system
```

---

## 🔐 Default Login Credentials

- **Username:** `admin`
- **Password:** `admin123`

⚠️ **IMPORTANT:** Change this password immediately after first login!

---

## ✨ Features

### Core Features
- ✅ **Dashboard** - Overview with statistics and recent loans
- ✅ **Loan Management** - Add, edit, delete, and track loans
- ✅ **Payment Tracking** - Mark loans as paid (full or partial amounts)
- ✅ **User Management** - Create and manage admin accounts (Super Admin only)
- ✅ **Statistics & Charts** - Visual analytics with Chart.js
- ✅ **Reports** - Generate reports for 3, 6, 9, and 12-month periods
- ✅ **Profile Management** - Users can update their own information
- ✅ **Password Reset** - Change passwords without email verification

### Security Features
- ✅ **PDO with Prepared Statements** - SQL injection prevention
- ✅ **CSRF Protection** - All forms protected
- ✅ **Password Hashing** - Secure password storage
- ✅ **Session Management** - Secure session handling
- ✅ **Input Validation** - All inputs validated and sanitized
- ✅ **Security Logging** - All actions logged for audit

---

## 🎯 User Roles

### Super Admin
- Full system access
- Create/edit/delete users
- Manage all loans
- Access all features
- View system logs

### Admin
- Create and manage loans
- View statistics and reports
- Update own profile
- Limited administrative access

---

## 📱 System Features

### Loan Management
- **Add Loans** - With automatic interest calculations
- **Track Payments** - Full or partial payment tracking
- **Status Tracking** - Unpaid, Paid, Overdue, Partially Paid
- **Due Date Alerts** - Automatic overdue detection

### Statistics Dashboard
- Total loans count
- Paid vs unpaid loans
- Total interest earned
- Financial summaries
- Visual charts and graphs

### Reports
- Generate reports for different time periods
- Export to PDF/Excel
- Print-friendly layouts
- Detailed loan breakdowns

---

## 🔧 Troubleshooting

### Database Connection Error
- Check `includes/config.php` credentials
- Ensure MySQL server is running
- Verify database exists

### Blank/White Pages
- Enable error display in `includes/config.php`:
  ```php
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  ```
- Check PHP error logs
- Verify all files uploaded correctly

### Permission Errors
- Ensure `logs/` directory is writable
- Check file permissions (755 for directories, 644 for files)

### Login Issues
- Verify default credentials: `admin` / `admin123`
- Clear browser cache and cookies
- Check if database was imported correctly

---

## 📊 Database Structure

The system uses 5 main tables:
- **users** - User accounts and authentication
- **loans** - Loan records
- **payments** - Payment history
- **loan_history** - Audit trail
- **settings** - System configuration

---

## 🔄 Updating the System

1. **Backup Database:**
   ```bash
   mysqldump -u root -p loan_tracking_system > backup.sql
   ```

2. **Backup Files:**
   - Copy entire project folder to safe location

3. **Apply Updates:**
   - Replace files
   - Run any new SQL migration scripts

---

## 💡 Tips for Best Use

1. **Change Default Password** - Do this immediately!
2. **Regular Backups** - Backup database weekly
3. **Use Strong Passwords** - For all user accounts
4. **Monitor Logs** - Check `logs/security.log` regularly
5. **Test on Localhost** - Before deploying to production

---

## 📞 Support

If you encounter any issues:
1. Check this documentation
2. Review error logs in `/logs/`
3. Verify all installation steps completed
4. Check browser console (F12) for JavaScript errors

---

## 📄 License

This project is proprietary software. All rights reserved.

---

## 👨‍💻 Created By

**ACTiveVision**
Visit: [https://activevision.42web.io/?i=1](https://activevision.42web.io/?i=1)

---

**Made with ❤️ using PHP, MySQL, and Bootstrap**
