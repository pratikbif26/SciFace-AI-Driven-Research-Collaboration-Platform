<?php
// db.php

$db_server = "localhost";
$db_user = "root";
$db_pass = "eckosql";
$db_name = "testsciface";
$charset = 'utf8mb4';

// Data Source Name (DSN)
$dsn = "mysql:host=$db_server;dbname=$db_name;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use real prepared statements
];

try {
    // This $pdo variable is the one used by register.php and search.php
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (\PDOException $e) {
    // We don't echo anything here.
    // Instead, we throw the exception, which will be caught 
    // by the try...catch blocks in register.php or any other script.
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>