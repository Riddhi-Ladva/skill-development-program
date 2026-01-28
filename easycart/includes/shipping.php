<?php
/**
 * Shipping Cost Calculation
 * 
 * Provides reusable function to calculate shipping costs based on method type and cart subtotal.
 * This is the single source of truth for all shipping calculations.
 */

/**
 * Calculate shipping cost based on shipping type and cart subtotal
 * 
 * Business Rules:
 * - Standard Shipping: Flat $40
 * - Express Shipping: $80 OR 10% of subtotal (whichever is lower)
 * - White Glove Delivery: $150 OR 5% of subtotal (whichever is lower)
 * - Freight Shipping: 3% of subtotal, minimum $200
 * 
 * @param string $type Shipping method type (standard, express, white-glove, freight)
 * @param float $subtotal Cart subtotal amount
 * @return float Calculated shipping cost
 */
function calculateShippingCost($type, $subtotal)
{
    switch ($type) {
        case 'standard':
            // Flat rate of $40
            return 40;

        case 'express':
            // $80 OR 10% of subtotal, whichever is LOWER
            return min(80, $subtotal * 0.10);

        case 'white-glove':
            // $150 OR 5% of subtotal, whichever is LOWER
            return min(150, $subtotal * 0.05);

        case 'freight':
            // 3% of subtotal, MINIMUM $200
            return max(200, $subtotal * 0.03);

        default:
            // Default to standard shipping
            return 40;
    }
}

/**
 * Calculate checkout totals
 * 
 * Business Rules:
 * - Subtotal: Sum of (price × quantity) for all cart items
 * - Shipping: Order-level shipping cost
 * - Tax: 18% on (Subtotal + Shipping)
 * - Total: Subtotal + Shipping + Tax
 * 
 * @param array $cart_items Cart items from session (product_id => quantity)
 * @param array $products Product data array
 * @param float $shipping_cost Shipping cost
 * @return array Associative array with subtotal, shipping, tax, total
 */
function calculateCheckoutTotals($cart_items, $products, $shipping_cost)
{
    $subtotal = 0;

    // Calculate subtotal from cart items
    foreach ($cart_items as $id => $quantity) {
        if (isset($products[$id])) {
            $subtotal += $products[$id]['price'] * $quantity;
        }
    }

    // Tax is 18% on (Subtotal + Shipping)
    $tax = ($subtotal + $shipping_cost) * 0.18;

    // Total = Subtotal + Shipping + Tax
    $total = $subtotal + $shipping_cost + $tax;

    return [
        'subtotal' => $subtotal,
        'shipping' => $shipping_cost,
        'tax' => $tax,
        'total' => $total
    ];
}
