<?php
/**
 * Auth Guard
 * 
 * Responsibility: Protects routes and endpoints by checking for an active user session.
 */

if (!function_exists('auth_guard')) {
    /**
     * Redirects to login page if user is not authenticated.
     */
    function auth_guard()
    {
        // Enforce no-cache for protected pages
        require_once dirname(__DIR__) . '/auth/cache-control.php';

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . url('login'));
            exit;
        }
    }
}

if (!function_exists('ajax_auth_guard')) {
    /**
     * Returns 401 Unauthorized if user is not authenticated for AJAX requests.
     */
    function ajax_auth_guard()
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized access. Please log in.']);
            exit;
        }
    }
}
?>