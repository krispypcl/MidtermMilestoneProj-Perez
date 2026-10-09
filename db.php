<?php
declare(strict_types=1);

/*
 * db.php
 * Holds the database settings and creates ONE PDO connection ($pdo).
 * Every page that needs the database loads this file with require_once.
 */

// [DATABASE SETTINGS]
// Kept in one place so we only change them here when moving to another server.
const DB_HOST    = 'localhost';
const DB_NAME    = 'lutong_bahay';
const DB_USER    = 'root';
const DB_PASS    = '';
const DB_CHARSET = 'utf8mb4';

// [PDO OPTIONS]
// ERRMODE_EXCEPTION: a failed query throws an exception we can catch (instead of failing silently).
// FETCH_ASSOC: rows come back as ['title' => '...'] arrays, easy to read.
// EMULATE_PREPARES false: MySQL itself handles the placeholders = real prepared statements.
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

// [DATABASE CONNECTION]
// If connecting fails, the real reason goes to the PHP error log (for us),
// and the visitor only sees a short friendly message (no passwords or server details).
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Sorry, the recipe site is having trouble right now. Please try again later.');
}
