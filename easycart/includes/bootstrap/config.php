<?php
/**
 * Configuration File
 * 
 * Responsibility: This file defines the global configuration for the entire application.
 * It sets up path constants and helper functions to ensure that links and includes 
 * work correctly regardless of the folder structure.
 * 
 * Why it exists: To avoid hardcoding paths everywhere. If the project moves to a new
 * folder or domain, you only need to change it here.
 * 
 * When it runs: This file is included at the very beginning of every request (via session.php).
 */

// BASE_PATH is the URL path from the domain root.
// For example, if your project is at localhost/easycart, BASE_PATH should be /easycart.
define('BASE_PATH', '/skill-development-program/easycart');

// ROOT_PATH is the absolute physical path on the server's hard drive.
// Since this file is in includes/bootstrap/, we go up two levels to reach the project root.
define('ROOT_PATH', dirname(dirname(__DIR__)));

/**
 * URL Helper Function
 * 
 * Purpose: Generates a full URL for links (<a> tags, headers).
 * Example: url('pages/products.php') returns '/easycart/pages/products.php'
 * 
 * @param string $path The relative path within the project
 * @return string The absolute URL path
 */
function url($path = '')
{
    // Clean URL Support: Strip .php extension and pages/ prefix if present
    // But be careful not to break assets or other things.
    // Actually, the user wants me to CHANGE the paths in the files. 
    // "must chnage all paths to all websites pages"
    return BASE_PATH . '/' . ltrim($path, '/');
}

/**
 * Asset Helper Function
 * 
 * Purpose: Generates a full URL for assets like CSS, JS, and Images.
 * We've moved our assets into a dedicated 'assets' folder.
 * 
 * @param string $path The relative path within the assets folder
 * @return string The absolute URL path to the asset
 */
function asset($path = '')
{
    // We prepend 'assets/' if it's not already there for convenience
    // But since the user might pass 'assets/css/main.css', we check first.
    if (strpos($path, 'assets/') === 0) {
        return BASE_PATH . '/' . ltrim($path, '/');
    }
    return BASE_PATH . '/assets/' . ltrim($path, '/');
}
