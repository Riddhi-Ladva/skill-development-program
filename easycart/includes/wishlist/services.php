<?php
/**
 * Wishlist Service Functions
 */

/**
 * Get the current wishlist count for the user.
 * 
 * Supports:
 * - Logged-in users (DB source)
 * - Guest users (Returns 0 as strictly DB-only per requirements)
 * 
 * @return int Total number of items in wishlist
 */
function getWishlistCount()
{
    // 1. Logged-in User -> DB Source
    if (isset($_SESSION['user_id'])) {
        // Ensure DB functions are loaded
        if (!function_exists('get_wishlist_count_db')) {
            require_once dirname(__DIR__) . '/db_functions.php';
        }
        return get_wishlist_count_db($_SESSION['user_id']);
    }

    // 2. Guest User -> Not supported per strict requirements
    return 0;
}
