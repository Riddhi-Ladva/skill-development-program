<?php
/**
 * Edit Profile Page
 * 
 * Responsibility: Allows users to update their personal details (First Name, Last Name, Email, Phone).
 */

require_once __DIR__ . '/../includes/bootstrap/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth/guard.php';

// Auth Guard - Only logged in users
auth_guard();

$user_id = $_SESSION['user_id'];
$pdo = getDbConnection();

$errors = [];
$success_message = "";

// 1. Fetch current user data
try {
    $stmt = $pdo->prepare("SELECT email, first_name, last_name, phone FROM users WHERE id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // This shouldn't happen if auth_guard works, but safety first
        header("Location: " . url('pages/login.php'));
        exit;
    }
} catch (PDOException $e) {
    error_log("Profile Fetch Error: " . $e->getMessage());
    $errors[] = "Could not load profile data.";
}

// 2. Handle Update Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // Basic Validation
    if (empty($firstName) || empty($lastName) || empty($email)) {
        $errors[] = "First name, last_name, and email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($errors)) {
        try {
            // Check if email is already taken by ANOTHER user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :user_id");
            $stmt->execute(['email' => $email, 'user_id' => $user_id]);
            if ($stmt->fetch()) {
                $errors[] = "This email is already in use by another account.";
            } else {
                // Update DB
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET first_name = :first_name, 
                        last_name = :last_name, 
                        email = :email, 
                        phone = :phone,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = :user_id
                ");
                $stmt->execute([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'phone' => $phone,
                    'user_id' => $user_id
                ]);

                $success_message = "Profile updated successfully!";

                // Refresh local user data for the form
                $user['first_name'] = $firstName;
                $user['last_name'] = $lastName;
                $user['email'] = $email;
                $user['phone'] = $phone;
            }
        } catch (PDOException $e) {
            error_log("Profile Update Error: " . $e->getMessage());
            $errors[] = "An error occurred while updating your profile.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - EasyCart</title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css?v=1.2'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/pages/dashboard.css?v=1.1'); ?>">
    <style>
        .profile-form-container {
            max-width: 600px;
            background: white;
            padding: var(--spacing-8);
            border-radius: var(--border-radius-lg);
            border: 1px solid var(--color-border);
            box-shadow: var(--shadow-sm);
        }

        .form-group {
            margin-bottom: var(--spacing-6);
        }

        .form-group label {
            display: block;
            margin-bottom: var(--spacing-2);
            font-weight: var(--font-weight-medium);
            color: var(--color-text-primary);
        }

        .form-group input {
            width: 100%;
            padding: var(--spacing-3);
            border: 1px solid var(--color-border);
            border-radius: var(--border-radius-md);
            font-size: var(--font-size-sm);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .alert {
            padding: var(--spacing-4);
            border-radius: var(--border-radius-md);
            margin-bottom: var(--spacing-6);
        }

        .alert-error {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .btn-save {
            background-color: var(--color-primary);
            color: white;
            padding: var(--spacing-3) var(--spacing-8);
            border: none;
            border-radius: var(--border-radius-md);
            font-weight: var(--font-weight-semibold);
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-save:hover {
            background-color: var(--color-primary-dark);
        }
    </style>
</head>

<body class="dashboard-page">
    <?php include '../includes/header.php'; ?>

    <main id="main-content">
        <div class="account-container">


            <section class="account-main">
                <header class="page-header">
                    <h1>Account Details</h1>
                    <p>Update your personal information</p>
                </header>

                <div class="profile-form-container">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-error">
                            <?php foreach ($errors as $error)
                                echo htmlspecialchars($error) . "<br>"; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name"
                                value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name"
                                value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email"
                                value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone"
                                value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                placeholder="e.g. +1 234 567 8900">
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-save">Save Changes</button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>

</html>