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
$input = json_decode(file_get_contents('php://input'), true);
$product_id = isset($input['product_id']) ? (int) $input['product_id'] : 0;

if ($product_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
    exit;
}

try {
    $pdo = getDbConnection();
    $cart_id = get_user_cart_id($user_id);

    // 1. Check if product is ALREADY in cart
    $stmt = $pdo->prepare("SELECT 1 FROM sales_cart_items WHERE cart_id = :cart_id AND product_id = :product_id");
    $stmt->execute([':cart_id' => $cart_id, ':product_id' => $product_id]);
    $exists_in_cart = $stmt->fetchColumn();

    // 2. Strict Logic: Only Add if NOT in Cart
    if (!$exists_in_cart) {
        add_to_cart_db($user_id, $product_id, 1);
        $message = 'Moved to cart';
    } else {
        $message = 'Removed from wishlist'; // Already in cart, just remove from wishlist
    }

    // 3. Always Remove from Wishlist
    remove_from_wishlist_db($user_id, $product_id);

    // 4. Returns updated counts
    $wishlist = get_user_wishlist($user_id);
    require_once ROOT_PATH . '/includes/cart/services.php';
    $cart_count = getCartCount();

    echo json_encode([
        'success' => true,
        'message' => $message,
        'wishlist' => $wishlist,
        'cartCount' => $cart_count
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
