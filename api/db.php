<?php
/**
 * StayNest - Database Connection Handler
 * Provides a unified PDO connection for MySQL with intelligent auto-fallback
 * to SQLite for zero-downtime evaluation and portable testing.
 */

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load environment configurations or defaults
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'student_accommodation');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');

/**
 * Returns the active PDO database connection singleton.
 * Attempts MySQL first; if unavailable, falls back to seeded SQLite.
 *
 * @return PDO
 */
function getDBConnection(): PDO
{
    static $pdo = null;
    static $driver = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $pdoOptions = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    // 1. Attempt MySQL connection
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $pdoOptions);
        $driver = 'mysql';
        return $pdo;
    } catch (PDOException $e) {
        // MySQL connection failed; log and proceed to fallback
        error_log("StayNest: MySQL connection failed (" . $e->getMessage() . "). Falling back to SQLite.");
    }

    // 2. Fallback to SQLite
    try {
        $dbDir = __DIR__ . '/../database';
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0777, true);
        }
        $sqliteFile = $dbDir . '/student_accommodation.sqlite';
        $isNewDb = !file_exists($sqliteFile) || filesize($sqliteFile) === 0;

        $pdo = new PDO("sqlite:" . $sqliteFile, null, null, $pdoOptions);
        $pdo->exec("PRAGMA foreign_keys = ON;");
        $driver = 'sqlite';

        if ($isNewDb) {
            seedSqliteDatabase($pdo);
        }

        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . $e->getMessage()
        ]);
        exit;
    }
}

/**
 * Seeds SQLite database with tables and sample data identical to MySQL schema.
 */
