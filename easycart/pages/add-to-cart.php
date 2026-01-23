<?php
require_once '../includes/session.php';
require_once '../data/products.php';

if (isset($_POST['product_id'])) {
    $product_id = (int) $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

    if (isset($products[$product_id])) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] = $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
    }
}

// Redirect back with success flag if came from product detail
if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'product-detail.php') !== false) {
    $separator = (strpos($_SERVER['HTTP_REFERER'], '?') !== false) ? '&' : '?';
    header('Location: ' . $_SERVER['HTTP_REFERER'] . $separator . 'added=1');
} else {
    header('Location: cart.php?added=1');
}
exit;
?>