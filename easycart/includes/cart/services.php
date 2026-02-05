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
/**
 * Calculates current subtotal based on session quantities and product prices.
 * NOW INCLUDES QUANTITY-BASED DISCOUNTS.
 *
 * @param array $cart_items Session cart array (ID => Quantity)
 * @param array $products Master product data source
 * @return float Total value of items after item-level discounts
 */
function calculateSubtotal($cart_items, $products)
{
    $details = calculateCartDetails($cart_items, $products);

    // Sum up the 'final_total' from each line item
    $subtotal = 0;
    foreach ($details as $item) {
        $subtotal += $item['final_total'];
    }

    return $subtotal;
}

/**
 * Calculates detailed breakdown for each item in the cart, including discounts.
 *
 * @param array $cart_items Session cart array
 * @param array $products Master product data
 * @return array Detailed list of items with price, discount, and final total
 */
function calculateCartDetails($cart_items, $products)
{
    $details = [];
    if (empty($cart_items)) {
        return $details;
    }

    // EXCLUSIVITY RULE: Check if a global promo code is active
    // If active, item-level quantity discounts are strictly DISABLED.
    $promo_active = isset($_SESSION['promo_code']);

    foreach ($cart_items as $id => $quantity) {
        if (isset($products[$id])) {
            $price = $products[$id]['price'];
            $original_total = $price * $quantity;

            // Logic: If promo is active, Quantity Discount = 0
            if ($promo_active) {
                $discount_percent = 0;
            } else {
                // Default: N quantity = N% discount
                $discount_percent = $quantity;
            }

            $discount_amount = $original_total * ($discount_percent / 100);
            $final_total = $original_total - $discount_amount;

            // NEW: Shipping Eligibility Logic (Refactored to shared helper)
            if (!function_exists('getShippingEligibility')) {
                require_once dirname(__DIR__) . '/shipping/services.php';
            }
            $shipping_eligibility = getShippingEligibility($price);

            $details[$id] = [
                'name' => $products[$id]['name'],
                'price' => $price,
                'quantity' => $quantity,
                'original_total' => $original_total,
                'discount_percent' => $discount_percent,
                'discount_amount' => $discount_amount,
                'final_total' => $final_total,
                'shipping_eligibility' => $shipping_eligibility // NEW field
            ];
        }
    }
    return $details;
}

/**
 * Calculates cart-wide shipping constraints based on items and subtotal (considering promos).
 *
 * @param array $cart_details Result from calculateCartDetails
 * @return array Constraints flags
 */
function calculateCartShippingConstraints($cart_details)
{
    $requires_freight = false;
    $raw_subtotal = 0;

    foreach ($cart_details as $item) {
        $raw_subtotal += $item['final_total'];
        if ($item['shipping_eligibility']['requires_freight']) {
            $requires_freight = true;
        }
    }

    // Calculate Promo Discount to get Effective Subtotal
    $promo_discount = 0;
    if (isset($_SESSION['promo_code']) && isset($_SESSION['promo_value'])) {
        $promo_value = $_SESSION['promo_value'];
        $promo_discount = $raw_subtotal * ($promo_value / 100);
    }

    $effective_subtotal = $raw_subtotal - $promo_discount;

    // Constraint: If EFFECTIVE subtotal > 300, Freight is legally required
    if ($effective_subtotal > 300) {
        $requires_freight = true;
    }

    return [
        'requires_freight' => $requires_freight,
        'effective_subtotal' => $effective_subtotal // Useful for debugging or other logic
    ];
}

/**
 * NEW: Centralized Cart Count Resolver
 *
 * Single source of truth for the cart badge count.
 * - Logged-in: Count from DB
 * - Guest: Count from Session
 *
 * @return int Total number of items
 */
function getCartCount()
{
    // 1. Logged-in User -> DB Source
    if (isset($_SESSION['user_id'])) {
        // Ensure DB functions are loaded
        if (!function_exists('get_cart_items_db')) {
            require_once dirname(__DIR__) . '/db_functions.php';
        }
        $cart_items = get_cart_items_db($_SESSION['user_id']);
        return array_sum($cart_items);
    }

    // 2. Guest User -> Session Source
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        return array_sum($_SESSION['cart']);
    }

    return 0;
}

/**
 * Validates and possibly auto-corrects the selected shipping method.
 *
 * @param string $current_method The currently selected method ID
 * @param array $constraints The constraints array from calculateCartShippingConstraints
 * @return string The validated (or corrected) shipping method ID
 */
function validateShippingMethod($current_method, $constraints)
{
    if ($constraints['requires_freight']) {
        // If freight is required, we CANNOT use standard or express
        if ($current_method === 'standard' || $current_method === 'express') {
            return 'freight'; // Auto-upgrade to freight
        }
        // White-glove is allowed as a premium freight option
        return $current_method;
    } else {
        // If freight is NOT required (subtotal <= 300 AND no heavy items)
        // We generally shouldn't force freight, but if they selected it, maybe they want it?
        // REQUIREMENT: "If Freight is NOT required... Disable: Freight Shipping, White Glove Delivery"
        // So we must downgrade if they have a heavy method selected.

        if ($current_method === 'freight' || $current_method === 'white-glove') {
            return 'standard'; // Auto-downgrade to standard
        }

        return $current_method;
    }
}

/**
 * Aggregates all cost components into a final checkout summary.
 *
 * @param float $subtotal Calculated items total
 * @param float $shipping_cost Selected shipping method cost
 * @return array Associative array containing 'subtotal', 'shipping', 'tax', 'total', and 'promo_discount'
 */
function calculateCheckoutTotals($subtotal, $shipping_cost)
{
    // Safety check: make sure the tax math file is actually loaded
    if (!function_exists('calculateTax')) {
        require_once dirname(__DIR__) . '/tax/services.php';
    }

    // 1. Calculate Promo Discount if active
    $promo_discount = 0;
    if (isset($_SESSION['promo_code']) && isset($_SESSION['promo_value'])) {
        $promo_value = $_SESSION['promo_value'];
        // Discount is percentage of the subtotal
        $promo_discount = $subtotal * ($promo_value / 100);
    }

    // 2. Tax is calculated on (Subtotal - Promo + Shipping)
    $taxable_amount = max(0, $subtotal - $promo_discount);
    $tax = calculateTax($taxable_amount, $shipping_cost);

    // 3. Final Total
    $total = $subtotal - $promo_discount + $shipping_cost + $tax;

    // Return an array so the page can easily grab whatever piece it needs ($totals['tax'], etc.)
    return [
        'subtotal' => $subtotal,
        'shipping' => $shipping_cost,
        'promo_discount' => $promo_discount, // NEW field
        'tax' => $tax,
        'total' => $total
    ];
}