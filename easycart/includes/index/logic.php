<?php
/**
 * Index Page Logic
 * 
 * Handles variable preparation for the home page.
 */

// Load the bootstrap file to initialize session and config
require_once __DIR__ . '/../bootstrap/session.php';

// Load brands data
require_once ROOT_PATH . '/data/brands.php';
$brands = isset($brands) && is_array($brands) ? $brands : [];

// Define page specific variables if any
$title = "EasyCart - Home";
