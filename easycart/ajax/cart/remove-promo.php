<?php
/**
 * AJAX Endpoint: Remove Promo Code
 * 
 * Purpose: Removes the promo code from session.
 * Side Effect: Implicitly restores quantity-based discounts via services.php logic.
 */

ob_start();

require_once __DIR__ . '/../../includes/bootstrap/session.php';
require_once __DIR__ . '/../../includes/auth/guard.php';

// Protect endpoint
ajax_auth_guard();
require_once ROOT_PATH . '/data/products.php';
// require_once ROOT_PATH . '/data/promocodes.php'; // Not needed for removal
require_once ROOT_PATH . '/includes/cart/services.php';
require_once ROOT_PATH . '/includes/shipping/services.php';
require_once ROOT_PATH . '/includes/tax/services.php';

ob_end_clean();

header('Content-Type: application/json');

if (isset($_SESSION['promo_code'])) {
    unset($_SESSION['promo_code']);
    unset($_SESSION['promo_value']);

    // PERSIST REMOVAL TO DB
    update_cart_promo_db($_SESSION['user_id'], null);

    // Recalculate everything
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
        'message' => "Promo code removed.",
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
    echo json_encode(['success' => false, 'message' => 'No promo code active']);
}