function seedSqliteDatabase(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            phone TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS properties (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            city TEXT NOT NULL,
            address TEXT NOT NULL,
            price REAL NOT NULL,
            gender TEXT CHECK(gender IN ('Male', 'Female', 'Co-living')) NOT NULL,
            rating REAL NOT NULL DEFAULT 4.0,
            description TEXT NOT NULL,
            image TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS amenities (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            icon TEXT NOT NULL DEFAULT 'bi-check-circle'
        );

        CREATE TABLE IF NOT EXISTS property_amenities (
            property_id INTEGER NOT NULL,
            amenity_id INTEGER NOT NULL,
            PRIMARY KEY (property_id, amenity_id),
            FOREIGN KEY (property_id) REFERENCES properties (id) ON DELETE CASCADE,
            FOREIGN KEY (amenity_id) REFERENCES amenities (id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS property_images (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            property_id INTEGER NOT NULL,
            image_url TEXT NOT NULL,
            FOREIGN KEY (property_id) REFERENCES properties (id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS interested_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            property_id INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE (user_id, property_id),
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
            FOREIGN KEY (property_id) REFERENCES properties (id) ON DELETE CASCADE
        );
    ");

    // Insert amenities
    $amenities = [
        [1, 'WiFi', 'bi-wifi'],
        [2, 'AC', 'bi-snow'],
        [3, 'Food', 'bi-egg-fried'],
        [4, 'Laundry', 'bi-droplet-half'],
        [5, 'Parking', 'bi-p-circle'],
        [6, 'CCTV', 'bi-shield-check'],
        [7, 'Power Backup', 'bi-lightning-charge'],
        [8, 'Study Room', 'bi-book'],
        [9, 'Gym', 'bi-heart-pulse'],
        [10, 'Housekeeping', 'bi-stars'],
    ];
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO amenities (id, name, icon) VALUES (?, ?, ?)");
    foreach ($amenities as $a) {
        $stmt->execute($a);
    }

    // Insert sample users (Password: Password123!)
    $hashedPassword = password_hash('Password123!', PASSWORD_BCRYPT);
    $users = [
        [1, 'Rahul Sharma', 'rahul@student.edu', $hashedPassword, '+91 9876543210', '2026-01-15 10:00:00'],
        [2, 'Priya Kulkarni', 'priya@student.edu', $hashedPassword, '+91 9845012345', '2026-02-01 11:30:00'],
        [3, 'Vikram Patil', 'vikram@student.edu', $hashedPassword, '+91 9741234567', '2026-02-10 14:15:00'],
    ];
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO users (id, name, email, password, phone, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($users as $u) {
        $stmt->execute($u);
    }

    // Insert 15 properties
    $properties = [
        [1, 'Student Nest PG', 'Bengaluru', '5th Block, Koramangala, Near Jyoti Nivas College', 7500.00, 'Male', 4.6, 'Premium student residence with high-speed 500Mbps WiFi, 3 hygienic meals daily, biometric entry, and quiet study lounges. Walking distance from major colleges and tech parks.', 'assets/images/property-1.jpg'],
        [2, 'Campus Comfort Stay', 'Dharwad', 'Near Karnatak University Campus, Kelageri Road', 4500.00, 'Co-living', 4.4, 'Spacious and peaceful student accommodation designed specifically for university students. Features serene study gardens, home-cooked North/South Indian meals, and 24/7 security.', 'assets/images/property-2.jpg'],
        [3, 'Scholar\'s Residency', 'Hubballi', 'Vidyanagar, Opposite KLE Technological University', 5000.00, 'Male', 4.3, 'Strategically located directly opposite university gates. Well-ventilated rooms with study tables, hot water, power backup, laundry service, and delicious veg/non-veg mess.', 'assets/images/property-3.jpg'],
        [4, 'Royal Palace Luxury Stay', 'Mysuru', 'Saraswathipuram, Near St. Philomena\'s College', 6500.00, 'Female', 4.7, 'Safe and secure women\'s PG with round-the-clock female security warden, CCTV surveillance, finger-print access, fully furnished rooms, attached washrooms, and healthy home-style dining.', 'assets/images/property-4.jpg'],
        [5, 'Cyber Stay Co-living', 'Hyderabad', 'Phase 2, HITEC City, Near IIIT Hyderabad', 9500.00, 'Co-living', 4.8, 'Modern high-tech co-living space tailored for IT scholars and university interns. Offers AC rooms, ergonomic workstations, gaming lounge, gym, high-speed fiber internet, and buffet meals.', 'assets/images/property-5.jpg'],
        [6, 'Elite Student Living', 'Pune', 'Viman Nagar, Close to Symbiosis International University', 8500.00, 'Female', 4.6, 'Upscale girls PG offering luxury shared and private rooms. Includes air-conditioned rooms, daily housekeeping, chef-prepared meals, rooftop relaxation deck, and secure digital entry.', 'assets/images/property-6.jpg'],
        [7, 'Green View PG', 'Bengaluru', 'Electronic City Phase 1, Near PES University Campus', 6000.00, 'Male', 4.2, 'Affordable, clean accommodation surrounded by lush greenery. Includes WiFi, purified drinking water, daily cleaning, nutritious home-cooked meals, and two-wheeler parking.', 'assets/images/property-7.jpg'],
        [8, 'Serene Girls Hostel & PG', 'Dharwad', 'Kalyan Nagar, Near SDM College of Medical Sciences', 4200.00, 'Female', 4.5, 'Warm and welcoming residence for female scholars and medical students. Safe neighborhood, in-house warden, study desk in every room, laundry machines, and wholesome local cuisine.', 'assets/images/property-8.jpg'],
        [9, 'Tech Hub Student Living', 'Hubballi', 'BVB Campus Road, Vidyanagar', 5500.00, 'Co-living', 4.4, 'Vibrant student community housing engineers and design students. Features high-speed fiber internet, open terrace study zone, modern kitchen facilities, and bi-weekly events.', 'assets/images/property-9.jpg'],
        [10, 'Heritage PG for Men', 'Mysuru', 'Gokulam 3rd Stage, Near CFTRI & Yoga Hub', 5200.00, 'Male', 4.3, 'Peaceful accommodation in Mysore\'s premier residential locality. Spacious double and triple sharing rooms, clean bathrooms, laundry facilities, and authentic traditional food.', 'assets/images/property-10.jpg'],
        [11, 'Skyline Deluxe Residency', 'Hyderabad', 'Telecom Nagar, Gachibowli, Near University of Hyderabad', 10000.00, 'Co-living', 4.9, 'Premium co-living property with hotel-style amenities. Includes air conditioning, smart TVs, rooftop cafeteria, professional housekeeping, laundry care, and 24/7 fitness center.', 'assets/images/property-11.jpg'],
        [12, 'Oxford Haven PG', 'Pune', 'Kothrud, Near MIT World Peace University', 7000.00, 'Male', 4.5, 'Top-rated boy\'s accommodation located minutes from top colleges. Fully furnished with box beds, individual wardrobes, high-speed WiFi, RO water, and vegetarian & non-vegetarian mess.', 'assets/images/property-12.jpg'],
        [13, 'Blossom Luxury Residence', 'Bengaluru', '100 Feet Road, Indiranagar, Near Metro Station', 10500.00, 'Female', 4.8, 'Boutique women\'s residence in prime Indiranagar. Air-conditioned suites, aesthetic interior design, biometric security, microwave & fridge access, daily housekeeping, and 3-course meals.', 'assets/images/property-13.jpg'],
        [14, 'Vidyarthi Nilaya', 'Dharwad', 'Saptapur Last Bus Stop, Near JSS College', 4000.00, 'Male', 4.1, 'Budget-friendly, highly practical hostel for competitive exam aspirants and college students. Quiet surroundings, dedicated library hall, hot water solar heaters, and hygienic meal plans.', 'assets/images/property-14.jpg'],
        [15, 'Phoenix Premium Co-living', 'Pune', 'Phase 1, Hinjewadi Rajiv Gandhi Infotech Park', 9000.00, 'Co-living', 4.7, 'State-of-the-art student & tech intern co-living complex. Community kitchen, high-speed mesh WiFi, recreation room with pool table, weekly social mixers, and daily sanitization.', 'assets/images/property-15.jpg'],
    ];

    $stmt = $pdo->prepare("INSERT OR IGNORE INTO properties (id, name, city, address, price, gender, rating, description, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($properties as $p) {
        $stmt->execute($p);
    }

    // Property amenities mapping
    $mapping = [
        1 => [1, 3, 4, 6, 7, 8, 10],
        2 => [1, 3, 5, 6, 7, 8],
        3 => [1, 3, 4, 5, 6, 7],
        4 => [1, 2, 3, 4, 6, 7, 10],
        5 => [1, 2, 3, 4, 6, 7, 8, 9, 10],
        6 => [1, 2, 3, 4, 6, 7, 8, 10],
        7 => [1, 3, 5, 6, 7, 10],
        8 => [1, 3, 4, 6, 7, 8, 10],
        9 => [1, 3, 4, 5, 6, 7, 8],
        10 => [1, 3, 5, 6, 7, 10],
        11 => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        12 => [1, 3, 4, 6, 7, 8, 10],
        13 => [1, 2, 3, 4, 6, 7, 8, 10],
        14 => [1, 3, 5, 6, 7, 8],
        15 => [1, 2, 3, 4, 6, 7, 8, 9, 10],
    ];
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO property_amenities (property_id, amenity_id) VALUES (?, ?)");
    foreach ($mapping as $pId => $aIds) {
        foreach ($aIds as $aId) {
            $stmt->execute([$pId, $aId]);
        }
    }

    // Property images
    $images = [
        [1, 'assets/images/property-1.jpg'],
        [1, 'assets/images/gallery/p1-room.jpg'],
        [1, 'assets/images/gallery/p1-mess.jpg'],
        [1, 'assets/images/gallery/p1-study.jpg'],
        [2, 'assets/images/property-2.jpg'],
        [2, 'assets/images/gallery/p2-room.jpg'],
        [2, 'assets/images/gallery/p2-lounge.jpg'],
        [3, 'assets/images/property-3.jpg'],
        [3, 'assets/images/gallery/p3-room.jpg'],
        [3, 'assets/images/gallery/p3-dining.jpg'],
        [4, 'assets/images/property-4.jpg'],
        [4, 'assets/images/gallery/p4-room.jpg'],
        [4, 'assets/images/gallery/p4-lobby.jpg'],
        [5, 'assets/images/property-5.jpg'],
        [5, 'assets/images/gallery/p5-room.jpg'],
        [5, 'assets/images/gallery/p5-gym.jpg'],
        [5, 'assets/images/gallery/p5-lounge.jpg'],
        [6, 'assets/images/property-6.jpg'],
        [6, 'assets/images/gallery/p6-room.jpg'],
        [6, 'assets/images/gallery/p6-deck.jpg'],
        [7, 'assets/images/property-7.jpg'],
        [7, 'assets/images/gallery/p7-room.jpg'],
        [8, 'assets/images/property-8.jpg'],
        [8, 'assets/images/gallery/p8-room.jpg'],
        [9, 'assets/images/property-9.jpg'],
        [9, 'assets/images/gallery/p9-room.jpg'],
        [10, 'assets/images/property-10.jpg'],
        [10, 'assets/images/gallery/p10-room.jpg'],
        [11, 'assets/images/property-11.jpg'],
        [11, 'assets/images/gallery/p11-room.jpg'],
        [11, 'assets/images/gallery/p11-gym.jpg'],
        [12, 'assets/images/property-12.jpg'],
        [12, 'assets/images/gallery/p12-room.jpg'],
        [13, 'assets/images/property-13.jpg'],
        [13, 'assets/images/gallery/p13-room.jpg'],
        [14, 'assets/images/property-14.jpg'],
        [14, 'assets/images/gallery/p14-room.jpg'],
        [15, 'assets/images/property-15.jpg'],
        [15, 'assets/images/gallery/p15-room.jpg'],
        [15, 'assets/images/gallery/p15-games.jpg'],
    ];
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO property_images (property_id, image_url) VALUES (?, ?)");
    foreach ($images as $img) {
        $stmt->execute($img);
    }

    // Interested users
    $interests = [
        [1, 1],
        [1, 5],
        [1, 11],
        [2, 4],
        [2, 6],
    ];
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO interested_users (user_id, property_id) VALUES (?, ?)");
    foreach ($interests as $int) {
        $stmt->execute($int);
    }
}

/**
 * Sends a standardized JSON API response.
 *
 * @param array $data
 * @param int $statusCode
 */
function jsonResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/**
 * Returns the currently authenticated user's ID or null.
 *
 * @return int|null
 */
function getAuthUserId(): ?int
{
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

/**
 * Returns current logged-in user profile or null.
 *
 * @return array|null
 */
function getAuthUser(): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? 'Student',
        'email' => $_SESSION['user_email'] ?? '',
        'phone' => $_SESSION['user_phone'] ?? '',
    ];
}

/**
 * Enforces user authentication for protected endpoints.
 * Returns the user ID if logged in, or emits 401 JSON and terminates.
 *
 * @return int
 */
function requireAuthApi(): int
{
    $userId = getAuthUserId();
    if (!$userId) {
        jsonResponse([
            'success' => false,
            'message' => 'Please login to shortlist properties.',
            'require_login' => true
        ], 401);
    }
    return $userId;
}

/**
 * Helper to get shortlisted property IDs for a given user.
 *
 * @param PDO $pdo
 * @param int|null $userId
 * @return array
 */
function getUserShortlistIds(PDO $pdo, ?int $userId): array
{
    if (!$userId) {
        return [];
    }
    $stmt = $pdo->prepare("SELECT property_id FROM interested_users WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
}
