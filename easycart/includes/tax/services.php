<?php
/**
 * Tax Calculation Service
 *
 * Purpose: Centralized location for tax rate definition and calculation.
 * Logic: Applies a flat tax rate to the taxable total (Subtotal + Shipping).
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../db_functions.php';

/**
 * Fetch tax rate from DB (Simple Global Implementation)
 */
function get_tax_rate_db()
{
    static $rate = null;
    if ($rate !== null)
        return $rate;

    $pdo = getDbConnection();
    // Fetch global default rate
    $stmt = $pdo->prepare("SELECT percentage FROM tax_rates WHERE country_code = '*' LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $rate = $row ? ((float) $row['percentage'] / 100) : 0.0;
    return $rate;
}

/**
 * Calculates tax based on the total taxable amount.
 *
 * @param float $subtotal Items total
 * @param float $shipping_cost Shipping cost
 * @return float Calculated tax amount
 */
function calculateTax($subtotal, $shipping_cost)
{
    $rate = get_tax_rate_db();
    return ($subtotal + $shipping_cost) * $rate;
}