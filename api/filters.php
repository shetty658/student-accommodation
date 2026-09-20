<?php
/**
 * StayNest API - Filters Metadata
 * Returns available cities, budget brackets, genders, and amenities.
 */

require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDBConnection();

    // Fetch Distinct Cities with listing count
    $cityStmt = $pdo->query("SELECT city, COUNT(*) as count FROM properties GROUP BY city ORDER BY city ASC");
    $cities = $cityStmt->fetchAll();

    // Fetch Amenities
    $amenityStmt = $pdo->query("SELECT id, name, icon FROM amenities ORDER BY name ASC");
    $amenities = $amenityStmt->fetchAll();

    // Fetch Price range min and max
    $priceStmt = $pdo->query("SELECT MIN(price) as min_price, MAX(price) as max_price FROM properties");
    $priceRange = $priceStmt->fetch();

    $budgetBrackets = [
        ['id' => 'under_5000', 'label' => 'Under ₹5,000'],
        ['id' => '5000_7000', 'label' => '₹5,000 – ₹7,000'],
        ['id' => '7000_10000', 'label' => '₹7,000 – ₹10,000'],
        ['id' => 'above_10000', 'label' => 'Above ₹10,000'],
    ];

    $genderOptions = [
        ['id' => 'All', 'label' => 'All Categories'],
        ['id' => 'Male', 'label' => 'Male PG'],
        ['id' => 'Female', 'label' => 'Female PG'],
        ['id' => 'Co-living', 'label' => 'Co-living / Unisex'],
    ];

    $ratingOptions = [
        ['id' => 0, 'label' => 'Any Rating'],
        ['id' => 3, 'label' => '3.0+ Stars'],
        ['id' => 4, 'label' => '4.0+ Stars'],
        ['id' => 4.5, 'label' => '4.5+ Top Rated'],
    ];

    jsonResponse([
        'success' => true,
        'cities' => $cities,
        'budget_brackets' => $budgetBrackets,
        'gender_options' => $genderOptions,
        'rating_options' => $ratingOptions,
        'amenities' => $amenities,
        'price_range' => [
            'min' => (float)($priceRange['min_price'] ?? 4000),
            'max' => (float)($priceRange['max_price'] ?? 11000)
        ]
    ]);

} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Error retrieving filters: ' . $e->getMessage()
    ], 500);
}
