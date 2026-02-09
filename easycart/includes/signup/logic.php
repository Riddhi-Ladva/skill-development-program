<?php
require_once __DIR__ . '/../bootstrap/session.php';
require_once ROOT_PATH . '/config/db.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first-name'] ?? '');
    $lastName = trim($_POST['last-name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm-password'] ?? '';

    // Validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        $errors[] = "All required fields must be filled.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } elseif ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }

    if (empty($errors)) {
        try {
            $pdo = getDbConnection();

            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                $errors[] = "An account with this email already exists.";
            } else {
                // Insert new user
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO users (email, password_hash, first_name, last_name, created_at, updated_at)
                    VALUES (:email, :password_hash, :first_name, :last_name, NOW(), NOW())
                ");
                $stmt->execute([
                    'email' => $email,
                    'password_hash' => $passwordHash,
                    'first_name' => $firstName,
                    'last_name' => $lastName
                ]);

                // Success! Redirect to login
                header('Location: ' . url('login?signup=success'));
                exit;
            }
        } catch (PDOException $e) {
            error_log("Signup error: " . $e->getMessage());
            $errors[] = "An error occurred during signup. Please try again later.";
        }
    }
}
?>