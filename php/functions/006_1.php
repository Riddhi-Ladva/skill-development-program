<?php
declare(strict_types=1);

// Step 1: Create string
$text = " Hello World ";

// Step 2: Trim spaces
$trimmed = trim($text);

// Step 3: Convert to lowercase
$lower = strtolower($trimmed);

// Step 4: Replace word
$result = str_replace("world", "php", $lower);

echo $result;
?>
