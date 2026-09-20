<?php
/**
 * StayNest API - Single Property Details
 * Fetches full property information, image gallery, amenities, and shortlist state.
 */

require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    jsonResponse([
        'success' => false,
        'message' => 'Valid Property ID is required.'
    ], 400);
}

try {
    $pdo = getDBConnection();
    $userId = getAuthUserId();

    // Fetch Property Details
    $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->execute([$id]);
    $property = $stmt->fetch();

    if (!$property) {
        jsonResponse([
            'success' => false,
            'message' => 'Property not found.'
        ], 404);
    }

    // Fetch Image Gallery
    $imgStmt = $pdo->prepare("SELECT image_url FROM property_images WHERE property_id = ?");
    $imgStmt->execute([$id]);
    $gallery = $imgStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

    // Ensure main image is included at the front of gallery
    if (!empty($property['image']) && !in_array($property['image'], $gallery)) {
        array_unshift($gallery, $property['image']);
    }

    // If gallery is still empty, add default main image
    if (empty($gallery)) {
        $gallery[] = $property['image'];
    }

    // Fetch Amenities
    $amenityStmt = $pdo->prepare("
        SELECT a.id, a.name, a.icon 
        FROM property_amenities pa 
        JOIN amenities a ON pa.amenity_id = a.id 
        WHERE pa.property_id = ?
    ");
    $amenityStmt->execute([$id]);
    $amenities = $amenityStmt->fetchAll() ?: [];

    // Check if current user shortlisted
    $isInterested = false;
    if ($userId) {
        $intStmt = $pdo->prepare("SELECT id FROM interested_users WHERE user_id = ? AND property_id = ?");
        $intStmt->execute([$userId, $id]);
        $isInterested = (bool)$intStmt->fetch();
    }

    // Landlord / Property Manager demo details
    $contactInfo = [
        'manager_name' => 'Campus Care Team (' . $property['city'] . ' Branch)',
        'phone' => '+91 91234 56789',
        'email' => 'support@staynest.in',
        'office_hours' => '9:00 AM - 7:00 PM (Mon-Sat)',
        'visit_timings' => 'Available for viewing every day between 10:00 AM - 6:00 PM'
    ];

    jsonResponse([
        'success' => true,
        'property' => [
            'id' => (int)$property['id'],
            'name' => $property['name'],
            'city' => $property['city'],
            'address' => $property['address'],
            'price' => (float)$property['price'],
            'formatted_price' => '₹' . number_format((float)$property['price']),
            'gender' => $property['gender'],
            'rating' => (float)$property['rating'],
            'description' => $property['description'],
            'image' => $property['image'],
            'gallery' => $gallery,
            'amenities' => $amenities,
            'is_interested' => $isInterested,
            'contact' => $contactInfo,
            'deposit' => '₹' . number_format((float)$property['price'] * 2), // 2 months deposit standard
            'notice_period' => '30 Days',
            'created_at' => $property['created_at']
        ]
    ]);

} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Error retrieving property: ' . $e->getMessage()
    ], 500);
}
