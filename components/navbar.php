<?php
/**
 * StayNest - Reusable Navbar Component
 * Displays branding, navigation links, dynamic authentication state, and live shortlist count.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentUser = getAuthUser();
$shortlistCount = 0;

if ($currentUser) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM interested_users WHERE user_id = ?");
        $stmt->execute([$currentUser['id']]);
        $shortlistCount = (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        $shortlistCount = 0;
    }
}

// Determine active page
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-staynest sticky-top">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand navbar-brand-logo" href="index.php">
            <i class="bi bi-houses-fill"></i>
            <span>StayNest</span>
        </a>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarStayNestContent" aria-controls="navbarStayNestContent" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list fs-2 text-dark"></i>
        </button>

        <!-- Navbar Links & Auth -->
        <div class="collapse navbar-collapse" id="navbarStayNestContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>" href="index.php">
                        <i class="bi bi-house-door me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'properties.php' && !isset($_GET['view'])) ? 'active' : '' ?>" href="properties.php">
                        <i class="bi bi-search me-1"></i> Find PG
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (isset($_GET['view']) && $_GET['view'] === 'react') ? 'active' : '' ?>" href="properties.php?view=react">
                        <i class="bi bi-cpu me-1 text-primary"></i> React Explorer
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'shortlist.php' ? 'active' : '' ?>" href="shortlist.php">
                        <i class="bi bi-heart me-1 text-danger"></i> Shortlist
                        <span class="badge bg-danger rounded-pill nav-shortlist-badge <?= $shortlistCount > 0 ? '' : 'd-none' ?>">
                            <?= $shortlistCount ?>
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php#about">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php#contact">Contact</a>
                </li>
            </ul>

            <!-- Auth Buttons Right Side -->
            <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                <?php if ($currentUser): ?>
                    <!-- Logged-in User Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle d-flex align-items-center gap-2 rounded-pill px-3 py-2" type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-5"></i>
                            <span class="fw-bold"><?= htmlspecialchars($currentUser['name']) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 mt-2" aria-labelledby="userMenuDropdown">
                            <li class="px-3 py-2 border-bottom">
                                <small class="text-muted d-block">Signed in as</small>
                                <strong class="text-truncate d-block" style="max-width: 200px;"><?= htmlspecialchars($currentUser['email']) ?></strong>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="profile.php">
                                    <i class="bi bi-person me-2 text-primary"></i> My Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="shortlist.php">
                                    <i class="bi bi-heart me-2 text-danger"></i> My Shortlist
                                    <span class="badge bg-danger ms-2 nav-shortlist-badge <?= $shortlistCount > 0 ? '' : 'd-none' ?>">
                                        <?= $shortlistCount ?>
                                    </span>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2 text-danger" href="logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i> Log Out
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Guest Buttons -->
                    <a href="login.php" class="btn btn-outline-primary px-3 py-2 rounded-pill">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login
                    </a>
                    <a href="register.php" class="btn btn-primary px-3 py-2 rounded-pill text-white">
                        <i class="bi bi-person-plus me-1"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
