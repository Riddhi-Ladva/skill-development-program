<?php
require_once '../includes/session.php';
require_once '../data/products.php';

if (isset($_POST['product_id'])) {
    $product_id = (int) $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

    if (isset($products[$product_id])) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
    }
}

header('Location: cart.php');
exit;
?>