<?php
require_once dirname(__DIR__) . '/includes/db_functions.php';
$pdo = getDbConnection();
$stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'shipping_methods'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>