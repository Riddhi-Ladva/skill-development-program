<?php
require_once __DIR__ . '/../../includes/bootstrap/session.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/auth/guard.php';
require_once ROOT_PATH . '/includes/cart/services.php';
require_once ROOT_PATH . '/includes/shipping/services.php';
require_once ROOT_PATH . '/includes/tax/services.php';
require_once ROOT_PATH . '/includes/db_functions.php';

// Ensure user is logged in
ajax_auth_guard();

header('Content-Type: application/json');

try {
    $pdo = getDbConnection();

    // 1. Get Cart Data from DB
    $cart_items = get_cart_items_db($_SESSION['user_id']);
    if (empty($cart_items)) {
        throw new Exception('Cart is empty.');
    }

    // 2. Fetch products and Calculate Totals
    $all_products = get_products([]);
    $products_indexed = [];
    foreach ($all_products as $p) {
        $products_indexed[$p['id']] = $p;
    }

    $subtotal = calculateSubtotal($cart_items, $products_indexed);
    $shipping_method = $_SESSION['shipping_method'] ?? 'standard';
    $shipping = calculateShippingCost($shipping_method, $subtotal);
    $totals = calculateCheckoutTotals($subtotal, $shipping);

    $grand_total = $totals['total'];
    $subtotal_val = $totals['subtotal'];
    $tax_val = $totals['tax'];
    $discount_val = $totals['promo_discount'] ?? 0;

    // 3. Start Transaction
    $pdo->beginTransaction();

    // 4. Create Order
    $order_number = 'ORD-' . strtoupper(uniqid());
    $stmt = $pdo->prepare("
        INSERT INTO sales_order (user_id, order_number, status, grand_total, subtotal, shipping_total, discount_total, tax_total, created_at, updated_at)
        VALUES (:user_id, :order_number, :status, :grand_total, :subtotal, :shipping_total, :discount_total, :tax_total, NOW(), NOW())
        RETURNING id
    ");
    $stmt->execute([
        'user_id' => $_SESSION['user_id'],
        'order_number' => $order_number,
        'status' => 'processing',
        'grand_total' => $grand_total,
        'subtotal' => $subtotal_val,
        'shipping_total' => $shipping,
        'discount_total' => $discount_val,
        'tax_total' => $tax_val
    ]);
    $order_id = $stmt->fetchColumn();

    // 5. Create Order Items
    $stmt_item = $pdo->prepare("
        INSERT INTO sales_order_items (order_id, product_id, sku, name, price, qty_ordered, row_total, created_at)
        VALUES (:order_id, :product_id, :sku, :name, :price, :qty_ordered, :row_total, NOW())
    ");

    foreach ($cart_items as $product_id => $qty) {
        if (!isset($products_indexed[$product_id]))
            continue;
        $product = $products_indexed[$product_id];

        $stmt_item->execute([
            'order_id' => $order_id,
            'product_id' => $product_id,
            'sku' => $product['sku'] ?? 'SKU-' . $product_id,
            'name' => $product['name'],
            'price' => $product['price'],
            'qty_ordered' => $qty,
            'row_total' => $product['price'] * $qty
        ]);
    }

    // 6. Commit
    $pdo->commit();

    // 7. Clear Cart from DB and session
    clear_user_cart_db($_SESSION['user_id']);
    unset($_SESSION['promo_code']);

    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'order_number' => $order_number,
        'redirect' => url('pages/orders.php')
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>