<?php
/**
 * Cart Calculation Service
 *
 * Purpose: Centralized business logic for processing cart value.
 * Responsibility: Calculates line item totals and aggregates the final checkout subtotal.
 * Used By: Cart page component and AJAX services.
 */

/**
 * Calculates current subtotal based on session quantities and product prices.
 *
 * @param array $cart_items Session cart array (ID => Quantity)
 * @param array $products Master product data source
 * @return float Total value of items before shipping/tax
 */
function calculateSubtotal($cart_items, $products)
{
    if (empty($cart_items)) {
        return 0;
    }

    $subtotal = 0;
    foreach ($cart_items as $id => $quantity) {
        if (isset($products[$id])) {
            // Future reminder: $products[$id]['price'] comes from data/products.php
            $subtotal += $products[$id]['price'] * $quantity;
        }
    }
    return $subtotal;
}

/**
 * Aggregates all cost components into a final checkout summary.
 *
 * @param float $subtotal Calculated items total
 * @param float $shipping_cost Selected shipping method cost
 * @return array Associative array containing 'subtotal', 'shipping', 'tax', and 'total'
 */
function calculateCheckoutTotals($subtotal, $shipping_cost)
{
    // Tax is calculated on taxable amount (Subtotal + Shipping)
// dependency: tax/services.php containing calculateTax()

    // Safety check: make sure the tax math file is actually loaded
    if (!function_exists('calculateTax')) {
        require_once dirname(__DIR__) . '/tax/services.php';
    }

    $tax = calculateTax($subtotal, $shipping_cost);
    $total = $subtotal + $shipping_cost + $tax;

    // Return an array so the page can easily grab whatever piece it needs ($totals['tax'], etc.)
    return [
        'subtotal' => $subtotal,
        'shipping' => $shipping_cost,
        'tax' => $tax,
        'total' => $total
    ];
}