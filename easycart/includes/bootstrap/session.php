<?php
/**
 * Session & Bootstrap Loader
 * 
 * Responsibility: This file starts the user session and loads the core configuration.
 * It also initializes essential session variables like the shopping cart.
 * 
 * Why it exists: It acts as the "bootstrap" file. By including this at the top 
 * of any page, you immediately get access to session data and configuration helper functions.
 * 
 * When it runs: At the very top of every PHP file in this project.
 */

// Include the configuration file for paths and constants
require_once __DIR__ . '/config.php';

// Start the session if it hasn't been started already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Cart Initialization
 * 
 * Business Rule: Every user must have a 'cart' array in their session.
 * If they don't have one (e.g., first visit), we create an empty one.
 */
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/**
 * Shipping Initialization
 * 
 * Business Rule: Every user starts with 'standard' shipping as the default.
 */
if (!isset($_SESSION['shipping_method'])) {
    $_SESSION['shipping_method'] = 'standard';
}
