<?php
/**
 * Index Page Logic
 * 
 * Handles variable preparation for the home page.
 */

// Load the bootstrap file to initialize session and config
require_once __DIR__ . '/../bootstrap/session.php';
require_once __DIR__ . '/../db_functions.php';

// Load brands data
$brands = get_all_brands();

// Load categories
$categories = get_all_categories();

// Load featured products
$featured_products = get_featured_products(4);

// Define page specific variables if any
$title = "EasyCart - Home";
