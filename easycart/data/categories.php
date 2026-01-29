<?php
/**
 * Categories Data Source
 * 
 * Purpose: Defines the available product categories for the store.
 * Why: Used to populate navigation menus and filter products.
 * Structure: Associative array indexed by unique category slug.
 */
$categories = [
    'electronics' => [
        'id' => 'electronics',
        'name' => 'Electronics',
        'description' => 'Latest gadgets and tech',
        'image' => '', // placeholders if needed
    ],
    'clothing' => [
        'id' => 'clothing',
        'name' => 'Clothing',
        'description' => 'Fashion for everyone',
        'image' => '',
    ],
    'home' => [
        'id' => 'home',
        'name' => 'Home & Garden',
        'description' => 'Make your space beautiful',
        'image' => '',
    ],
    'sports' => [
        'id' => 'sports',
        'name' => 'Sports & Outdoors',
        'description' => 'Gear for active lifestyle',
        'image' => '',
    ]
];
