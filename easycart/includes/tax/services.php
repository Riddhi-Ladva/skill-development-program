<?php
/**
 * MY STUDY NOTES: Tax Calculation
 * 
 * Why 18%? -> This is the standard tax rate for this project.
 * 
 * Important Note: Tax is NOT just on the products. It's on 
 * (Products + Shipping). This means if the user picks a more 
 * expensive shipping, the tax goes up too!
 */

/**
 * How it works:
 * It takes digits for subtotal and shipping, adds them, 
 * and multiplies by 0.18.
 */
function calculateTax($subtotal, $shipping_cost)
{
    // Math: 18% of (Subtotal + Shipping)
    return ($subtotal + $shipping_cost) * 0.18;
}
