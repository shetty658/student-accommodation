<?php
/**
 * StayNest API - Toggle Property Interest / Shortlist
 * Handles add/remove from shortlist with duplicate protection and authentication.
 */

require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

// Accept JSON payload or form-data
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$propertyId = isset($input['property_id']) ? (int)$input['property_id'] : 0;

if ($propertyId <= 0) {
    jsonResponse([
        'success' => false,
        'message' => 'Valid property ID is required.'
    ], 400);
}

// Enforce login
$userId = getAuthUserId();
if (!$userId) {
    jsonResponse([
        'success' => false,
        'message' => 'Please login to shortlist properties.',
        'require_login' => true
    ], 401);
}

try {
    $pdo = getDBConnection();

    // Verify property exists
    $propCheck = $pdo->prepare("SELECT id, name FROM properties WHERE id = ?");
    $propCheck->execute([$propertyId]);
    $property = $propCheck->fetch();

    if (!$property) {
        jsonResponse([
            'success' => false,
            'message' => 'Property not found.'
        ], 404);
    }

    // Check if already shortlisted
    $checkStmt = $pdo->prepare("SELECT id FROM interested_users WHERE user_id = ? AND property_id = ?");
    $checkStmt->execute([$userId, $propertyId]);
    $existing = $checkStmt->fetch();

    if ($existing) {
        // Remove from shortlist
        $delStmt = $pdo->prepare("DELETE FROM interested_users WHERE user_id = ? AND property_id = ?");
        $delStmt->execute([$userId, $propertyId]);

        // Get updated total count
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM interested_users WHERE user_id = ?");
        $countStmt->execute([$userId]);
        $totalShortlist = (int)$countStmt->fetchColumn();

        jsonResponse([
            'success' => true,
            'action' => 'removed',
            'is_interested' => false,
            'property_id' => $propertyId,
            'shortlist_count' => $totalShortlist,
            'message' => 'Property removed from shortlist.'
        ]);
    } else {
        // Add to shortlist
        $insStmt = $pdo->prepare("INSERT INTO interested_users (user_id, property_id, created_at) VALUES (?, ?, ?)");
        $insStmt->execute([$userId, $propertyId, date('Y-m-d H:i:s')]);

        // Get updated total count
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM interested_users WHERE user_id = ?");
        $countStmt->execute([$userId]);
        $totalShortlist = (int)$countStmt->fetchColumn();

        jsonResponse([
            'success' => true,
            'action' => 'added',
            'is_interested' => true,
            'property_id' => $propertyId,
            'shortlist_count' => $totalShortlist,
            'message' => 'Property added to shortlist!'
        ]);
    }

} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ], 500);
}
