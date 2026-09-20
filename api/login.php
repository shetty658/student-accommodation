<?php
/**
 * StayNest API - User Login
 * Handles session authentication with email and password verification.
 */

require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

// Accept JSON payload or form POST
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$email = isset($input['email']) ? trim($input['email']) : '';
$password = isset($input['password']) ? trim($input['password']) : '';

if (empty($email) || empty($password)) {
    jsonResponse([
        'success' => false,
        'message' => 'Please provide both email address and password.'
    ], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse([
        'success' => false,
        'message' => 'Please enter a valid email address.'
    ], 400);
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id, name, email, password, phone FROM users WHERE LOWER(email) = LOWER(?)");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        jsonResponse([
            'success' => false,
            'message' => 'Invalid email address or password. Please try again.'
        ], 401);
    }

    // Set Session
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_phone'] = $user['phone'];

    // Get user's shortlist count
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM interested_users WHERE user_id = ?");
    $countStmt->execute([$user['id']]);
    $shortlistCount = (int)$countStmt->fetchColumn();

    jsonResponse([
        'success' => true,
        'message' => 'Login successful! Welcome back, ' . htmlspecialchars($user['name']) . '.',
        'user' => [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'shortlist_count' => $shortlistCount
        ],
        'redirect' => 'properties.php'
    ]);

} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Authentication service error: ' . $e->getMessage()
    ], 500);
}
