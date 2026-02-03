<?php
require_once '../includes/bootstrap/session.php';
require_once ROOT_PATH . '/data/products.php';
require_once ROOT_PATH . '/includes/cart/services.php';
require_once ROOT_PATH . '/includes/shipping/services.php';
require_once ROOT_PATH . '/includes/tax/services.php';

$cart_items = isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? $_SESSION['cart'] : [];

if (empty($cart_items)) {
    header('Location: cart.php');
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