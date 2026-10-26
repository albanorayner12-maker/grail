<?php


$host = '127.0.0.1';
$db   = 'grail_db';
$user = 'root'; // Keep as root for local development, or use a dedicated user
$pass = '';     // Insert your MariaDB root password profile string here if configured
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false, // Disables client-side emulation to enforce true native SQL injection security
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // Secure error trapping logic for capstone defenses
     error_log("GRAIL System Core DB Failure: " . $e->getMessage());
     die("A secure data infrastructure error occurred. Access vector halted.");
}
?>
