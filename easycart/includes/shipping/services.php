<?php
/**
 * Shipping Cost Service
 *
 * Purpose: Determines the shipping cost based on selected method and order value.
 * Logic: Implements tiered pricing rules where some methods depend on a percentage
 * of the subtotal with caps or minimums.
 *
 * Note: Session state is not modified here; this is a pure calculation utility.
 */

/**
 * Calculates shipping cost for a given method type.
 *
 * @param string $type The shipping method key (standard, express, etc)
 * @param float $subtotal The order subtotal to apply percentage rules to
 * @return float Calculated shipping cost
 */
function calculateShippingCost($type, $subtotal)
{
    switch ($type) {
        case 'standard':
            // Simple: Always $40, no matter what.
            return 40;

        case 'express':
            // Rule: 10% of subtotal, but we "cap" it at $80 so it's not too expensive.
            return min(80, $subtotal * 0.10);

        case 'white-glove':
            // Rule: 5% of subtotal, capped at $150.
// Better for very expensive orders.
            return min(150, $subtotal * 0.05);

        case 'freight':
            // Rule: 3% of subtotal, minimum $200
            return max(200, $subtotal * 0.03);

        default:
            // Fallback: Charge standard rate for unrecognized types
            return 40;
    }
}