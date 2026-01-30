<?php
declare(strict_types=1);

// Step 1: Create array
$numbers = [1, 3, 5, 7];

// Step 2: Check if 5 exists
if (in_array(5, $numbers)) {
    echo "5 exists in array\n";
} else {
    echo "5 does not exist\n";
}

// Step 3: Add a number
array_push($numbers, 9);

// Step 4: Merge with another array
$moreNumbers = [11, 13];
$mergedArray = array_merge($numbers, $moreNumbers);

// Print merged array
print_r($mergedArray);
?>
