<?php
/**
 * StayNest - User Shortlist Page (shortlist.php)
 * Protected view displaying student's shortlisted properties with AJAX removal,
 * empty state, and direct navigation to details.
 */

require_once __DIR__ . '/api/db.php';

$userId = getAuthUserId();
if (!$userId) {
    header('Location: login.php?redirect=' . urlencode('shortlist.php'));
    exit;
}

$pdo = getDBConnection();
$currentUser = getAuthUser();

// Fetch shortlisted properties
$sql = "
    SELECT p.*, iu.created_at as shortlisted_at 
    FROM interested_users iu
    JOIN properties p ON iu.property_id = p.id
    WHERE iu.user_id = ?
    ORDER BY iu.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$shortlistedProperties = $stmt->fetchAll();

// Fetch amenities for each
$amenityStmt = $pdo->prepare("
    SELECT a.id, a.name, a.icon 
    FROM property_amenities pa 
    JOIN amenities a ON pa.amenity_id = a.id 
    WHERE pa.property_id = ?
");

$properties = [];
foreach ($shortlistedProperties as $prop) {
    $amenityStmt->execute([$prop['id']]);
    $prop['amenities'] = $amenityStmt->fetchAll();
    $properties[] = $prop;
}

$totalCount = count($properties);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Shortlist (<?= $totalCount ?>) | StayNest</title>
    <meta name="description" content="View and manage your shortlisted student PG accommodations. Compare prices, amenities, and schedule visits.">

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    <!-- Reusable Navbar -->
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <!-- Header Banner -->
    <div class="bg-white border-bottom py-4">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb small mb-1">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="properties.php">Properties</a></li>
                            <li class="breadcrumb-item active" aria-current="page">My Shortlist</li>
                        </ol>
                    </nav>
                    <h1 class="h3 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-heart-fill text-danger"></i>
                        <span>My Shortlisted PGs</span>
                        <span class="badge bg-danger rounded-pill fs-6" id="shortlist-header-count"><?= $totalCount ?></span>
                    </h1>
                </div>

                <div>
                    <a href="properties.php" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold">
                        <i class="bi bi-plus-circle me-1"></i> Explore More PGs
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container py-5 flex-grow-1">

        <!-- Shortlist Cards Grid -->
        <div id="shortlist-grid" class="row g-4 <?= $totalCount === 0 ? 'd-none' : '' ?>">
            <?php foreach ($properties as $p): ?>
                <?php
                    $genderClass = strtolower($p['gender']) === 'male' ? 'male' : (strtolower($p['gender']) === 'female' ? 'female' : 'co-living');
                    $genderIcon = $p['gender'] === 'Female' ? 'bi-gender-female' : ($p['gender'] === 'Male' ? 'bi-gender-male' : 'bi-people');
                ?>
                <div class="col-md-6 col-lg-4" id="shortlist-card-<?= (int)$p['id'] ?>">
                    <div class="property-card">
                        <div class="property-image-wrapper">
                            <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy" onerror="this.src='assets/images/property-1.jpg'">
                            <span class="property-badge-gender <?= $genderClass ?>">
                                <i class="bi <?= $genderIcon ?>"></i> <?= htmlspecialchars($p['gender']) ?>
                            </span>
                        </div>
                        <div class="property-card-body">
                            <h3 class="property-card-title"><?= htmlspecialchars($p['name']) ?></h3>
                            <div class="property-location">
                                <i class="bi bi-geo-alt-fill text-primary"></i>
                                <span class="text-truncate"><?= htmlspecialchars($p['city']) ?> • <?= htmlspecialchars($p['address']) ?></span>
                            </div>

                            <div class="property-pricing-rating">
                                <div class="property-price">
                                    ₹<?= number_format((float)$p['price']) ?><small>/month</small>
                                </div>
                                <div class="property-rating">
                                    <i class="bi bi-star-fill text-warning"></i>
                                    <span><?= number_format((float)$p['rating'], 1) ?></span>
                                </div>
                            </div>

                            <div class="property-amenities-pills">
                                <?php foreach (array_slice($p['amenities'] ?? [], 0, 3) as $am): ?>
                                    <span class="amenity-pill">
                                        <i class="bi <?= htmlspecialchars($am['icon'] ?? 'bi-check-circle') ?>"></i>
                                        <?= htmlspecialchars($am['name']) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>

                            <!-- Actions Footer: View Details & AJAX Remove -->
                            <div class="property-card-footer d-flex gap-2">
                                <a href="property-details.php?id=<?= (int)$p['id'] ?>" class="btn btn-primary flex-grow-1 py-2 d-flex align-items-center justify-content-center gap-1">
                                    <span>View Details</span>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger btn-remove-shortlist px-3 py-2" data-property-id="<?= (int)$p['id'] ?>" title="Remove from shortlist">
                                    <i class="bi bi-trash3"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Empty State -->
        <div id="shortlist-empty-state" class="text-center py-5 bg-white rounded-4 border shadow-sm <?= $totalCount === 0 ? '' : 'd-none' ?>">
            <div class="mb-3 text-muted">
                <i class="bi bi-heartbreak fs-1 text-danger"></i>
            </div>
            <h3 class="fw-bold text-dark mb-2">No properties shortlisted yet</h3>
            <p class="text-muted mb-4" style="max-width: 480px; margin: 0 auto;">
                Explore student accommodations across Bengaluru, Dharwad, Hubballi, Mysuru, Hyderabad, and Pune. Click the heart icon on any property to save it here for comparison.
            </p>
            <a href="properties.php" class="btn btn-primary px-4 py-2.5 rounded-pill fw-bold shadow-sm">
                <i class="bi bi-search me-1"></i> Explore PGs
            </a>
        </div>

    </div>

    <!-- Reusable Footer -->
    <?php include __DIR__ . '/components/footer.php'; ?>

</body>
</html>
