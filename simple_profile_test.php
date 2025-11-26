<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Step 1: Starting...<br>";

require_once 'includes/app.php';
echo "Step 2: App loaded<br>";

checkPageAccess(true);
echo "Step 3: Access checked<br>";

$currentUser = getCurrentUser();
echo "Step 4: Got current user<br>";
echo "User ID: " . $currentUser['id'] . "<br>";

$userDetails = $db->fetch("SELECT * FROM users WHERE id = ?", [$currentUser['id']]);
echo "Step 5: Got user details<br>";
print_r($userDetails);

echo "<br><br><strong>If you see this, profile.php should work!</strong>";
?>
