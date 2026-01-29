<?php
/**
 * MY STUDY NOTES: Cart Calculations
 * 
 * What is this? -> This is where the "math" for the cart lives.
 * Why modular? -> Instead of having math scattered everywhere, I put it here so 
 * if a price is wrong, I only have ONE place to check.
 * 
 * Core Logic:
 * 1. calculateSubtotal: Loop through the session cart and multiply qty by price.
 * 2. calculateCheckoutTotals: The "Big Boss" function that brings subtotal, 
 *    shipping, and tax together.
 */

/**
 * How it works:
 * It takes the cart from the SESSION (which is just IDs and Quantities)
 * and uses the $products array to look up the actual prices.
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
 * Why this function exists:
 * On the checkout page or cart summary, I need the final number.
 * This function handles the "Chain Reaction":
 * Subtotal -> Shipping -> Tax -> Grand Total.
 */
function calculateCheckoutTotals($subtotal, $shipping_cost)
{
    // Note to self: Tax depends on (Subtotal + Shipping), 
    // so I MUST pass both into calculateTax.

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
