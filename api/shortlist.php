<?php
/**
 * StayNest API - User Shortlist
 * Returns all properties shortlisted by the authenticated user, or handles removal.
 */

require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

$userId = getAuthUserId();
if (!$userId) {
    jsonResponse([
        'success' => false,
        'message' => 'Please login to view shortlisted properties.',
        'require_login' => true
    ], 401);
}

try {
    $pdo = getDBConnection();
    $method = $_SERVER['REQUEST_METHOD'];

    // Handle Removal if request is DELETE or POST with action=remove
    if ($method === 'DELETE' || ($method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove')) {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $propertyId = isset($input['property_id']) ? (int)$input['property_id'] : 0;

        if ($propertyId <= 0) {
            jsonResponse(['success' => false, 'message' => 'Property ID is required.'], 400);
        }

        $delStmt = $pdo->prepare("DELETE FROM interested_users WHERE user_id = ? AND property_id = ?");
        $delStmt->execute([$userId, $propertyId]);

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM interested_users WHERE user_id = ?");
        $countStmt->execute([$userId]);
        $remainingCount = (int)$countStmt->fetchColumn();

        jsonResponse([
            'success' => true,
            'message' => 'Property removed from shortlist.',
            'property_id' => $propertyId,
            'shortlist_count' => $remainingCount
        ]);
    }

    // Default GET: Fetch all shortlisted properties
    $sql = "
        SELECT p.*, iu.created_at as shortlisted_at 
        FROM interested_users iu
        JOIN properties p ON iu.property_id = p.id
        WHERE iu.user_id = ?
        ORDER BY iu.created_at DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $properties = $stmt->fetchAll();

    // Fetch amenities for each
    $amenityStmt = $pdo->prepare("
        SELECT a.id, a.name, a.icon 
        FROM property_amenities pa 
        JOIN amenities a ON pa.amenity_id = a.id 
        WHERE pa.property_id = ?
    ");

    $formatted = [];
    foreach ($properties as $prop) {
        $amenityStmt->execute([$prop['id']]);
        $amenities = $amenityStmt->fetchAll();

        $formatted[] = [
            'id' => (int)$prop['id'],
            'name' => $prop['name'],
            'city' => $prop['city'],
            'address' => $prop['address'],
            'price' => (float)$prop['price'],
            'formatted_price' => '₹' . number_format((float)$prop['price']),
            'gender' => $prop['gender'],
            'rating' => (float)$prop['rating'],
            'description' => $prop['description'],
            'image' => $prop['image'],
            'amenities' => $amenities,
            'is_interested' => true,
            'shortlisted_at' => $prop['shortlisted_at']
        ];
    }

    jsonResponse([
        'success' => true,
        'count' => count($formatted),
        'properties' => $formatted
    ]);

} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Error accessing shortlist: ' . $e->getMessage()
    ], 500);
}
