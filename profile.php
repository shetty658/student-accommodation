<?php
/**
 * StayNest - Student Profile Dashboard (profile.php)
 * Protected view displaying account details and accommodation activity.
 */

require_once __DIR__ . '/api/db.php';

$userId = getAuthUserId();
if (!$userId) {
    header('Location: login.php?redirect=' . urlencode('profile.php'));
    exit;
}

$pdo = getDBConnection();
$currentUser = getAuthUser();

// Fetch fresh user record
$stmt = $pdo->prepare("SELECT id, name, email, phone, created_at FROM users WHERE id = ?");
$stmt->execute([$userId]);
$userProfile = $stmt->fetch();

if (!$userProfile) {
    header('Location: logout.php');
    exit;
}

// Fetch shortlist count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM interested_users WHERE user_id = ?");
$countStmt->execute([$userId]);
$shortlistCount = (int)$countStmt->fetchColumn();

// Update feedback message
$feedbackMessage = '';
$feedbackType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $newName = trim($_POST['name'] ?? '');
    $newPhone = trim($_POST['phone'] ?? '');

    if (strlen($newName) < 2 || strlen($newPhone) < 8) {
        $feedbackMessage = 'Please enter valid name and contact phone details.';
        $feedbackType = 'danger';
    } else {
        try {
            $upd = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
            $upd->execute([$newName, $newPhone, $userId]);
            $_SESSION['user_name'] = $newName;
            $_SESSION['user_phone'] = $newPhone;
            $userProfile['name'] = $newName;
            $userProfile['phone'] = $newPhone;
            $feedbackMessage = 'Profile updated successfully!';
        } catch (Exception $e) {
            $feedbackMessage = 'Update failed: ' . $e->getMessage();
            $feedbackType = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Student Profile | StayNest</title>
    <meta name="description" content="View and manage your StayNest account information, contact preferences, and student accommodation shortlists.">

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    <!-- Reusable Navbar -->
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <div class="container py-5 flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <?php if (!empty($feedbackMessage)): ?>
                    <div class="alert alert-<?= $feedbackType ?> rounded-3 shadow-sm mb-4" role="alert">
                        <i class="bi bi-info-circle me-1"></i> <?= htmlspecialchars($feedbackMessage) ?>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <!-- Profile Header Banner -->
                    <div class="p-4 p-md-5 text-white" style="background: linear-gradient(135deg, #1e3a8a, #0f172a);">
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-4">
                            <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center shadow-lg" style="width: 80px; height: 80px; font-size: 2.5rem; font-weight: 800;">
                                <?= strtoupper(substr($userProfile['name'], 0, 1)) ?>
                            </div>
                            <div>
                                <h1 class="h3 fw-bold mb-1"><?= htmlspecialchars($userProfile['name']) ?></h1>
                                <div class="text-white-50 small mb-2"><?= htmlspecialchars($userProfile['email']) ?></div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                    <i class="bi bi-patch-check-fill me-1"></i> Verified Student Scholar
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Stats -->
                    <div class="row g-0 border-bottom text-center bg-white">
                        <div class="col-6 py-3 border-end">
                            <div class="fs-4 fw-bold text-dark"><?= $shortlistCount ?></div>
                            <div class="text-muted small">Shortlisted PGs</div>
                        </div>
                        <div class="col-6 py-3">
                            <div class="fs-4 fw-bold text-dark"><?= date('M Y', strtotime($userProfile['created_at'])) ?></div>
                            <div class="text-muted small">Member Since</div>
                        </div>
                    </div>

                    <!-- Edit Profile Form -->
                    <div class="card-body p-4 p-md-5">
                        <h2 class="h5 fw-bold text-dark mb-4">Account Information</h2>
                        <form method="POST" action="profile.php">
                            <input type="hidden" name="action" value="update_profile">
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Full Name</label>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($userProfile['name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Email Address</label>
                                    <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($userProfile['email']) ?>" readonly disabled>
                                    <small class="text-muted">Email cannot be modified.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Contact Phone</label>
                                    <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($userProfile['phone']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Student Verification Status</label>
                                    <input type="text" class="form-control bg-light" value="Active / Validated" readonly disabled>
                                </div>
                            </div>

                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                                <button type="submit" class="btn btn-primary px-4 py-2.5 rounded-pill fw-bold">
                                    <i class="bi bi-check2 me-1"></i> Save Changes
                                </button>
                                <a href="shortlist.php" class="btn btn-outline-danger px-4 py-2.5 rounded-pill fw-bold">
                                    <i class="bi bi-heart me-1"></i> View My Shortlist (<?= $shortlistCount ?>)
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Reusable Footer -->
    <?php include __DIR__ . '/components/footer.php'; ?>

</body>
</html>
