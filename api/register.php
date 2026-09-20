<?php
/**
 * StayNest API - User Registration
 * Validates student details, checks email uniqueness, hashes password, and creates account.
 */

require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$name = isset($input['name']) ? trim($input['name']) : '';
$email = isset($input['email']) ? trim($input['email']) : '';
$phone = isset($input['phone']) ? trim($input['phone']) : '';
$password = isset($input['password']) ? $input['password'] : '';
$confirmPassword = isset($input['confirm_password']) ? $input['confirm_password'] : '';

// Validation
$errors = [];

if (empty($name) || strlen($name) < 2) {
    $errors[] = 'Full Name must be at least 2 characters.';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please provide a valid email address.';
}

if (empty($phone) || strlen($phone) < 8) {
    $errors[] = 'Please provide a valid contact phone number.';
}

if (empty($password) || strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters long.';
}

if ($password !== $confirmPassword) {
    $errors[] = 'Password and Confirm Password do not match.';
}

if (!empty($errors)) {
    jsonResponse([
        'success' => false,
        'message' => implode(' ', $errors),
        'errors' => $errors
    ], 422);
}

try {
    $pdo = getDBConnection();

    // Check duplicate email
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(?)");
    $checkStmt->execute([$email]);
    if ($checkStmt->fetch()) {
        jsonResponse([
            'success' => false,
            'message' => 'An account with this email address already exists. Please log in.'
        ], 409);
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert user
    $insStmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, created_at) VALUES (?, ?, ?, ?, ?)");
    $insStmt->execute([$name, $email, $hashedPassword, $phone, date('Y-m-d H:i:s')]);
    $newUserId = (int)$pdo->lastInsertId();

    // Automatically log in user
    $_SESSION['user_id'] = $newUserId;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_phone'] = $phone;

    jsonResponse([
        'success' => true,
        'message' => 'Registration successful! Welcome to StayNest, ' . htmlspecialchars($name) . '.',
        'user' => [
            'id' => $newUserId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'shortlist_count' => 0
        ],
        'redirect' => 'properties.php'
    ], 201);

} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Registration failed: ' . $e->getMessage()
    ], 500);
}
