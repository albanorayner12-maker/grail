<?php
/**
 * Shared database connection.
 *
 * Schema creation and migrations intentionally do not run from web requests.
 * They should be applied separately when the database structure is ready.
 */
$host = 'localhost';
$dbname = 'grail_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $exception) {
    throw new RuntimeException(
        'The database is currently unavailable. Start MySQL and verify the GRAIL database configuration.',
        0,
        $exception
    );
}
