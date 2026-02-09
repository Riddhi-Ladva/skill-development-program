<?php
/**
* AJAX Endpoint: Remove from Cart
*
* Purpose: Removes a specific product from the cart session.
* Side Effects: Recalculates all cart totals (subtotal, shipping, tax) as removing an item affects all downstream
values.
* Output: Full updated cart summary JSON for UI refresh.
*/

// Start output buffering to prevent whitespace from includes breaking JSON
ob_start();

// Load bootstrap (session and config)
require_once __DIR__ . '/../../includes/bootstrap/session.php';
require_once ROOT_PATH . '/includes/db-functions.php';
require_once ROOT_PATH . '/includes/auth/guard.php';

// Protect endpoint: REMOVED for guest access
// ajax_auth_guard();
require_once ROOT_PATH . '/includes/cart/services.php';
require_once ROOT_PATH . '/includes/shipping/services.php';
require_once ROOT_PATH . '/includes/tax/services.php';

// Clear any buffered output
ob_end_clean();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the product ID to remove
    $data = json_decode(file_get_contents('php://input'), true);
    $productId = $data['product_id'] ?? null;

    if ($productId) {
        // SYNC WITH DB or Session
        if (isset($_SESSION['user_id'])) {
            remove_from_cart_db($_SESSION['user_id'], $productId);
        } else {
            if (isset($_SESSION['cart'][$productId])) {
                unset($_SESSION['cart'][$productId]);
            }
        }

        // Fetch current cart from DB or Session for calculations
        $cart = [];
        if (isset($_SESSION['user_id'])) {
            $cart = get_cart_items_db($_SESSION['user_id']);
        } else {
            $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
        }

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

        // Determine current total items for the badge
        $totalItems = getCartCount();

        // 3. Calculate Shipping Constraints
        $constraints = calculateCartShippingConstraints($cart_details);

        // 4. Validate and Auto-Correct Shipping Method
        $current_method = $_SESSION['shipping_method'] ?? 'standard';
        $validated_method = validateShippingMethod($current_method, $constraints);
        $_SESSION['shipping_method'] = $validated_method; // Persist correction

        $shipping_cost = ($subtotal > 0) ? calculateShippingCost($validated_method, $subtotal) : 0;

        // 6. Aggregate final checkout totals
        $totals = calculateCheckoutTotals($subtotal, $shipping_cost);

        // 7. Recalculate available shipping options based on new subtotal
        $shipping_options = [
            'standard' => ($subtotal > 0) ? calculateShippingCost('standard', $subtotal) : 0,
            'express' => ($subtotal > 0) ? calculateShippingCost('express', $subtotal) : 0,
            'white-glove' => ($subtotal > 0) ? calculateShippingCost('white-glove', $subtotal) : 0,
            'freight' => ($subtotal > 0) ? calculateShippingCost('freight', $subtotal) : 0
        ];

        // Send all the fresh math back to the UI
        echo json_encode([
            'success' => true,
            'totals' => [
                'subtotal' => '$' . number_format($totals['subtotal'], 2),
                'shipping' => '$' . number_format($totals['shipping'], 2),
                'promo_discount' => isset($totals['promo_discount']) ? '-$' . number_format($totals['promo_discount'], 2) : '$0.00',
                'tax' => '$' . number_format($totals['tax'], 2),
                'grandTotal' => '$' . number_format($totals['total'], 2),
                'totalItems' => $totalItems
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
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}