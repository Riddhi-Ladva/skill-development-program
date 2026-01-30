<?php
/**
 * AJAX Endpoint: Remove Promo Code
 * 
 * Purpose: Removes the promo code from session.
 * Side Effect: Implicitly restores quantity-based discounts via services.php logic.
 */

ob_start();

require_once __DIR__ . '/../../includes/bootstrap/session.php';
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

    // Recalculate everything
    $subtotal = calculateSubtotal($_SESSION['cart'], $products);

    $shipping_method = $_SESSION['shipping_method'] ?? 'standard';
    $shipping_cost = (count($_SESSION['cart']) > 0) ? calculateShippingCost($shipping_method, $subtotal) : 0;

    $totals = calculateCheckoutTotals($subtotal, $shipping_cost);

    // Recalculate items to restore quantity discounts
    $cart_details = calculateCartDetails($_SESSION['cart'], $products);

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
        'cartItems' => $cart_details // Frontend needs this to restore "Discount (N%)" labels
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'No promo code active']);
}
