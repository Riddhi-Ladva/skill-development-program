<?php

/**
 * Database Connection Configuration
 * 
 * This file is responsible for establishing a connection to the PostgreSQL database.
 * It uses the PDO (PHP Data Objects) extension for security and flexibility.
 * 
 * Usage:
 * require_once __DIR__ . '/../config/db.php';
 * $pdo = getDbConnection();
 */

/**
 * Establishes and returns a singleton database connection.
 * 
 * Using a function prevents global variable pollution.
 * Using a static variable ensures the connection is reused during the script execution.
 * 
 * @return PDO The PDO database connection instance.
 */
function getDbConnection() {
    // Static variable acts as a cache for the connection (Singleton pattern)
    static $pdo = null;

    // If connection is already established, return it
    if ($pdo !== null) {
        return $pdo;
    }

    // Database Configuration Variables
    // In a real production environment, load these from .env files or environment variables.
    $host = 'localhost';
    $port = '5432';       // Default PostgreSQL port
    $dbname = 'easycart';
    $user = 'postgres';
    $password = '1513'; // UPDATE THIS with your actual password

    // Data Source Name (DSN) for PostgreSQL
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

    try {
        // Create a new PDO instance
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // Throw exceptions on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // Fetch results as associative arrays
            PDO::ATTR_EMULATE_PREPARES => false,                // Use native prepared statements
        ]);

        return $pdo;

    } catch (PDOException $e) {
        // Log the error details to the server's error log (not visible to user)
        error_log("Database connection failed: " . $e->getMessage());

        // Stop execution and show a generic error message
        die("Service unavailable. Could not connect to the database.");
    }
}
