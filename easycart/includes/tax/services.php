<?php
/**
 * Tax Calculation Service
 *
 * Purpose: Centralized location for tax rate definition and calculation.
 * Logic: Applies a flat tax rate to the taxable total (Subtotal + Shipping).
 */

/**
 * Calculates tax based on the total taxable amount.
 *
 * @param float $subtotal Items total
 * @param float $shipping_cost Shipping cost
 * @return float Calculated tax amount
 */
function calculateTax($subtotal, $shipping_cost)
{
    // Math: 18% of (Subtotal + Shipping)
    return ($subtotal + $shipping_cost) * 0.18;
}