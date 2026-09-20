<?php
/**
 * StayNest API - Properties Listing & Filtering
 * Supports filtering by city, budget bracket, gender, rating, and keyword search.
 */

require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDBConnection();
    $userId = getAuthUserId();
    $shortlistIds = getUserShortlistIds($pdo, $userId);

    // Filter parameters
    $city = isset($_GET['city']) ? trim($_GET['city']) : '';
    $budget = isset($_GET['budget']) ? trim($_GET['budget']) : '';
    $gender = isset($_GET['gender']) ? trim($_GET['gender']) : '';
    $rating = isset($_GET['rating']) ? (float)$_GET['rating'] : 0;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $sort = isset($_GET['sort']) ? trim($_GET['sort']) : '';
    $limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 50;
    $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;

    $where = [];
    $params = [];

    // City Filter
    if (!empty($city) && strtolower($city) !== 'all') {
        $where[] = "LOWER(p.city) = LOWER(?)";
        $params[] = $city;
    }

    // Gender Filter
    if (!empty($gender) && strtolower($gender) !== 'all') {
        $where[] = "LOWER(p.gender) = LOWER(?)";
        $params[] = $gender;
    }

    // Rating Filter
    if ($rating > 0) {
        $where[] = "p.rating >= ?";
        $params[] = $rating;
    }

    // Budget Bracket Filter
    if (!empty($budget) && strtolower($budget) !== 'all') {
        switch ($budget) {
            case 'under_5000':
            case '<5000':
                $where[] = "p.price < 5000";
                break;
            case '5000_7000':
            case '5000-7000':
                $where[] = "p.price >= 5000 AND p.price <= 7000";
                break;
            case '7000_10000':
            case '7000-10000':
                $where[] = "p.price > 7000 AND p.price <= 10000";
                break;
            case 'above_10000':
            case '>10000':
                $where[] = "p.price > 10000";
                break;
        }
    }

    // Search Filter (Keyword matching)
    if (!empty($search)) {
        $where[] = "(p.name LIKE ? OR p.address LIKE ? OR p.city LIKE ? OR p.description LIKE ?)";
        $searchTerm = '%' . $search . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    // Sorting
    $orderBy = "ORDER BY p.id ASC";
    if ($sort === 'price_low' || $sort === 'price_asc') {
        $orderBy = "ORDER BY p.price ASC";
    } elseif ($sort === 'price_high' || $sort === 'price_desc') {
        $orderBy = "ORDER BY p.price DESC";
    } elseif ($sort === 'rating_high' || $sort === 'rating_desc') {
        $orderBy = "ORDER BY p.rating DESC";
    }

    // Fetch Properties
    $sql = "SELECT p.* FROM properties p {$whereSql} {$orderBy} LIMIT {$limit} OFFSET {$offset}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $properties = $stmt->fetchAll();

    // Fetch Amenities for each property
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
            'is_interested' => in_array((int)$prop['id'], $shortlistIds),
            'created_at' => $prop['created_at']
        ];
    }

    jsonResponse([
        'success' => true,
        'count' => count($formatted),
        'properties' => $formatted,
        'filters' => [
            'city' => $city ?: 'All',
            'budget' => $budget ?: 'All',
            'gender' => $gender ?: 'All',
            'rating' => $rating ?: 'Any',
            'search' => $search
        ]
    ]);

} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Failed to fetch properties: ' . $e->getMessage()
    ], 500);
}
