<?php
require_once __DIR__ . '/../../includes/bootstrap/session.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/db_functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;

if ($product_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
    exit;
}

try {
    $success = add_to_wishlist_db($user_id, $product_id);
    if ($success) {
        $wishlist = get_user_wishlist($user_id);
        echo json_encode([
            'success' => true,
            'message' => 'Product added to wishlist',
            'wishlist' => $wishlist,
            'count' => count($wishlist)
        ]);
    } else {
        throw new Exception('Failed to add to wishlist');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
