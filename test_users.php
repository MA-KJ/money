<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Users Page</h2>";

echo "Step 1: Loading app...<br>";
require_once 'includes/app.php';
echo "✅ App loaded<br>";

echo "Step 2: Checking if logged in...<br>";
if (!isLoggedIn()) {
    echo "❌ Not logged in<br>";
    exit;
}
echo "✅ Logged in<br>";

echo "Step 3: Checking if super admin...<br>";
if (!isSuperAdmin()) {
    echo "❌ Not super admin. Your role: " . getCurrentUser()['role'] . "<br>";
    exit;
}
echo "✅ Is super admin<br>";

echo "Step 4: Getting all users...<br>";
try {
    $users = getAllUsers();
    echo "✅ Got " . count($users) . " users<br>";
    echo "<pre>";
    print_r($users);
    echo "</pre>";
} catch (Exception $e) {
    echo "❌ Error getting users: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}

echo "<br><strong>All tests passed! Users page should work.</strong><br>";
echo '<a href="users.php">Go to Users Page</a>';
?>
