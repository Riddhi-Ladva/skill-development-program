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
function getDbConnection()
{
    // Static variable acts as a cache for the connection (Singleton pattern)
    static $pdo = null;

    // If connection is already established, return it
    if ($pdo !== null) {
        return $pdo;
    }

    // Database Configuration Variables
    // In a real production environment, load these from .env files or environment variables.
    $host = getenv('DB_HOST') ?: 'localhost';
    $port = getenv('DB_PORT') ?: '5432';
    $dbname = getenv('DB_NAME') ?: 'easycart';
    $user = getenv('DB_USER') ?: 'postgres';
    $password = getenv('DB_PASSWORD') ?: '';

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

        // Throw a generic exception to be caught by the caller
        throw new Exception("Database connection failed. Please contact administrator.");
    }
}
