<?php
require_once __DIR__ . '/../../includes/bootstrap/session.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/db-functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Support JSON input consistently
$input = json_decode(file_get_contents('php://input'), true);
$product_id = isset($input['product_id']) ? (int) $input['product_id'] : 0;

// Fallback to POST if JSON fails
if ($product_id === 0 && isset($_POST['product_id'])) {
    $product_id = (int) $_POST['product_id'];
}

if ($product_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
    exit;
}

try {
    $success = remove_from_wishlist_db($user_id, $product_id);
    $wishlist = get_user_wishlist($user_id);

    echo json_encode([
        'success' => true,
        'message' => 'Removed from wishlist',
        'wishlist' => $wishlist,
        'count' => get_wishlist_count_db($user_id)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
