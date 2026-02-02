<?php
function validateForm(array $data): array
{
    $errors = [];

    // 1. Trim inputs
    $name     = trim($data['name'] ?? '');
    $email    = trim($data['email'] ?? '');
    $password = trim($data['password'] ?? '');

    // 2. Check empty fields
    if ($name === '') {
        $errors['name'] = 'Name is required';
    }

    if ($email === '') {
        $errors['email'] = 'Email is required';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required';
    }

    // 3. Validate email
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }

    // 4. Password length check
    if ($password !== '' && strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    }

    // 5. Return errors array
    return $errors;
}
$formData = [
    'name'     => '',
    'email'    => 'test@',
    'password' => '123'
];

$errors = validateForm($formData);

print_r($errors);
?>