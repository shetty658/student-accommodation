<?php
/**
 * StayNest - Property Listing Page (properties.php)
 * Features real-time AJAX filtering, search, sorting, and seamless React component integration.
 */

require_once __DIR__ . '/api/db.php';

$pdo = getDBConnection();
$userId = getAuthUserId();

// Fetch initial metadata
$cities = ['Bengaluru', 'Dharwad', 'Hubballi', 'Mysuru', 'Hyderabad', 'Pune'];
$selectedCity = isset($_GET['city']) ? trim($_GET['city']) : 'All';
$selectedBudget = isset($_GET['budget']) ? trim($_GET['budget']) : 'All';
$selectedGender = isset($_GET['gender']) ? trim($_GET['gender']) : 'All';
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$viewMode = isset($_GET['view']) && $_GET['view'] === 'react' ? 'react' : 'ajax';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Student Accommodations & PGs | StayNest</title>
    <meta name="description" content="Search and filter student PGs by city, budget, gender, and amenities. Dynamic AJAX and React-powered accommodation search.">

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- React Component CSS if build exists -->
    <?php if (file_exists(__DIR__ . '/react/dist/assets/index.css')): ?>
        <link rel="stylesheet" href="react/dist/assets/index.css">
    <?php endif; ?>
</head>
<body class="bg-light">

    <!-- Reusable Navbar -->
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <!-- Breadcrumb & Header Bar -->
    <div class="bg-white border-bottom py-3">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb small mb-1">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Properties</li>
                        </ol>
                    </nav>
                    <h1 class="h3 fw-bold text-dark mb-0">Student Accommodations & PGs</h1>
                </div>

                <!-- View Switcher (AJAX vs React) -->
                <div class="d-flex align-items-center gap-2">
                    <span class="small fw-semibold text-muted d-none d-sm-inline">Explore Mode:</span>
                    <div class="btn-group p-1 bg-light rounded-pill border" role="group" aria-label="View Mode Switcher">
                        <a href="properties.php" class="btn btn-sm rounded-pill px-3 fw-bold <?= $viewMode === 'ajax' ? 'btn-primary text-white shadow-sm' : 'text-muted' ?>">
                            <i class="bi bi-lightning-charge me-1"></i> AJAX View
                        </a>
                        <a href="properties.php?view=react" class="btn btn-sm rounded-pill px-3 fw-bold <?= $viewMode === 'react' ? 'btn-primary text-white shadow-sm' : 'text-muted' ?>">
                            <i class="bi bi-cpu me-1"></i> React Component
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container py-4">

        <?php if ($viewMode === 'react'): ?>
            <!-- ========================================================= -->
            <!-- REACT APPLICATION MOUNT CONTAINER                         -->
            <!-- ========================================================= -->
            <div class="mb-4">
                <div class="alert alert-primary d-flex align-items-center justify-content-between rounded-3 border-0 shadow-sm" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle-fill fs-5 text-primary"></i>
                        <div>
                            <strong>React Integration Active:</strong> This property listing interface is dynamically managed by a React component tree (<code class="text-primary">PropertyList &rarr; PropertyFilters, PropertyCard</code>) communicating with the PHP JSON API.
                        </div>
                    </div>
                    <a href="properties.php" class="btn btn-sm btn-outline-primary rounded-pill ms-3 text-nowrap">Switch to AJAX</a>
                </div>

                <!-- Mount Point for React App -->
                <div id="react-property-root">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-3 fw-semibold">Mounting React Property Component...</p>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- ========================================================= -->
            <!-- NATIVE AJAX LISTING WITH LIVE FILTERS                     -->
            <!-- ========================================================= -->
            <div class="row g-4">
                <!-- Mobile Filter Trigger Button (visible only on small screens) -->
                <div class="col-12 d-lg-none">
                    <button class="btn btn-outline-primary w-100 py-2.5 rounded-3 d-flex align-items-center justify-content-center gap-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFilters">
                        <i class="bi bi-sliders"></i>
                        <span>Filter & Search Options</span>
                    </button>
                </div>

                <!-- Left Sidebar Filters (Desktop) -->
                <div class="col-lg-3 d-none d-lg-block">
                    <div class="filter-sidebar sticky-top" style="top: 90px;">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                            <span class="fw-bold text-dark fs-6"><i class="bi bi-funnel text-primary me-1"></i> Filters</span>
                            <button id="btn-reset-filters" class="btn btn-sm btn-link text-decoration-none text-danger p-0 fw-semibold">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset All
                            </button>
                        </div>

                        <!-- 1. Search Keyword -->
                        <div class="mb-4">
                            <label for="filter-search" class="filter-group-title">Search</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                <input type="text" id="filter-search" class="form-control border-start-0" placeholder="PG Name, Locality..." value="<?= htmlspecialchars($searchQuery) ?>">
                            </div>
                        </div>

                        <!-- 2. City Filter -->
                        <div class="mb-4">
                            <label for="filter-city" class="filter-group-title">City</label>
                            <select id="filter-city" class="form-select">
                                <option value="All" <?= $selectedCity === 'All' ? 'selected' : '' ?>>All Cities</option>
                                <?php foreach ($cities as $c): ?>
                                    <option value="<?= $c ?>" <?= strtolower($selectedCity) === strtolower($c) ? 'selected' : '' ?>><?= $c ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- 3. Monthly Budget -->
                        <div class="mb-4">
                            <label class="filter-group-title">Monthly Budget</label>
                            <div class="d-flex flex-column gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filter-budget" id="budget-all" value="All" <?= $selectedBudget === 'All' ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="budget-all">Any Budget</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filter-budget" id="budget-1" value="under_5000" <?= $selectedBudget === 'under_5000' ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="budget-1">Under ₹5,000</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filter-budget" id="budget-2" value="5000_7000" <?= $selectedBudget === '5000_7000' ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="budget-2">₹5,000 – ₹7,000</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filter-budget" id="budget-3" value="7000_10000" <?= $selectedBudget === '7000_10000' ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="budget-3">₹7,000 – ₹10,000</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filter-budget" id="budget-4" value="above_10000" <?= $selectedBudget === 'above_10000' ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="budget-4">Above ₹10,000</label>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Gender Category -->
                        <div class="mb-4">
                            <label class="filter-group-title">Accommodation Type</label>
                            <div class="d-flex flex-column gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filter-gender" id="gender-all" value="All" <?= $selectedGender === 'All' ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="gender-all">All Categories</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filter-gender" id="gender-male" value="Male" <?= $selectedGender === 'Male' ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="gender-male">Male Only PG</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filter-gender" id="gender-female" value="Female" <?= $selectedGender === 'Female' ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="gender-female">Female Only PG</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filter-gender" id="gender-coliving" value="Co-living" <?= $selectedGender === 'Co-living' ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="gender-coliving">Co-living / Unisex</label>
                                </div>
                            </div>
                        </div>

                        <!-- 5. Minimum Rating -->
                        <div class="mb-2">
                            <label class="filter-group-title">Rating</label>
                            <div class="d-flex flex-column gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filter-rating" id="rating-0" value="0" checked>
                                    <label class="form-check-label small" for="rating-0">Any Rating</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filter-rating" id="rating-4" value="4">
                                    <label class="form-check-label small" for="rating-4">⭐ 4.0+ Stars</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filter-rating" id="rating-3" value="3">
                                    <label class="form-check-label small" for="rating-3">⭐ 3.0+ Stars</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Properties Grid Area -->
                <div class="col-lg-9">
                    <!-- Top Results Bar & Sorting -->
                    <div class="bg-white p-3 rounded-3 border mb-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span id="results-count" class="fw-bold text-dark fs-6">Loading properties...</span>
                            <span id="properties-loading" class="spinner-border spinner-border-sm text-primary" role="status"></span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label for="filter-sort" class="small text-muted text-nowrap fw-semibold">Sort By:</label>
                            <select id="filter-sort" class="form-select form-select-sm" style="min-width: 170px;">
                                <option value="">Default Recommended</option>
                                <option value="price_low">Price: Low to High</option>
                                <option value="price_high">Price: High to Low</option>
                                <option value="rating_high">Top Rated First</option>
                            </select>
                        </div>
                    </div>

                    <!-- Dynamic Property Cards Container (Updated via AJAX) -->
                    <div id="properties-container" class="row">
                        <!-- AJAX generated cards rendered by assets/js/filters.js -->
                    </div>

                    <!-- Empty State -->
                    <div id="properties-empty" class="text-center py-5 bg-white rounded-3 border d-none">
                        <div class="mb-3 text-muted">
                            <i class="bi bi-search fs-1"></i>
                        </div>
                        <h4 class="fw-bold text-dark">No properties match your filters</h4>
                        <p class="text-muted small mb-4">Try adjusting your city, budget bracket, or category to find available accommodations.</p>
                        <button type="button" onclick="document.getElementById('btn-reset-filters').click();" class="btn btn-outline-primary px-4 rounded-pill">
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Offcanvas Filter Drawer -->
            <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasFilters" aria-labelledby="offcanvasFiltersLabel">
                <div class="offcanvas-header border-bottom">
                    <h5 class="offcanvas-title fw-bold" id="offcanvasFiltersLabel">Filter Accommodations</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <p class="text-muted small">Please adjust the main desktop filters or apply changes.</p>
                    <button class="btn btn-primary w-100" data-bs-dismiss="offcanvas">View Results</button>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- Reusable Footer -->
    <?php include __DIR__ . '/components/footer.php'; ?>

    <?php if ($viewMode === 'ajax'): ?>
        <!-- AJAX Filtering Controller -->
        <script src="assets/js/filters.js"></script>
    <?php else: ?>
        <!-- React Production Bundle Integration -->
        <script type="module" src="react/dist/assets/index.js"></script>
    <?php endif; ?>

</body>
</html>
