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

    // 1. Get JSON Input Data
    error_log("Order placement started.");
    $input_raw = file_get_contents('php://input');
    $input = json_decode($input_raw, true);

    // For local testing/simulations
    if (!$input && isset($mock_checkout_data)) {
        $input = $mock_checkout_data;
    }

    if (!$input) {
        throw new Exception('Invalid order request. Raw input empty or invalid.');
    }

    $contact = $input['contact'] ?? [];
    $shipping_data = $input['shipping'] ?? [];
    $payment_data = $input['payment'] ?? [];

    $cart_items = get_cart_items_db($_SESSION['user_id']);
    if (empty($cart_items)) {
        throw new Exception('Cart is empty.');
    }
    error_log("Cart fetched for user " . $_SESSION['user_id']);

    // 2. Fetch products and Calculate Totals using Unified Summary
    if (!function_exists('get_products')) {
        throw new Exception('Required function get_products() is missing.');
    }
    $all_products = get_products([]);
    $products_indexed = [];
    foreach ($all_products as $p) {
        $products_indexed[$p['id']] = $p;
    }

    $summary = calculateCompleteCartSummary($cart_items, $products_indexed);

    // 3. Start Transaction
    $pdo->beginTransaction();
    error_log("Transaction started.");

    // 4. Create Order (Using Gross Subtotal + Discount Columns)
    $order_number = 'ORD-' . strtoupper(uniqid());
    $stmt = $pdo->prepare("
        INSERT INTO sales_order (
            user_id, order_number, status, grand_total, subtotal, shipping_total, 
            discount_total, tax_total, shipping_method, created_at, updated_at,
            discount_method, discount_id
        )
        VALUES (
            :user_id, :order_number, :status, :grand_total, :subtotal, :shipping_total, 
            :discount_total, :tax_total, :shipping_method, NOW(), NOW(),
            :discount_method, :discount_id
        )
        RETURNING id
    ");
    $stmt->execute([
        'user_id' => $_SESSION['user_id'],
        'order_number' => $order_number,
        'status' => 'processing',
        'grand_total' => $summary['grand_total'],
        'subtotal' => $summary['gross_subtotal'],
        'shipping_total' => $summary['shipping'],
        'discount_total' => $summary['final_discount'],
        'tax_total' => $summary['tax'],
        'shipping_method' => $summary['shipping_method'],
        'discount_method' => $summary['discount_method'],
        'discount_id' => $summary['discount_id']
    ]);
    $order_id = $stmt->fetchColumn();
    error_log("Order created: " . $order_id . " with discount type: " . ($summary['discount_method'] ?? 'none'));

    // 5. Create Order Address
    $stmt_addr = $pdo->prepare("
        INSERT INTO sales_order_address (order_id, address_type, street, city, state, zip, country, phone, email)
        VALUES (:order_id, 'shipping', :street, :city, :state, :zip, :country, :phone, :email)
    ");
    $stmt_addr->execute([
        'order_id' => $order_id,
        'street' => $shipping_data['address'] ?? '',
        'city' => $shipping_data['city'] ?? '',
        'state' => $shipping_data['state'] ?? '',
        'zip' => $shipping_data['zip'] ?? '',
        'country' => $shipping_data['country'] ?? '',
        'phone' => $shipping_data['phone'] ?? '',
        'email' => $contact['email'] ?? ''
    ]);

    // 6. Create Order Payment
    $stmt_pay = $pdo->prepare("
        INSERT INTO sales_order_payment (order_id, method, amount_paid, status, last_4)
        VALUES (:order_id, :method, :amount_paid, 'captured', :last_4)
    ");
    $stmt_pay->execute([
        'order_id' => $order_id,
        'method' => $payment_data['method'] ?? 'card',
        'amount_paid' => $summary['grand_total'],
        'last_4' => substr($payment_data['card_number'] ?? '0000', -4)
    ]);

    // 7. Process Order Items & Deduct Stock
    // Prepared statements for stock management
    $stmt_check_stock = $pdo->prepare("SELECT qty FROM catalog_product_inventory WHERE product_id = :id FOR UPDATE");
    $stmt_update_stock = $pdo->prepare("UPDATE catalog_product_inventory SET qty = :qty, is_in_stock = :in_stock WHERE product_id = :id");

    $stmt_item = $pdo->prepare("
        INSERT INTO sales_order_items (order_id, product_id, sku, name, price, qty_ordered, row_total, created_at)
        VALUES (:order_id, :product_id, :sku, :name, :price, :qty_ordered, :row_total, NOW())
    ");

    foreach ($cart_items as $product_id => $qty) {
        if (!isset($products_indexed[$product_id]))
            continue;
        $product = $products_indexed[$product_id];

        // 7a. Stock Validation & Deduction
        $stmt_check_stock->execute(['id' => $product_id]);
        $current_stock = $stmt_check_stock->fetchColumn();

        if ($current_stock === false) {
            throw new Exception("Product ID {$product_id} not found in inventory.");
        }

        if ($current_stock < $qty) {
            throw new Exception("Insufficient stock for product '{$product['name']}'. Available: {$current_stock}, Requested: {$qty}");
        }

        $new_qty = $current_stock - $qty;
        $is_in_stock = ($new_qty > 0) ? 'TRUE' : 'FALSE';

        $stmt_update_stock->execute([
            'qty' => $new_qty,
            'in_stock' => $is_in_stock,
            'id' => $product_id
        ]);

        // 7b. Create Order Item
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

    // 8. Commit
    $pdo->commit();
    error_log("Transaction committed.");

    // 9. Log Initial Status
    log_order_status_change($order_id, 'processing', 'Order placed successfully by customer.', true);

    // 9. Clear Cart from DB and session
    clear_user_cart_db($_SESSION['user_id']);
    unset($_SESSION['promo_code']);

    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'order_number' => $order_number,
        'redirect' => url('pages/orders.php')
    ]);

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Checkout Flow Error: " . $e->getMessage());
    if (ob_get_length())
        ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>