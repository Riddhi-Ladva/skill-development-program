<?php
/**
 * Simple Environment Variable Loader
 * 
 * Responsibility: Parses the .env file and populates the $_ENV superglobal
 * and getenv() function.
 * 
 * Note: This is a lightweight alternative to phpdotenv for projects without Composer.
 */

function loadEnv($path)
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse KEY=VALUE
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Load from project root
$envPath = dirname(dirname(__DIR__)) . '/.env';
loadEnv($envPath);
