<?php
require_once __DIR__ . '/../../includes/bootstrap/session.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/db_functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    // If not logged in, return empty wishlist/count explicitly
    echo json_encode(['success' => true, 'count' => 0, 'wishlist' => []]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch full list of IDs
    $wishlist = get_user_wishlist($user_id);
    $count = count($wishlist);

    echo json_encode([
        'success' => true,
        'wishlist' => $wishlist,
        'count' => $count
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
