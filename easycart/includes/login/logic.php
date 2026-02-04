<?php
require_once __DIR__ . '/../bootstrap/session.php';
require_once ROOT_PATH . '/config/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$errors = [];
$loginSuccessMessage = isset($_GET['signup']) && $_GET['signup'] === 'success' ? "Account created successfully! Please log in." : "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errors[] = "Please enter both email and password.";
    } else {
        try {
            $pdo = getDbConnection();

            $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password_hash'])) {
                // Success!
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                header('Location: ../index.php');
                exit;
            } else {
                $errors[] = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $errors[] = "An error occurred during login. Please try again later.";
        }
    }
}
?>