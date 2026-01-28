<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Initialize default shipping method
if (!isset($_SESSION['shipping_method'])) {
    $_SESSION['shipping_method'] = 'standard';
}
?>