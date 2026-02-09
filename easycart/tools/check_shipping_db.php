<?php
require_once dirname(__DIR__) . '/includes/db_functions.php';
$pdo = getDbConnection();
$stmt = $pdo->query("SELECT * FROM shipping_methods LIMIT 1");
print_r($stmt->fetch(PDO::FETCH_ASSOC));
?>