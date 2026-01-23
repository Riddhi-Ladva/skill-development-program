<?php
/**
 * Configuration file for EasyCart
 * Defines base paths and constants for the application
 */

// Define the base path for the application
// This works regardless of where the project is installed
define('BASE_PATH', '/skill-development-program/easycart');

// Define absolute server paths
define('ROOT_PATH', dirname(__DIR__));

// Helper function to generate URLs
function url($path = '')
{
    return BASE_PATH . '/' . ltrim($path, '/');
}

// Helper function to generate asset URLs
function asset($path = '')
{
    return BASE_PATH . '/' . ltrim($path, '/');
}
