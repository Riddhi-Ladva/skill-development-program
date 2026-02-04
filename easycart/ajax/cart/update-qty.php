<?php
/**
 * AJAX Endpoint: Update Cart Quantity
 *
 * Purpose: Handling quantity updates from the cart page.
 * Logic: Updates session state, then performs a full recalculation of the cart (Shipping, Tax, Totals)
 * to return specific data needed for live UI updates.
 */

ob_start();

require_once __DIR__ . '/../../includes/bootstrap/session.php';
require_once ROOT_PATH . '/includes/db_functions.php';
require_once ROOT_PATH . '/includes/auth/guard.php';

// Protect endpoint: Logged-in users only
ajax_auth_guard();
require_once ROOT_PATH . '/includes/cart/services.php';
require_once ROOT_PATH . '/includes/shipping/services.php';
require_once ROOT_PATH . '/includes/tax/services.php';

ob_end_clean();

header('Content-Type: application/json');

// Decoding JSON input from JS
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['product_id']) || !isset($input['quantity'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$product_id = $input['product_id'];
$quantity = (int) $input['quantity'];

// Business Rule: Keep quantity between 1 and 10
if ($quantity < 1)
    $quantity = 1;
if ($quantity > 10)
    $quantity = 10;

// SYNC WITH DB
update_cart_qty_db($_SESSION['user_id'], $product_id, $quantity);

// Fetch current cart from DB for calculation
$cart = get_cart_items_db($_SESSION['user_id']);

if (true) { // Logic was wrapped in session check, now using DB

    // Fetch all products for calculation
    $all_products = get_products([]);
    $products_indexed = [];
    foreach ($all_products as $p) {
        $products_indexed[$p['id']] = $p;
    }

    // 1. Get detailed breakdown first (needed for constraints & subtotal)
    $cart_details = calculateCartDetails($cart, $products_indexed);

    // 2. Calculate Subtotal from details
    $subtotal = 0;
    foreach ($cart_details as $item) {
        $subtotal += $item['final_total'];
    }

    // 3. Calculate Shipping Constraints
    $constraints = calculateCartShippingConstraints($cart_details);

    // 4. Validate and Auto-Correct Shipping Method
    $current_method = $_SESSION['shipping_method'] ?? 'standard';
    $validated_method = validateShippingMethod($current_method, $constraints);
    $_SESSION['shipping_method'] = $validated_method; // Persist correction

    // 5. Calculate Shipping Cost
    $shipping_cost = (count($cart) > 0) ? calculateShippingCost($validated_method, $subtotal) : 0;

    // 6. Recalculate Tax and Grand Total
    $totals = calculateCheckoutTotals($subtotal, $shipping_cost);

    // 7. Return updated shipping options (costs may vary by subtotal)
    $shipping_count = count($cart);
    $shipping_options = [
        'standard' => ($shipping_count > 0) ? calculateShippingCost('standard', $subtotal) : 0,
        'express' => ($shipping_count > 0) ? calculateShippingCost('express', $subtotal) : 0,
        'white-glove' => ($shipping_count > 0) ? calculateShippingCost('white-glove', $subtotal) : 0,
        'freight' => ($shipping_count > 0) ? calculateShippingCost('freight', $subtotal) : 0
    ];

    // Send everything back so JS can just "plug it in" to the HTML
    echo json_encode([
        'success' => true,
        'totals' => [
            'subtotal' => '$' . number_format($totals['subtotal'], 2),
            'shipping' => '$' . number_format($totals['shipping'], 2),
            'promo_discount' => isset($totals['promo_discount']) ? '-$' . number_format($totals['promo_discount'], 2) : '$0.00',
            'tax' => '$' . number_format($totals['tax'], 2),
            'grandTotal' => '$' . number_format($totals['total'], 2)
        ],
        'cartItems' => $cart_details,
        'shippingOptions' => [
            'standard' => '$' . number_format($shipping_options['standard'], 2),
            'express' => '$' . number_format($shipping_options['express'], 2),
            'white-glove' => '$' . number_format($shipping_options['white-glove'], 2),
            'freight' => '$' . number_format($shipping_options['freight'], 2)
        ],
        'shippingConstraints' => $constraints // NEW
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Session not active']);
}