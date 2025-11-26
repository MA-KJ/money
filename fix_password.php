<?php
// DELETE THIS FILE AFTER USE!
$password = 'musepa123';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Your bcrypt hash is:<br>";
echo "<strong>" . $hash . "</strong><br><br>";
echo "Copy this hash and use it in phpMyAdmin SQL query:<br>";
echo "<textarea style='width:100%;height:100px;'>";
echo "UPDATE users SET password_hash = '" . $hash . "' WHERE username = 'admin';";
echo "</textarea>";
