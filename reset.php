<?php
require_once 'db.php';

// Encrypt 'admin123' using your server's exact PHP security generator
$new_password = password_hash('admin123', PASSWORD_DEFAULT);

try {
    // Update the database with the perfect hash
    $stmt = $pdo->prepare("UPDATE admins SET password = :password WHERE username = 'admin'");
    $stmt->execute(['password' => $new_password]);
    echo "<h2 style='color:green;'>SUCCESS! Password fixed perfectly.</h2>";
    echo "<p><a href='login.php'>Click here to Log In now!</a></p>";
} catch (PDOException $e) {
    echo "Error updating password: " . $e->getMessage();
}
?>
