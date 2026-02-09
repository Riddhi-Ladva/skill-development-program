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
 * Caches shipping methods to avoid repeated DB calls in same request
 */
function get_shipping_method_config($code)
{
    static $cache = [];
    if (isset($cache[$code]))
        return $cache[$code];

    $pdo = getDbConnection();
    // In a full implementation, we would store cost rules in DB. 
    // For now, we verify the method exists and is active.
    $stmt = $pdo->prepare("SELECT * FROM shipping_methods WHERE code = :code AND is_active = TRUE");
    $stmt->execute(['code' => $code]);
    $method = $stmt->fetch(PDO::FETCH_ASSOC);

    $cache[$code] = $method;
    return $method;
}

/**
 * Calculates shipping cost for a given method type.
 *
 * @param string $type The shipping method key (standard, express, etc)
 * @param float $subtotal The order subtotal to apply percentage rules to
 * @return float Calculated shipping cost
 */
function calculateShippingCost($type, $subtotal)
{
    // Verify method exists in DB
    $method = get_shipping_method_config($type);

    // Fallback if deleted or inactive
    if (!$method) {
        $type = 'standard';
    }

    switch ($type) {
        case 'standard':
            // Simple: Always use DB cost (Default 40)
            return (float) $method['cost'];

        case 'express':
            // Rule: 10% of subtotal, but we "cap" it at $80 so it's not too expensive.
            // Future: Could verify if DB has a cost and use it as base.
            return min(80, $subtotal * 0.10);

        case 'white-glove':
            // Rule: 5% of subtotal, capped at $150.
            return min(150, $subtotal * 0.05);

        case 'freight':
            // Rule: 3% of subtotal, minimum is DB cost (Default 200)
            return max((float) $method['cost'], $subtotal * 0.03);

        default:
            // Fallback: Charge standard rate/DB cost
            return (float) $method['cost'];
    }
}

/**
 * Determines shipping eligibility based on product price.
 * 
 * @param float $price Product price
 * @return array ['label' => string, 'requires_freight' => bool]
 */
function getShippingEligibility($price)
{
    // < 300: Express Available
    // >= 300: Freight Required
    $is_freight = $price >= 300;

    return $is_freight
        ? [
            'label' => 'Freight Required',
            'requires_freight' => true,
            'class' => 'shipping-freight',
            'icon' => '🚚'
        ]
        : [
            'label' => 'Express Available',
            'requires_freight' => false,
            'class' => 'shipping-express',
            'icon' => '⚡'
        ];
}