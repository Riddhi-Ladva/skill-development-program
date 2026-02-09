<?php
/**
 * Auth Services
 * 
 * Handles user profile data and updates.
 */

require_once __DIR__ . '/../../config/db.php';

function get_user_profile($user_id)
{
    $pdo = getDbConnection();
    try {
        $stmt = $pdo->prepare("SELECT email, first_name, last_name, phone FROM users WHERE id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Profile Fetch Error: " . $e->getMessage());
        return null;
    }
}

function is_email_taken($email, $exclude_user_id)
{
    $pdo = getDbConnection();
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :user_id");
        $stmt->execute(['email' => $email, 'user_id' => $exclude_user_id]);
        return (bool) $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Email Check Error: " . $e->getMessage());
        return true; // Fail safe
    }
}

function update_user_profile($user_id, $firstName, $lastName, $email, $phone)
{
    $pdo = getDbConnection();
    try {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET first_name = :first_name, 
                last_name = :last_name, 
                email = :email, 
                phone = :phone,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :user_id
        ");
        return $stmt->execute([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'user_id' => $user_id
        ]);
    } catch (PDOException $e) {
        error_log("Profile Update Error: " . $e->getMessage());
        return false;
    }
}
