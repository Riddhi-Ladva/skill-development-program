<?php
require_once __DIR__ . '/../../includes/bootstrap/session.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/db_functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => true, 'count' => 0, 'wishlist' => [], 'guest' => true]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $wishlist = get_user_wishlist($user_id);
    echo json_encode([
        'success' => true,
        'wishlist' => $wishlist,
        'count' => count($wishlist)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
