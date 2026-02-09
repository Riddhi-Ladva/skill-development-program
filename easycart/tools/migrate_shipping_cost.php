<?php
require_once dirname(__DIR__) . '/includes/db_functions.php';
$pdo = getDbConnection();

echo "Checking if 'cost' column exists...\n";
$stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'shipping_methods' AND column_name = 'cost'");
if ($stmt->fetch()) {
    echo "Column 'cost' already exists.\n";
} else {
    echo "Adding 'cost' column...\n";
    $pdo->exec("ALTER TABLE shipping_methods ADD COLUMN cost DECIMAL(10,2) DEFAULT 0.00");
    echo "Column added.\n";
}

echo "Updating base costs...\n";
// Standard: Fixed $40
$pdo->prepare("UPDATE shipping_methods SET cost = 40.00 WHERE code = 'standard'")->execute();

// Freight: Min $200
$pdo->prepare("UPDATE shipping_methods SET cost = 200.00 WHERE code = 'freight'")->execute();

// Express & White Glove: calculated dynamically, set base to 0 for now (or could be min?)
$pdo->prepare("UPDATE shipping_methods SET cost = 0.00 WHERE code = 'express'")->execute();
$pdo->prepare("UPDATE shipping_methods SET cost = 0.00 WHERE code = 'white-glove'")->execute();

echo "Migration complete.\n";
?>