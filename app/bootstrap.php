<?php
/**
 * bootstrap.php
 * Central application initializer.
 */

// 1. Start Session securely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Load Configuration
$config = require __DIR__ . '/../config/config.php';

// 3. Global PDO Connection (Modern & Secure)
try {
    $dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['dbname']};charset={$config['db']['charset']}";
    $pdo = new PDO($dsn, $config['db']['username'], $config['db']['password'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die("Critical Error: Could not connect to the database.");
}

// 4. Load Security & Auth Helpers
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/auth.php';

// Global access to $pdo for all files
$GLOBALS['pdo'] = $pdo;