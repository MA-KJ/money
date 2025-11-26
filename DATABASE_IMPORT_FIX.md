# Database Import Errors - Quick Fix

## ❌ Error 1: Access Denied for Database Creation
```
SQL query: CREATE DATABASE IF NOT EXISTS loan_tracking_system...
MySQL said: #1044 - Access denied for user 'if0_40061184'@'192.168.%' to database 'loan_tracking_system'
```

**Why This Happens:**
- Shared hosting (like InfinityFree) doesn't allow you to create databases via SQL
- You must create databases through the hosting control panel

**✅ Solution:**
1. **DO NOT use** `includes/schema.sql`
2. **USE** `includes/schema_shared_hosting.sql` instead
3. This file removes the `CREATE DATABASE` command
4. Create the database manually in your hosting control panel FIRST
5. Then import the schema

---

## ❌ Error 2: CREATE VIEW Command Denied
```
SQL query: CREATE OR REPLACE VIEW loan_statistics AS...
MySQL said: #1142 - CREATE VIEW command denied to user 'if0_40061184'@'192.168.0.6'
```

**Why This Happens:**
- InfinityFree and many free hosting services don't allow VIEW creation
- This is a security/resource limitation

**✅ Solution:**
1. **USE** `includes/schema_shared_hosting.sql` - it doesn't have VIEW statements
2. The application calculates statistics in PHP code instead of using database views
3. No functionality is lost, just a different implementation

---

## 🎯 Correct Import Process

### Step-by-Step:

1. **Login to your hosting control panel** (cPanel/Vista Panel)

2. **Create Database:**
   - Go to "MySQL Databases" section
   - Click "Create New Database"
   - Name it something like: `loan_tracking` or `loan_system`
   - Remember the full database name (e.g., `if0_12345678_loan_tracking`)

3. **Create Database User:**
   - Create a new MySQL user
   - Set a strong password
   - Remember username and password

4. **Add User to Database:**
   - Find "Add User to Database" section
   - Select your user
   - Select your database
   - Grant ALL PRIVILEGES
   - Click "Add"

5. **Import Schema:**
   - Open phpMyAdmin from control panel
   - Click on your database name in left sidebar
   - Click "Import" tab
   - Click "Choose File"
   - Select: `includes/schema_shared_hosting.sql` ✅
   - DO NOT select: `includes/schema.sql` ❌
   - Click "Go"
   - Wait for success message

6. **Update Config:**
   - Edit `includes/config.php`
   - Update DB_HOST, DB_NAME, DB_USER, DB_PASS
   - Save file

---

## 📋 Comparison: Which Schema File to Use?

| File | Use For | Contains |
|------|---------|----------|
| `schema.sql` | ❌ DO NOT USE for shared hosting | CREATE DATABASE, CREATE VIEW |
| `schema_shared_hosting.sql` | ✅ USE for InfinityFree/shared hosting | Only CREATE TABLE statements |

---

## 🔍 How to Check If Import Was Successful

After importing `schema_shared_hosting.sql`, you should see these tables in phpMyAdmin:

- ✅ `users` - User accounts
- ✅ `loans` - Loan records
- ✅ `payments` - Payment tracking
- ✅ `loan_history` - Change logs
- ✅ `settings` - Application settings

**Default Data:**
- 1 user: admin / admin123
- 5 settings entries

If you see all these, the import was successful! 🎉

---

## 🆘 Still Having Issues?

### Check Your Hosting Privileges
Run this in phpMyAdmin SQL tab:
```sql
SHOW GRANTS;
```

You should see something like:
```
GRANT ALL PRIVILEGES ON `if0_12345678_loan_tracking`.* TO 'if0_12345678'@'%'
```

If you see `GRANT USAGE` only, contact your hosting support.

### Test Database Connection
Create a test file `test_db.php` in your root directory:
```php
<?php
$host = 'sql123.infinityfree.com'; // Your host
$db = 'if0_12345678_loan_tracking'; // Your database
$user = 'if0_12345678'; // Your username
$pass = 'your_password'; // Your password

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    echo "✅ Connected successfully!";
} catch(PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
?>
```

Visit: `https://yoursite.infinityfreeapp.com/test_db.php`

**Important:** Delete this test file after checking!

---

## 📞 Need More Help?

1. Read the full deployment guide: `DEPLOYMENT_GUIDE.md`
2. Check InfinityFree documentation
3. Visit InfinityFree support forum
4. Ensure you're using `schema_shared_hosting.sql` not `schema.sql`

---

**Quick Reminder:**
- ✅ Create database in control panel first
- ✅ Use `schema_shared_hosting.sql` for import
- ✅ Update `includes/config.php` with correct credentials
- ❌ Don't use `schema.sql` on shared hosting
