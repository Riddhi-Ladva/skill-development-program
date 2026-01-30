<?php

declare(strict_types=1);

function calculateTotal(float $price, int $qty): float {
    return $price * $qty;
}

echo calculateTotal(450.50, 2);
?>