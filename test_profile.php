<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Profile Page</h2>";

try {
    require_once 'includes/app.php';
    echo "✅ includes/app.php loaded<br>";
    
    // Check if user is logged in
    if (!isLoggedIn()) {
        echo "❌ User not logged in<br>";
        echo "Redirecting to login...<br>";
        header('Location: login.php');
        exit;
    }
    
    echo "✅ User is logged in<br>";
    
    $currentUser = getCurrentUser();
    echo "✅ Current user retrieved: ";
    print_r($currentUser);
    echo "<br>";
    
    // Get full user details from database
    $userDetails = $db->fetch(
        "SELECT * FROM users WHERE id = ?",
        [$currentUser['id']]
    );
    
    echo "✅ User details from database: ";
    print_r($userDetails);
    echo "<br>";
    
    echo "<hr>";
    echo "<h3>All tests passed! Profile page should work.</h3>";
    echo '<a href="profile.php">Go to Profile Page</a>';
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}
?>
