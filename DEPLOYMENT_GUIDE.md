# Loan Tracking System - Deployment Guide for Shared Hosting

## For InfinityFree and Similar Shared Hosting Platforms

This guide will help you deploy your Loan Tracking System on shared hosting platforms like InfinityFree.

---

## 🚀 Quick Start

### Prerequisites
- A hosting account (InfinityFree, 000webhost, or similar)
- FTP client (FileZilla recommended) or File Manager access
- Database access through phpMyAdmin

---

## 📁 Step 1: Upload Files

### Option A: Using FTP Client (FileZilla)
1. Download and install [FileZilla](https://filezilla-project.org/)
2. Connect to your hosting using FTP credentials from your hosting panel
3. Navigate to `htdocs` or `public_html` folder
4. Upload ALL files from your Loan-Tracking-System-Complete folder

### Option B: Using File Manager
1. Log into your hosting control panel (cPanel/Vista Panel)
2. Open File Manager
3. Navigate to `htdocs` or `public_html`
4. Upload all files as a ZIP and extract them

**Important Folders to Upload:**
- `/includes/` - Core system files
- `/api/` - API endpoints
- `/css/` - Stylesheets
- `/logs/` - System logs (create if missing)
- All `.php` files in root
- `.htaccess` file (important for security and routing)

---

## 🗄️ Step 2: Setup Database

### Create Database
1. Go to your hosting control panel
2. Find "MySQL Databases" or similar section
3. Create a new database (remember the name, usually like `if0_12345678_loan_tracking`)
4. Create a database user with a strong password
5. Add the user to the database with ALL PRIVILEGES

### Import Database Schema
1. Open phpMyAdmin from your control panel
2. Select your newly created database
3. Click on "Import" tab
4. Choose file: `includes/schema_shared_hosting.sql` (NOT schema.sql)
5. Click "Go" to import

**Why schema_shared_hosting.sql?**
- Removes `CREATE DATABASE` command (not allowed on shared hosting)
- Removes `CREATE VIEW` command (not supported on InfinityFree)
- Properly structured for shared hosting limitations

**Default Login Credentials After Import:**
- **Username:** admin
- **Password:** admin123
- ⚠️ **IMPORTANT:** Change this password immediately after first login!

---

## ⚙️ Step 3: Configure Database Connection

1. Open `includes/config.php` in File Manager or FTP client
2. Update these lines with your database details:

```php
// Database configuration
define('DB_HOST', 'sql123.infinityfree.com'); // Your DB host
define('DB_NAME', 'if0_12345678_loan_tracking'); // Your DB name
define('DB_USER', 'if0_12345678'); // Your DB username
define('DB_PASS', 'your_database_password'); // Your DB password
```

**Finding Your Database Details:**
- Log into your hosting control panel
- Go to MySQL Databases section
- You'll see hostname, database name, and username
- Use the password you created when setting up the database

### Important Note About SITE_URL
The system now **automatically detects** the correct URL! You don't need to change the SITE_URL in config.php anymore. It works on:
- Local development (http://localhost)
- Subdomain (https://yoursite.infinityfreeapp.com)
- Custom domain (https://yourdomain.com)

---

## 🔒 Step 4: Secure Your Installation

### Set Proper Permissions
In File Manager or via FTP, set these permissions:

```
/logs/ folder: 755 (rwxr-xr-x)
/includes/ folder: 755 (rwxr-xr-x)
All PHP files: 644 (rw-r--r--)
```

### Verify .htaccess is Active
The `.htaccess` file should be in your root directory. It provides:
- Security headers
- Directory access protection
- Prevents direct access to includes/ and logs/ folders

If you can't see `.htaccess` in File Manager:
1. Click "Settings" in File Manager
2. Enable "Show Hidden Files"

---

## 🧪 Step 5: Test Your Installation

1. Visit your website URL (e.g., https://yoursite.infinityfreeapp.com)
2. You should be redirected to the login page
3. Login with default credentials:
   - Username: `admin`
   - Password: `admin123`
4. Test navigation:
   - Dashboard ✓
   - Add Loan ✓
   - All Loans ✓
   - Statistics ✓
   - Reports ✓
   - Profile ✓

### Test Checklist
- [ ] Login works
- [ ] Navigation links work (no 404 errors)
- [ ] Can add a test loan
- [ ] Can view loan details
- [ ] Can edit profile
- [ ] Can change password
- [ ] Statistics show correctly
- [ ] Logout works

---

## 🐛 Troubleshooting

### Problem: "Database connection failed"
**Solution:** 
- Double-check database credentials in `config.php`
- Ensure database user has ALL PRIVILEGES
- Verify database host is correct (not always 'localhost' on shared hosting)

### Problem: "404 Not Found" on navigation links
**Solution:**
- Verify `.htaccess` file is uploaded
- Check if mod_rewrite is enabled (most shared hosts have it enabled)
- Ensure all PHP files are in the root directory

### Problem: "Headers already sent" error
**Solution:**
- Check that no PHP files have whitespace before `<?php`
- Save all files with UTF-8 encoding (no BOM)

### Problem: Navigation links don't work, taking you to wrong pages
**Solution:**
- Clear your browser cache
- The system now auto-detects URLs, so it should work automatically
- Check that SITE_URL is being set correctly in `config.php`

### Problem: CSS/Styles not loading
**Solution:**
- Check that `/css/` folder is uploaded
- Verify file permissions (644 for CSS files)
- Hard refresh your browser (Ctrl+F5)

### Problem: "Views not supported" when importing schema.sql
**Solution:**
- Use `schema_shared_hosting.sql` instead
- This version removes VIEW creation which isn't supported on InfinityFree

---

## 🔐 Security Best Practices

### After First Login
1. **Change default password immediately:**
   - Go to Profile → Change Password
   - Use a strong password (12+ characters, mixed case, numbers, symbols)

2. **Update admin email:**
   - Go to Profile
   - Change email from `admin@loansystem.com` to your real email

3. **Create a new super admin (optional):**
   - Go to Admin → Manage Users
   - Create new super admin account with your details
   - Delete or disable the default admin account

### Ongoing Security
- Regular backups (use hosting backup feature)
- Update admin passwords every 90 days
- Monitor system logs (Admin → System Logs)
- Keep only active users enabled
- Use strong passwords for all user accounts

---

## 🎨 Customization

### Change Currency Symbol
1. Login as super admin
2. Go to Admin → Settings (if available)
3. Or edit directly in database: `settings` table → `currency_symbol`

### Change Site Name
1. Edit in database: `settings` table → `site_name`
2. Or update in `config.php`: `SITE_NAME` constant

### Add Custom Domain
1. Purchase domain from domain registrar
2. In InfinityFree panel, add domain to your account
3. Update nameservers at domain registrar
4. Wait 24-48 hours for propagation
5. System will automatically detect new domain!

---

## 📊 Database Backup

### Manual Backup (Recommended Weekly)
1. Open phpMyAdmin
2. Select your database
3. Click "Export" tab
4. Choose "Quick" export method
5. Format: SQL
6. Click "Go" to download backup

### Restore from Backup
1. Open phpMyAdmin
2. Select your database
3. Click "Import" tab
4. Choose your backup .sql file
5. Click "Go"

---

## 📞 Support

### InfinityFree Specific Issues
- [InfinityFree Forum](https://forum.infinityfree.com/)
- [InfinityFree Knowledge Base](https://infinityfree.com/support/)

### Common InfinityFree Limitations
- No CREATE DATABASE privileges (handled in our schema)
- No CREATE VIEW privileges (handled in our schema)
- Limited cron jobs (manual statistics updates needed)
- Hit limit of ~50,000 hits/day
- 10 MB file upload limit

---

## ✅ Post-Deployment Checklist

- [ ] Files uploaded to htdocs/public_html
- [ ] Database created and user assigned
- [ ] schema_shared_hosting.sql imported successfully
- [ ] config.php updated with correct database credentials
- [ ] .htaccess file is present and active
- [ ] Can login with default credentials
- [ ] Default password changed
- [ ] Admin email updated
- [ ] Navigation works correctly
- [ ] Test loan created successfully
- [ ] Database backup created
- [ ] Bookmark admin panel for easy access

---

## 🎉 Congratulations!

Your Loan Tracking System is now live and ready to use!

**Next Steps:**
1. Create user accounts for your team
2. Add your first real loans
3. Explore statistics and reports
4. Customize currency and settings as needed

**Need Help?**
- Check the troubleshooting section above
- Review InfinityFree documentation
- Ensure all steps were followed correctly

---

## 📝 Notes

### Why Dynamic URL Detection?
The system automatically detects your website URL, so it works seamlessly whether you're using:
- Free subdomain (yoursite.infinityfreeapp.com)
- Custom domain (yourdomain.com)
- Local development (localhost)

No manual URL configuration needed!

### File Structure
```
/htdocs or /public_html
│
├── includes/
│   ├── config.php (UPDATE THIS)
│   ├── schema_shared_hosting.sql (IMPORT THIS)
│   ├── database.php
│   ├── auth.php
│   └── ...other includes
│
├── api/
│   └── mark-loan-paid.php
│
├── css/
│   └── style.css
│
├── logs/
│   └── (system logs here)
│
├── .htaccess (IMPORTANT)
├── index.php
├── login.php
├── dashboard.php
└── ...other PHP files
```

---

**Version:** 1.0  
**Last Updated:** 2025  
**Compatible With:** InfinityFree, 000webhost, Hostinger Free, and similar PHP/MySQL shared hosting
