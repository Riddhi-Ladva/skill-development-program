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

// ENFORCE AUTH: Cart is for logged-in users only
require_once dirname(__DIR__) . '/auth/guard.php';
auth_guard();

$user_id = $_SESSION['user_id'];
$cart_items = get_cart_items_db($user_id);
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
?>