<?php
/**
 * StayNest - Property Details Page (property-details.php)
 * Displays interactive gallery, comprehensive amenities, pricing breakdown,
 * interactive contact modal, and AJAX shortlist action.
 */

require_once __DIR__ . '/api/db.php';

$propertyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($propertyId <= 0) {
    header('Location: properties.php');
    exit;
}

$pdo = getDBConnection();
$userId = getAuthUserId();

// Fetch property
$stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
$stmt->execute([$propertyId]);
$property = $stmt->fetch();

if (!$property) {
    header('Location: properties.php');
    exit;
}

// Fetch Gallery Images
$galleryStmt = $pdo->prepare("SELECT image_url FROM property_images WHERE property_id = ?");
$galleryStmt->execute([$propertyId]);
$galleryImages = $galleryStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

// Ensure main image is included
if (!empty($property['image']) && !in_array($property['image'], $galleryImages)) {
    array_unshift($galleryImages, $property['image']);
}
if (empty($galleryImages)) {
    $galleryImages[] = $property['image'];
}

// Fetch Amenities
$amenityStmt = $pdo->prepare("
    SELECT a.id, a.name, a.icon 
    FROM property_amenities pa 
    JOIN amenities a ON pa.amenity_id = a.id 
    WHERE pa.property_id = ?
    ORDER BY a.name ASC
");
$amenityStmt->execute([$propertyId]);
$amenities = $amenityStmt->fetchAll() ?: [];

// Check Shortlist status
$isInterested = false;
if ($userId) {
    $intStmt = $pdo->prepare("SELECT id FROM interested_users WHERE user_id = ? AND property_id = ?");
    $intStmt->execute([$userId, $propertyId]);
    $isInterested = (bool)$intStmt->fetch();
}

// Price calculations
$price = (float)$property['price'];
$securityDeposit = $price * 2;
$formattedPrice = '₹' . number_format($price);
$formattedDeposit = '₹' . number_format($securityDeposit);

$genderClass = strtolower($property['gender']) === 'male' ? 'male' : (strtolower($property['gender']) === 'female' ? 'female' : 'co-living');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($property['name']) ?> | StayNest</title>
    <meta name="description" content="View verified details, pricing, amenities, room gallery, and house rules for <?= htmlspecialchars($property['name']) ?> in <?= htmlspecialchars($property['city']) ?>.">

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

    <!-- Reusable Navbar -->
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <!-- Breadcrumbs -->
    <div class="bg-white border-bottom py-2">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-0">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="properties.php">Properties</a></li>
                    <li class="breadcrumb-item"><a href="properties.php?city=<?= urlencode($property['city']) ?>"><?= htmlspecialchars($property['city']) ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($property['name']) ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Main Details Container -->
    <div class="container py-4">
        <div class="row g-4">
            
            <!-- Left Column: Gallery & Details -->
            <div class="col-lg-8">
                <!-- Title Header -->
                <div class="bg-white p-4 rounded-4 border shadow-sm mb-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                        <span class="property-badge-gender <?= $genderClass ?> position-static">
                            <i class="bi <?= $property['gender'] === 'Female' ? 'bi-gender-female' : ($property['gender'] === 'Male' ? 'bi-gender-male' : 'bi-people') ?>"></i>
                            <?= htmlspecialchars($property['gender']) ?> Accommodation
                        </span>
                        <div class="property-rating fs-6 py-1 px-3">
                            <i class="bi bi-star-fill text-warning"></i>
                            <span class="fw-bold"><?= number_format((float)$property['rating'], 1) ?></span>
                            <span class="text-muted small fw-normal ms-1">(Verified Ratings)</span>
                        </div>
                    </div>

                    <h1 class="h2 fw-bold text-dark mb-2"><?= htmlspecialchars($property['name']) ?></h1>
                    <div class="text-muted d-flex align-items-center gap-1">
                        <i class="bi bi-geo-alt-fill text-danger fs-5"></i>
                        <span><?= htmlspecialchars($property['address']) ?>, <strong><?= htmlspecialchars($property['city']) ?></strong></span>
                    </div>
                </div>

                <!-- 1. Interactive Image Gallery -->
                <div class="bg-white p-3 p-md-4 rounded-4 border shadow-sm mb-4">
                    <!-- Main Image Viewport -->
                    <div class="details-gallery-main mb-3">
                        <img id="gallery-active-image" src="<?= htmlspecialchars($galleryImages[0]) ?>" alt="<?= htmlspecialchars($property['name']) ?>" onerror="this.src='assets/images/property-1.jpg'">
                    </div>

                    <!-- Thumbnails Row -->
                    <?php if (count($galleryImages) > 1): ?>
                        <div class="gallery-thumbnail-row">
                            <?php foreach ($galleryImages as $index => $img): ?>
                                <div class="gallery-thumbnail-item <?= $index === 0 ? 'active' : '' ?>" onclick="switchGalleryImage(this, '<?= htmlspecialchars($img) ?>')">
                                    <img src="<?= htmlspecialchars($img) ?>" alt="Thumbnail <?= $index + 1 ?>" onerror="this.src='assets/images/property-1.jpg'">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 2. Description & Overview -->
                <div class="bg-white p-4 rounded-4 border shadow-sm mb-4">
                    <h3 class="h5 fw-bold text-dark mb-3 pb-2 border-bottom">About This Residence</h3>
                    <p class="text-secondary leading-relaxed mb-4">
                        <?= nl2br(htmlspecialchars($property['description'])) ?>
                    </p>

                    <div class="row g-3 py-3 bg-light rounded-3 px-2">
                        <div class="col-6 col-md-3 text-center">
                            <div class="text-muted small">Notice Period</div>
                            <div class="fw-bold text-dark">30 Days</div>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="text-muted small">Lock-in Period</div>
                            <div class="fw-bold text-dark">None (Flexible)</div>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="text-muted small">Maintenance Fee</div>
                            <div class="fw-bold text-success">Included</div>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="text-muted small">Electricity</div>
                            <div class="fw-bold text-dark">Submetered</div>
                        </div>
                    </div>
                </div>

                <!-- 3. Amenities Section -->
                <div class="bg-white p-4 rounded-4 border shadow-sm mb-4">
                    <h3 class="h5 fw-bold text-dark mb-3 pb-2 border-bottom">Included Amenities & Services</h3>
                    <div class="row g-3">
                        <?php foreach ($amenities as $am): ?>
                            <div class="col-6 col-md-4">
                                <div class="amenity-card-item">
                                    <i class="bi <?= htmlspecialchars($am['icon'] ?? 'bi-check-circle') ?>"></i>
                                    <span><?= htmlspecialchars($am['name']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 4. House Rules & Campus Guidelines -->
                <div class="bg-white p-4 rounded-4 border shadow-sm mb-4">
                    <h3 class="h5 fw-bold text-dark mb-3 pb-2 border-bottom">House Rules & Policies</h3>
                    <ul class="list-unstyled d-flex flex-column gap-2 text-secondary mb-0">
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-clock text-primary"></i>
                            <span>Gate Timing: 10:30 PM (Entry permitted with advance digital pass)</span>
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-people text-primary"></i>
                            <span>Visitors permitted in the common study lounge until 8:00 PM</span>
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-volume-mute text-primary"></i>
                            <span>Quiet study hours observed from 11:00 PM to 6:00 AM daily</span>
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-person text-primary"></i>
                            <span>Valid College ID / Proof of Enrollment required upon check-in</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Right Column: Sticky Booking / Shortlist Box -->
            <div class="col-lg-4">
                <div class="booking-sticky-box">
                    <div class="d-flex justify-content-between align-items-baseline mb-3 pb-3 border-bottom">
                        <div>
                            <span class="text-muted small">Monthly Rent</span>
                            <div class="property-price fs-2"><?= $formattedPrice ?><small class="fs-6 text-muted">/mo</small></div>
                        </div>
                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-semibold">
                            <i class="bi bi-check2-all me-1"></i> Available Now
                        </span>
                    </div>

                    <!-- Pricing Breakdown -->
                    <div class="d-flex flex-column gap-2 mb-4 text-secondary small">
                        <div class="d-flex justify-content-between">
                            <span>Monthly Rent:</span>
                            <span class="fw-semibold text-dark"><?= $formattedPrice ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Refundable Deposit (2 mos):</span>
                            <span class="fw-semibold text-dark"><?= $formattedDeposit ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Brokerage Fee:</span>
                            <span class="fw-bold text-success">₹0 (Free)</span>
                        </div>
                    </div>

                    <!-- Shortlist / Interest Toggle Button (AJAX) -->
                    <button class="btn btn-outline-danger w-100 py-3 rounded-3 mb-3 d-flex align-items-center justify-content-center gap-2 fw-bold btn-shortlist-action <?= $isInterested ? 'active' : '' ?>" data-property-id="<?= $propertyId ?>">
                        <i class="bi <?= $isInterested ? 'bi-heart-fill text-danger' : 'bi-heart' ?> fs-5"></i>
                        <span class="btn-label"><?= $isInterested ? 'Shortlisted' : 'Add to Shortlist' ?></span>
                    </button>

                    <!-- Contact Property Button (Opens Modal) -->
                    <button type="button" class="btn btn-primary w-100 py-3 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#contactPropertyModal">
                        <i class="bi bi-chat-dots-fill"></i>
                        <span>Contact Property</span>
                    </button>

                    <div class="text-center mt-3">
                        <small class="text-muted d-flex align-items-center justify-content-center gap-1">
                            <i class="bi bi-shield-check text-success"></i> Instant visit scheduling &amp; zero commission
                        </small>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Contact Property Modal -->
    <div class="modal fade" id="contactPropertyModal" tabindex="-1" aria-labelledby="contactModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-bottom pb-3">
                    <h5 class="modal-title fw-bold" id="contactModalTitle">
                        <i class="bi bi-building-check text-primary me-2"></i>Contact Property Manager
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="p-3 bg-light rounded-3 mb-4">
                        <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($property['name']) ?></h6>
                        <small class="text-muted"><?= htmlspecialchars($property['address']) ?>, <?= htmlspecialchars($property['city']) ?></small>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary-subtle text-primary p-3 rounded-circle" style="width: 46px; height: 46px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-person-fill fs-5"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Property Representative</small>
                                <strong class="text-dark">StayNest Campus Care Team (<?= htmlspecialchars($property['city']) ?>)</strong>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success-subtle text-success p-3 rounded-circle" style="width: 46px; height: 46px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-telephone-fill fs-5"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Direct Phone</small>
                                <strong class="text-dark">+91 91234 56789</strong>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-warning-subtle text-warning p-3 rounded-circle" style="width: 46px; height: 46px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-envelope-fill fs-5"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Official Email</small>
                                <strong class="text-dark">support@staynest.in</strong>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-info-subtle text-info p-3 rounded-circle" style="width: 46px; height: 46px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-calendar-event fs-5"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Viewing Hours</small>
                                <strong class="text-dark">Daily 10:00 AM – 6:00 PM</strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" onclick="showToast('Visit request scheduled! The accommodation manager will call you within 2 hours.', 'success'); bootstrap.Modal.getInstance(document.getElementById('contactPropertyModal')).hide();">
                        <i class="bi bi-calendar-check me-1"></i> Schedule a Free Visit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reusable Footer -->
    <?php include __DIR__ . '/components/footer.php'; ?>

    <!-- Image Gallery Switcher Script -->
    <script>
        function switchGalleryImage(thumbnailElement, newSrc) {
            const mainImg = document.getElementById('gallery-active-image');
            if (mainImg) {
                mainImg.style.opacity = '0.3';
                setTimeout(() => {
                    mainImg.src = newSrc;
                    mainImg.style.opacity = '1';
                }, 150);
            }
            document.querySelectorAll('.gallery-thumbnail-item').forEach(item => {
                item.classList.remove('active');
            });
            thumbnailElement.classList.add('active');
        }
    </script>
</body>
</html>
