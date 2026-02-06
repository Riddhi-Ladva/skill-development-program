<?php
require_once dirname(__DIR__) . '/bootstrap/session.php';
require_once ROOT_PATH . '/includes/db_functions.php';
require_once ROOT_PATH . '/includes/cart/services.php';
require_once ROOT_PATH . '/includes/shipping/services.php';
require_once ROOT_PATH . '/includes/tax/services.php';

// Fetch products and brands from DB
$all_products = get_products([]);
$products = [];
foreach ($all_products as $p) {
    $products[$p['id']] = $p;
}
$brands = get_all_brands();

// ENFORCE AUTH: REMOVED to allow guest cart
// require_once dirname(__DIR__) . '/auth/guard.php';
// auth_guard();

$cart_items = [];
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_items = get_cart_items_db($user_id);
} else {
    // Guest User -> Session Source
    $cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
}
$total_items = array_sum($cart_items);

$cart_details = calculateCartDetails($cart_items, $products);
$shipping_constraints = calculateCartShippingConstraints($cart_details);
$requires_freight = $shipping_constraints['requires_freight'];

$subtotal = 0;
foreach ($cart_details as $item) {
    $subtotal += $item['final_total'];
}

$shipping_method = isset($_SESSION['shipping_method']) ? $_SESSION['shipping_method'] : 'standard';
$shipping = calculateShippingCost($shipping_method, $subtotal);

$totals = calculateCheckoutTotals($subtotal, $shipping);

$subtotal = $totals['subtotal'];
$promo_discount = $totals['promo_discount'] ?? 0;
$shipping = $totals['shipping'];
$tax = $totals['tax'];
$order_total = $totals['total'];

// Dynamic Shipping Options
$shipping_methods = get_active_shipping_methods();
$shipping_options = [];
foreach ($shipping_methods as $method) {
    $code = $method['code'];
    $cost = calculateShippingCost($code, $subtotal);

    // Time estimates logic (could be DB driven eventually, but rule-based for now)
    $time_estimate = '5–7 business days';
    if ($code === 'express')
        $time_estimate = '2–3 business days';
    if ($code === 'white-glove')
        $time_estimate = '7–10 business days';
    if ($code === 'freight')
        $time_estimate = '10–14 business days';

    $shipping_options[] = [
        'code' => $code,
        'title' => $method['title'],
        'cost' => $cost,
        'time' => $time_estimate
    ];
}

// Payment Methods
$payment_methods = get_active_payment_methods();

// Recommended Products
$recommended_products = get_featured_products(3);
?>