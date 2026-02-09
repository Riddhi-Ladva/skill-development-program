<?php
/**
 * Dashboard Services
 * 
 * Handles data retrieval for the user dashboard.
 */

require_once __DIR__ . '/../../config/db.php';

function get_user_dashboard_metrics($user_id)
{
    if (!$user_id)
        return null;

    $pdo = getDbConnection();
    $metrics = [
        'email' => '',
        'display_name' => 'User',
        'total_orders' => 0,
        'total_spent' => 0.00
    ];

    try {
        // 1. User Info
        $stmt = $pdo->prepare("SELECT email FROM users WHERE id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_data) {
            $metrics['email'] = $user_data['email'];
            $metrics['display_name'] = ucfirst(explode('@', $user_data['email'])[0]);
        }

        // 2. Total Orders
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM sales_order WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        $metrics['total_orders'] = (int) $stmt->fetchColumn();

        // 3. Total Spent
        $stmt = $pdo->prepare("SELECT SUM(grand_total) FROM sales_order WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        $metrics['total_spent'] = (float) ($stmt->fetchColumn() ?: 0);

        return $metrics;

    } catch (PDOException $e) {
        error_log("Dashboard Service Error: " . $e->getMessage());
        return null;
    }
}
