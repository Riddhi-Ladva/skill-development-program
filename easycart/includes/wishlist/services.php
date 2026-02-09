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
    // Ensure session is accessible
    if (session_status() === PHP_SESSION_NONE) {
        // We shouldn't start it here side-effect free, but we rely on it being started.
    }

    // 1. Logged-in User -> DB Source
    if (isset($_SESSION['user_id'])) {
        // Ensure DB functions are loaded
        if (!function_exists('get_wishlist_count_db')) {
            require_once dirname(__DIR__) . '/db-functions.php';
        }

        try {
            $count = (int) get_wishlist_count_db($_SESSION['user_id']);
            if ($count === 0) {
                error_log("[Wishlist Debug] DB count is 0 for User ID: " . $_SESSION['user_id']);
            }
            return $count;
        } catch (Exception $e) {
            error_log("[Wishlist Debug] DB Error: " . $e->getMessage());
            return 0;
        }
    }

    // 2. Guest User -> Render 0. JS handles LocalStorage update.
    return 0;
}
