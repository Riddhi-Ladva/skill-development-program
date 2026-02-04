<?php
/**
 * Order Spending Data API
 * 
 * Responsibility: Fetches daily order totals for the logged-in user.
 * Returns: JSON array of objects {date: string, amount: number}
 */

require_once __DIR__ . '/../../includes/bootstrap/session.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    $pdo = getDbConnection();
    $user_id = $_SESSION['user_id'];

    // Query daily spending totals
    $stmt = $pdo->prepare("
        SELECT 
            created_at::date as date, 
            SUM(grand_total) as amount 
        FROM sales_order 
        WHERE user_id = :user_id 
        GROUP BY date 
        ORDER BY date ASC
    ");
    $stmt->execute(['user_id' => $user_id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ensure numeric values are floats
    foreach ($data as &$row) {
        $row['amount'] = (float) $row['amount'];
    }

    echo json_encode(['success' => true, 'data' => $data]);

} catch (PDOException $e) {
    error_log("Order Spending API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
