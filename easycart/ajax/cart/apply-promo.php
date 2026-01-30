<?php
/**
 * AJAX Endpoint: Apply Promo Code
 * 
 * Purpose: Validates and applies a promo code to the session.
 * Exclusivity: Setting a promo code will implicitly disable quantity discounts via services.php logic.
 */

ob_start();

require_once __DIR__ . '/../../includes/bootstrap/session.php';
require_once ROOT_PATH . '/data/products.php';
require_once ROOT_PATH . '/data/promocodes.php'; // NEW data source
require_once ROOT_PATH . '/includes/cart/services.php';
require_once ROOT_PATH . '/includes/shipping/services.php';
require_once ROOT_PATH . '/includes/tax/services.php';

ob_end_clean();

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['code'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No code provided']);
    exit;
}

$code = strtoupper(trim($input['code']));

if (array_key_exists($code, $promocodes)) {
    // Valid Code
    $_SESSION['promo_code'] = $code;
    $_SESSION['promo_value'] = $promocodes[$code];

    // Recalculate everything
    $subtotal = calculateSubtotal($_SESSION['cart'], $products);

    $shipping_method = $_SESSION['shipping_method'] ?? 'standard';
    // Recalculate shipping based on new subtotal (though subtotal shouldn't change, but good practice)
    $shipping_cost = (count($_SESSION['cart']) > 0) ? calculateShippingCost($shipping_method, $subtotal) : 0;

    $totals = calculateCheckoutTotals($subtotal, $shipping_cost);

    // Recalculate items to get them without quantity discounts
    $cart_details = calculateCartDetails($_SESSION['cart'], $products);

    echo json_encode([
        'success' => true,
        'message' => "Promo code $code applied!",
        'appliedCode' => $code,
        'totals' => [
            'subtotal' => '$' . number_format($totals['subtotal'], 2),
            'shipping' => '$' . number_format($totals['shipping'], 2),
            'promo_discount' => '-$' . number_format($totals['promo_discount'], 2),
            'tax' => '$' . number_format($totals['tax'], 2),
            'grandTotal' => '$' . number_format($totals['total'], 2)
        ],
        'cartItems' => $cart_details // Frontend needs this to remove "Discount (N%)" labels
    ]);
} else {
    // Invalid Code
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid promo code']);
}
