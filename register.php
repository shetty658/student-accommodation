<?php
/**
 * StayNest - Student Registration Page
 * Allows new students to create an account with secure password hashing.
 */

require_once __DIR__ . '/api/db.php';

// If already logged in, redirect
if (getAuthUserId()) {
    header('Location: properties.php');
    exit;
}

$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $errors = [];

    if (empty($name) || strlen($name) < 2) {
        $errors[] = 'Full Name must be at least 2 characters.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (empty($phone) || strlen($phone) < 8) {
        $errors[] = 'Please enter a valid phone number.';
    }
    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (!empty($errors)) {
        $errorMessage = implode('<br>', $errors);
    } else {
        try {
            $pdo = getDBConnection();

            // Check duplicate email
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(?)");
            $checkStmt->execute([$email]);
            if ($checkStmt->fetch()) {
                $errorMessage = 'An account with this email address already exists. Please log in.';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $insStmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, created_at) VALUES (?, ?, ?, ?, ?)");
                $insStmt->execute([$name, $email, $hashedPassword, $phone, date('Y-m-d H:i:s')]);

                $newId = (int)$pdo->lastInsertId();
                $_SESSION['user_id'] = $newId;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_phone'] = $phone;

                header('Location: properties.php');
                exit;
            }
        } catch (Exception $e) {
            $errorMessage = 'Registration error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an Account | StayNest</title>
    <meta name="description" content="Register with StayNest to discover student accommodations, save shortlists, and connect directly with verified property hosts.">

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    <!-- Reusable Navbar -->
    <?php include __DIR__ . '/components/navbar.php'; ?>

    <div class="container my-auto py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle mb-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-person-plus-fill fs-2"></i>
                            </div>
                            <h1 class="h3 fw-bold text-dark">Student Registration</h1>
                            <p class="text-muted small">Create your account to start shortlisting and booking student PGs</p>
                        </div>

                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 small py-2 px-3 mb-4" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <div><?= $errorMessage ?></div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="register.php">
                            <!-- Full Name -->
                            <div class="mb-3">
                                <label for="reg-name" class="form-label small fw-bold">Full Name *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-person"></i></span>
                                    <input type="text" name="name" id="reg-name" class="form-control border-start-0" placeholder="e.g. Siddharth Deshmukh" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                                </div>
                            </div>

                            <!-- Email Address -->
                            <div class="mb-3">
                                <label for="reg-email" class="form-label small fw-bold">Email Address *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" id="reg-email" class="form-control border-start-0" placeholder="siddharth@student.edu" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                                </div>
                            </div>

                            <!-- Phone Number -->
                            <div class="mb-3">
                                <label for="reg-phone" class="form-label small fw-bold">Phone Number *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-telephone"></i></span>
                                    <input type="tel" name="phone" id="reg-phone" class="form-control border-start-0" placeholder="+91 98765 43210" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                                </div>
                            </div>

                            <!-- Password -->
                            <div class="row g-3 mb-4">
                                <div class="col-sm-6">
                                    <label for="reg-password" class="form-label small fw-bold">Password *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                        <input type="password" name="password" id="reg-password" class="form-control border-start-0" placeholder="Min 6 chars" required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <label for="reg-confirm-password" class="form-label small fw-bold">Confirm Password *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-shield-check"></i></span>
                                        <input type="password" name="confirm_password" id="reg-confirm-password" class="form-control border-start-0" placeholder="Re-type password" required>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3 mb-3">
                                <i class="bi bi-check2-circle me-1"></i> Register Account
                            </button>

                            <div class="text-center small text-muted">
                                Already registered? <a href="login.php" class="fw-bold text-primary">Sign in here</a>
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
