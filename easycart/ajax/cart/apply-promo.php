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

    // VALIDATION PIPELINE
    // 1. Get detailed breakdown first
    $cart_details = calculateCartDetails($_SESSION['cart'], $products);

    // 2. Calculate Raw Subtotal from details
    $subtotal = 0;
    foreach ($cart_details as $item) {
        $subtotal += $item['final_total'];
    }

    // 3. Calculate Shipping Constraints (Uses Effective Subtotal internally)
    $constraints = calculateCartShippingConstraints($cart_details);

    // 4. Validate and Auto-Correct Shipping Method
    $current_method = $_SESSION['shipping_method'] ?? 'standard';
    $validated_method = validateShippingMethod($current_method, $constraints);
    $_SESSION['shipping_method'] = $validated_method; // Persist correction

    // 5. Calculate Shipping Cost
    $shipping_cost = (count($_SESSION['cart']) > 0) ? calculateShippingCost($validated_method, $subtotal) : 0;

    // 6. Calculate Finals
    $totals = calculateCheckoutTotals($subtotal, $shipping_cost);

    // 7. Get Shipping Options for UI
    $shipping_options = [
        'standard' => (count($_SESSION['cart']) > 0) ? calculateShippingCost('standard', $subtotal) : 0,
        'express' => (count($_SESSION['cart']) > 0) ? calculateShippingCost('express', $subtotal) : 0,
        'white-glove' => (count($_SESSION['cart']) > 0) ? calculateShippingCost('white-glove', $subtotal) : 0,
        'freight' => (count($_SESSION['cart']) > 0) ? calculateShippingCost('freight', $subtotal) : 0
    ];

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
    // Invalid Code
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid promo code']);
}
