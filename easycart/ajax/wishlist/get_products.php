<?php
require_once __DIR__ . '/../../includes/bootstrap/session.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/db_functions.php';

header('Content-Type: application/json');

// Get IDs from input
$input = json_decode(file_get_contents('php://input'), true);
$ids = isset($input['ids']) && is_array($input['ids']) ? $input['ids'] : [];

if (empty($ids)) {
    echo json_encode(['success' => true, 'products' => []]);
    exit;
}

// Sanitize IDs
$ids = array_filter($ids, fn($id) => is_numeric($id) && $id > 0);

if (empty($ids)) {
    echo json_encode(['success' => true, 'products' => []]);
    exit;
}

try {
    $pdo = getDbConnection();

    // Create placeholders
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    // Fetch product details similar to get_user_wishlist_details
    // We reuse logic but filtered by IDs
    $sql = "SELECT p.id, p.name, 
            COALESCE(pp.price, 0) as price,
            pi.image_path as image,
            b.name as brand_name,
            inv.is_in_stock
            FROM catalog_product_entity p
            LEFT JOIN catalog_product_price pp ON pp.product_id = p.id AND pp.customer_group_id = 0
            LEFT JOIN catalog_product_images pi ON pi.product_id = p.id AND pi.is_main = TRUE
            LEFT JOIN catalog_brand b ON p.brand_id = b.id
            LEFT JOIN catalog_product_inventory inv ON inv.product_id = p.id
            WHERE p.id IN ($placeholders) AND p.status = 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($ids));
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'products' => $products
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
