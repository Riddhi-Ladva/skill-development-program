<?php
/**
 * AJAX Endpoint: Apply Promo Code
 * 
 * Purpose: Validates and applies a promo code to the session.
 * Exclusivity: Setting a promo code will implicitly disable quantity discounts via services.php logic.
 */

ob_start();

require_once __DIR__ . '/../../includes/bootstrap/session.php';
require_once ROOT_PATH . '/includes/db_functions.php';
require_once ROOT_PATH . '/includes/auth/guard.php';

// Protect endpoint: Logged-in users only
// Protect endpoint: Logged-in users only
ajax_auth_guard();
// Removed: require_once ROOT_PATH . '/data/promocodes.php'; // Old file-base logic
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

try {
    $pdo = getDbConnection();

    // Fetch Promo from DB
    $stmt = $pdo->prepare("SELECT * FROM promo_codes WHERE code = :code AND is_active = TRUE LIMIT 1");
    $stmt->execute(['code' => $code]);
    $promo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($promo) {
        // Valid Code
        $_SESSION['promo_code'] = $promo['code'];
        // Assume services.php expects percentage. If type is 'fixed', logic might need update.
        // For now, mapping 'discount_value' to 'promo_value'.
        $_SESSION['promo_value'] = (float) $promo['discount_value'];
        $_SESSION['promo_type'] = $promo['discount_type']; // Store type just in case services.php needs it later

        // PERSIST TO DB: Requirements state DB is single source of truth
        update_cart_promo_db($_SESSION['user_id'], $code);

        // SYNC WITH DB: Fetch current items
        $cart = get_cart_items_db($_SESSION['user_id']);

        // VALIDATION PIPELINE
        // Fetch all products for calculation
        $all_products = get_products([]);
        $products_indexed = [];
        foreach ($all_products as $p) {
            $products_indexed[$p['id']] = $p;
        }

        // 1. Get detailed breakdown first
        $cart_details = calculateCartDetails($cart, $products_indexed);

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
        $shipping_count = count($cart);
        $shipping_cost = ($shipping_count > 0) ? calculateShippingCost($validated_method, $subtotal) : 0;

        // 6. Calculate Finals
        $totals = calculateCheckoutTotals($subtotal, $shipping_cost);

        // 7. Get Shipping Options for UI
        $shipping_options = [
            'standard' => ($shipping_count > 0) ? calculateShippingCost('standard', $subtotal) : 0,
            'express' => ($shipping_count > 0) ? calculateShippingCost('express', $subtotal) : 0,
            'white-glove' => ($shipping_count > 0) ? calculateShippingCost('white-glove', $subtotal) : 0,
            'freight' => ($shipping_count > 0) ? calculateShippingCost('freight', $subtotal) : 0
        ];

        echo json_encode([
            'success' => true,
            'message' => "Promo code $code applied!",
            'appliedCode' => $code,
            // ... totals ...
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
            'shippingConstraints' => $constraints
        ]);

    } else {
        // Invalid Code
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid promo code']);
    }

} catch (Exception $e) {
    error_log("Promo Apply Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error', 'debug' => $e->getMessage()]);
}
