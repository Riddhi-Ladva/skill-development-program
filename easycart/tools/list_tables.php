<?php
require_once dirname(__DIR__) . '/includes/db_functions.php';
$pdo = getDbConnection();
$stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);
?>