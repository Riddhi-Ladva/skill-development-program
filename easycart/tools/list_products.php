<?php
// tools/list_products.php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/includes/db_functions.php';
$p = get_products([]);
foreach ($p as $x) {
    echo "ID: " . $x['id'] . " | Price: $" . $x['price'] . "\n";
}
?>