<?php
// db.php - Database connection using PDO

$host = 'localhost';
$db   = 'marhar345_merlin';
$user = 'marhar345_merlin';
$pass = 'OaX94xS9q+';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // In production you might log this error to a file instead of showing it
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}

