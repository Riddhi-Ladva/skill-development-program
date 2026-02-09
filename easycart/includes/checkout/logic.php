<?php
require_once dirname(__DIR__) . '/bootstrap/session.php';
require_once ROOT_PATH . '/includes/db-functions.php';
require_once ROOT_PATH . '/includes/cart/services.php';
require_once ROOT_PATH . '/includes/shipping/services.php';
require_once ROOT_PATH . '/includes/tax/services.php';

// Fetch products from DB
$all_products = get_products([]);
$products = [];
foreach ($all_products as $p) {
    $products[$p['id']] = $p;
}

// ENFORCE AUTH: Checkout is for logged-in users only
require_once dirname(__DIR__) . '/auth/guard.php';
auth_guard();

$user_id = $_SESSION['user_id'];
$cart_items = get_cart_items_db($user_id);

if (empty($cart_items)) {
    header('Location: ' . url('cart'));
    exit;
}

$total_items = array_sum($cart_items);

$subtotal = calculateSubtotal($cart_items, $products);
$shipping_method = isset($_SESSION['shipping_method']) ? $_SESSION['shipping_method'] : 'standard';
$shipping = calculateShippingCost($shipping_method, $subtotal);
$totals = calculateCheckoutTotals($subtotal, $shipping);
$promo_discount = $totals['promo_discount'] ?? 0;
$subtotal = $totals['subtotal'];
$tax = $totals['tax'];
$order_total = $totals['total'];
?>