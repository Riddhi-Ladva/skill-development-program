<?php
require_once '../includes/bootstrap/session.php';
require_once ROOT_PATH . '/data/products.php';
require_once ROOT_PATH . '/data/brands.php';
require_once ROOT_PATH . '/includes/cart/services.php';
require_once ROOT_PATH . '/includes/shipping/services.php';
require_once ROOT_PATH . '/includes/tax/services.php';

$cart_items = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];
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