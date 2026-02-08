<?php
/**
 * Cache Control Helper
 * 
 * Responsibility: Enforces strict no-cache headers to prevent browser caching of sensitive pages.
 * Usage: Include this file at the top of any page that requires authentication or displays user-specific data.
 */

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");
?>