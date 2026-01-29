<?php
/**
 * MY STUDY NOTES: Shipping Rules
 * 
 * Why do we have this? -> Shipping isn't just one price. It changes based on 
 * what the user picks AND how much they spent.
 * 
 * Future Self Reminder: 
 * We NEVER store the shipping money in the session permanently because it 
 * needs to be recalculated if the user adds/removes items (since some rules 
 * depend on the % of subtotal).
 */

/**
 * How the math works:
 * This function is like a "Decision Tree". It looks at the $type 
 * (standard, express, etc.) and the $subtotal to pick the right price.
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
            // Rule: 3% of subtotal, but MUST be at least $200.
            // Used for heavy/bulk items.
            return max(200, $subtotal * 0.03);

        default:
            // Fallback: If something breaks, just charge the $40 standard rate.
            return 40;
    }
}
