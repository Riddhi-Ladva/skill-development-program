<?php
/**
 * AJAX Endpoint: Get Cart HTML
 * 
 * Purpose: Returns the rendered HTML for the cart items and summary sections.
 * Used by frontend to update the UI without page reload.
 */

require_once __DIR__ . '/../../includes/bootstrap/config.php';
require_once __DIR__ . '/../../includes/bootstrap/session.php'; // logic.php needs session

// Reuse the exact same logic as the main Cart Page to ensure consistency
// This sets up $cart_items, $cart_details, $totals, $shipping_options, etc.
require_once ROOT_PATH . '/includes/cart/logic.php';

// Render Cart Items
ob_start();
include ROOT_PATH . '/includes/cart/components/cart-items.php';
$cart_html = ob_get_clean();

// Render Cart Summary
ob_start();
include ROOT_PATH . '/includes/cart/components/cart-summary.php';
$summary_html = ob_get_clean();

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'cartHtml' => $cart_html,
    'summaryHtml' => $summary_html,
    'totalItems' => $total_items
]);
