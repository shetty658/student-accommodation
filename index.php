<?php
/**
 * StayNest - Home Page
 * Main landing page with Hero search, Featured accommodations, About cards, and Contact section.
 */

require_once __DIR__ . '/api/db.php';

$pdo = getDBConnection();
$userId = getAuthUserId();
$shortlistIds = getUserShortlistIds($pdo, $userId);

// Fetch 6 top-rated featured properties
$featuredStmt = $pdo->query("SELECT * FROM properties ORDER BY rating DESC, price ASC LIMIT 6");
$rawFeatured = $featuredStmt->fetchAll();

// Fetch amenities for featured
$amenityStmt = $pdo->prepare("
    SELECT a.id, a.name, a.icon 
    FROM property_amenities pa 
    JOIN amenities a ON pa.amenity_id = a.id 
    WHERE pa.property_id = ?
");

$featuredProperties = [];
foreach ($rawFeatured as $rf) {
    $amenityStmt->execute([$rf['id']]);
    $rf['amenities'] = $amenityStmt->fetchAll();
    $rf['is_interested'] = in_array((int)$rf['id'], $shortlistIds);
    $featuredProperties[] = $rf;
}

// Fetch cities list for hero selector
$citiesStmt = $pdo->query("SELECT DISTINCT city FROM properties ORDER BY city ASC");
$availableCities = $citiesStmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayNest | Find Your Ideal Student Accommodation & PG</title>
    <meta name="description" content="Affordable, safe and student-friendly accommodation near major universities and campuses across India. Explore verified PGs, compare prices, and shortlist effortlessly.">

    <!-- Bootstrap 5 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom Style -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Reusable Navbar -->
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <!-- 1. Hero Section -->
    <header class="hero-section">
        <div class="container">
            <div class="row align-items-center justify-content-between">
                <div class="col-lg-7 mb-4 mb-lg-0">
                    <span class="badge bg-success bg-opacity-25 text-white border border-success border-opacity-50 px-3 py-2 rounded-pill fw-semibold mb-3">
                        <i class="bi bi-shield-check me-1"></i> 100% Verified Student Housing
                    </span>
                    <h1 class="hero-title">Find a place you'll love to live.</h1>
                    <p class="hero-subtitle">
                        Affordable, safe and student-friendly accommodation near your campus. Browse verified student PGs, compare amenities, and find your next home.
                    </p>

                    <!-- Quick City Badges -->
                    <div class="d-flex flex-wrap align-items-center gap-2 pt-2">
                        <span class="text-white-50 small fw-bold text-uppercase">Popular Hubs:</span>
                        <?php foreach ($availableCities as $c): ?>
                            <a href="properties.php?city=<?= urlencode($c) ?>" class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-25 text-decoration-none px-3 py-2 rounded-pill hover-lift">
                                📍 <?= htmlspecialchars($c) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Hero Search Widget -->
                <div class="col-lg-5">
                    <div class="hero-search-card">
                        <h2 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-search text-primary"></i> Search Accommodations
                        </h2>
                        <form action="properties.php" method="GET">
                            <!-- City -->
                            <div class="mb-3">
                                <label for="hero-city">City / Campus Area</label>
                                <select name="city" id="hero-city" class="form-select">
                                    <option value="All">All Cities</option>
                                    <?php foreach ($availableCities as $c): ?>
                                        <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Budget -->
                            <div class="mb-3">
                                <label for="hero-budget">Monthly Budget</label>
                                <select name="budget" id="hero-budget" class="form-select">
                                    <option value="All">Any Budget</option>
                                    <option value="under_5000">Under ₹5,000</option>
                                    <option value="5000_7000">₹5,000 – ₹7,000</option>
                                    <option value="7000_10000">₹7,000 – ₹10,000</option>
                                    <option value="above_10000">Above ₹10,000</option>
                                </select>
                            </div>

                            <!-- Gender -->
                            <div class="mb-4">
                                <label for="hero-gender">Accommodation Type</label>
                                <select name="gender" id="hero-gender" class="form-select">
                                    <option value="All">All Categories</option>
                                    <option value="Male">Male PG</option>
                                    <option value="Female">Female PG</option>
                                    <option value="Co-living">Co-living / Unisex</option>
                                </select>
                            </div>

                            <!-- Submit CTA -->
                            <button type="submit" class="btn btn-search-hero w-100 py-3 fs-6">
                                <i class="bi bi-search"></i>
                                <span>Find PG Accommodations</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- 2. Highlights / Key Metrics Bar -->
    <section class="py-4 bg-white border-bottom shadow-sm">
        <div class="container">
            <div class="row text-center g-3">
                <div class="col-6 col-md-3">
                    <div class="fw-bold text-dark fs-3">15+</div>
                    <div class="text-muted small">Verified PG Listings</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="fw-bold text-dark fs-3">6</div>
                    <div class="text-muted small">Major Student Hubs</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="fw-bold text-dark fs-3">₹4,000</div>
                    <div class="text-muted small">Starting Price / Month</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="fw-bold text-dark fs-3">100%</div>
                    <div class="text-muted small">Zero Brokerage Fee</div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. Featured Accommodations Section -->
    <section class="py-5">
        <div class="container py-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
                <div>
                    <span class="text-primary fw-bold text-uppercase small letter-spacing-1">Handpicked Residences</span>
                    <h2 class="fw-bold text-dark mt-1">Featured Student Accommodations</h2>
                    <p class="text-muted mb-0">Explore our most popular and highly rated PG homes across top educational hubs.</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="properties.php" class="btn btn-outline-primary px-4 py-2 rounded-pill fw-semibold">
                        View All Accommodations <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- Property Cards Grid (3 on desktop, 2 on tablet, 1 on mobile) -->
            <div class="row g-4">
                <?php foreach ($featuredProperties as $property): ?>
                    <?php include __DIR__ . '/components/property-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- 4. About Section (3 Feature Cards) -->
    <section id="about" class="py-5 bg-white border-top border-bottom">
        <div class="container py-4">
            <div class="text-center max-w-700 mx-auto mb-5" style="max-width: 720px;">
                <span class="text-success fw-bold text-uppercase small letter-spacing-1">Why Choose StayNest</span>
                <h2 class="fw-bold text-dark mt-1">Everything You Need For Hassle-Free Student Living</h2>
                <p class="text-muted">
                    StayNest is a student-focused accommodation platform designed to help students discover affordable and convenient PGs near their college or workplace.
                </p>
            </div>

            <div class="row g-4">
                <!-- Feature Card 1 -->
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper">
                            <i class="bi bi-search"></i>
                        </div>
                        <h3 class="h5 fw-bold text-dark mb-2">🔎 Easy Search</h3>
                        <p class="text-muted mb-0">
                            Search and filter PGs quickly by city, monthly budget, gender requirements, and amenities without full-page reloads.
                        </p>
                    </div>
                </div>

                <!-- Feature Card 2 -->
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper" style="background-color: var(--accent-light); color: var(--accent);">
                            <i class="bi bi-house-check"></i>
                        </div>
                        <h3 class="h5 fw-bold text-dark mb-2">🏠 Verified Accommodation</h3>
                        <p class="text-muted mb-0">
                            Explore detailed accommodation information, verified room photos, transparent pricing, security deposits, and amenities.
                        </p>
                    </div>
                </div>

                <!-- Feature Card 3 -->
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon-wrapper" style="background-color: var(--danger-light); color: var(--danger);">
                            <i class="bi bi-heart-fill"></i>
                        </div>
                        <h3 class="h5 fw-bold text-dark mb-2">❤️ Easy Shortlisting</h3>
                        <p class="text-muted mb-0">
                            Save properties for later with a single click. Keep track of your favorite hostels and compare options before scheduling visits.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. Interactive Contact Section -->
    <section id="contact" class="py-5 bg-light">
        <div class="container py-4">
            <div class="row align-items-center g-5">
                <div class="col-lg-5">
                    <span class="text-primary fw-bold text-uppercase small">Get in Touch</span>
                    <h2 class="fw-bold text-dark mt-1 mb-3">Have Questions About Finding Your Ideal PG?</h2>
                    <p class="text-muted mb-4">
                        Our student advisory team is here to assist you with accommodation inquiries, campus proximity guidelines, and landlord verifications.
                    </p>

                    <div class="d-flex flex-column gap-3 mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white rounded-circle shadow-sm p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-telephone-fill text-success fs-5"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Student Helpline</div>
                                <div class="fw-bold text-dark">+91 91234 56789</div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white rounded-circle shadow-sm p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-envelope-fill text-primary fs-5"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Email Inquiries</div>
                                <div class="fw-bold text-dark">support@staynest.in</div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white rounded-circle shadow-sm p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-clock-fill text-warning fs-5"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Operating Hours</div>
                                <div class="fw-bold text-dark">Mon – Sat: 9:00 AM – 7:00 PM</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form Card -->
                <div class="col-lg-7">
                    <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border">
                        <h3 class="h4 fw-bold text-dark mb-4">Send Us a Message</h3>
                        <form id="staynest-contact-form">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="contact-name" class="form-label fw-semibold small">Full Name *</label>
                                    <input type="text" id="contact-name" class="form-control py-2" placeholder="e.g. Ananya Rao" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="contact-email" class="form-label fw-semibold small">Email Address *</label>
                                    <input type="email" id="contact-email" class="form-control py-2" placeholder="ananya@student.edu" required>
                                </div>
                                <div class="col-12">
                                    <label for="contact-subject" class="form-label fw-semibold small">Subject</label>
                                    <input type="text" id="contact-subject" class="form-control py-2" placeholder="Looking for PG in Koramangala, Bengaluru">
                                </div>
                                <div class="col-12">
                                    <label for="contact-message" class="form-label fw-semibold small">Your Message / Query *</label>
                                    <textarea id="contact-message" class="form-control" rows="4" placeholder="Mention your university, preferred sharing type, and move-in date..." required></textarea>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary px-4 py-3 w-100 fw-bold">
                                        <i class="bi bi-send-fill me-1"></i> Send Inquiry Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Reusable Footer -->
    <?php include __DIR__ . '/components/footer.php'; ?>

</body>
</html>
