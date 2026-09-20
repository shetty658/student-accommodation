<?php
/**
 * StayNest - User Login Page
 * Supports traditional form POST and modern AJAX login with password verification.
 */

require_once __DIR__ . '/api/db.php';

// If already logged in, redirect
if (getAuthUserId()) {
    header('Location: properties.php');
    exit;
}

$redirectUrl = isset($_GET['redirect']) ? htmlspecialchars($_GET['redirect']) : 'properties.php';
$errorMessage = '';

// Handle standard form POST fallback
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errorMessage = 'Please enter both email address and password.';
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, name, email, password, phone FROM users WHERE LOWER(email) = LOWER(?)");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_phone'] = $user['phone'];

                header("Location: {$redirectUrl}");
                exit;
            } else {
                $errorMessage = 'Invalid email address or password. Please try again.';
            }
        } catch (Exception $e) {
            $errorMessage = 'Login service error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | StayNest Student Accommodation</title>
    <meta name="description" content="Sign in to StayNest to access your shortlisted accommodations, schedule viewings, and manage your student profile.">

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
            <div class="col-md-7 col-lg-5">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-box-arrow-in-right fs-2"></i>
                            </div>
                            <h1 class="h3 fw-bold text-dark">Welcome Back</h1>
                            <p class="text-muted small">Sign in to manage your shortlisted student PGs</p>
                        </div>

                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 small py-2 px-3 mb-4" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <div><?= htmlspecialchars($errorMessage) ?></div>
                            </div>
                        <?php endif; ?>

                        <!-- Login Form -->
                        <form id="staynest-login-form" method="POST" action="login.php?redirect=<?= urlencode($redirectUrl) ?>">
                            <div class="mb-3">
                                <label for="login-email" class="form-label small fw-bold">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" id="login-email" class="form-control border-start-0" placeholder="e.g. rahul@student.edu" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label for="login-password" class="form-label small fw-bold">Password</label>
                                    <span class="text-muted small" style="cursor: pointer;" onclick="togglePasswordVisibility()"><i class="bi bi-eye"></i> Show</span>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" id="login-password" class="form-control border-start-0" placeholder="••••••••" required>
                                </div>
                            </div>

                            <button type="submit" id="btn-login-submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3 mb-3">
                                <span>Sign In</span>
                            </button>

                            <div class="text-center small text-muted">
                                Don't have an account yet? <a href="register.php" class="fw-bold text-primary">Register here</a>
                            </div>
                        </form>

                        <!-- Academic Quick Demo Accounts -->
                        <div class="mt-4 pt-3 border-top">
                            <span class="text-muted small d-block mb-2 text-center fw-semibold">Quick 1-Click Demo Accounts:</span>
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary text-start py-2 px-3 rounded-3" onclick="fillDemoCredentials('rahul@student.edu', 'Password123!')">
                                    <i class="bi bi-person-check me-2 text-success"></i> Rahul Sharma (Bengaluru Shortlists)
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary text-start py-2 px-3 rounded-3" onclick="fillDemoCredentials('priya@student.edu', 'Password123!')">
                                    <i class="bi bi-person-check me-2 text-primary"></i> Priya Kulkarni (Mysuru &amp; Pune Shortlists)
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reusable Footer -->
    <?php include __DIR__ . '/components/footer.php'; ?>

    <script>
        function fillDemoCredentials(email, password) {
            document.getElementById('login-email').value = email;
            document.getElementById('login-password').value = password;
            showToast('Demo credentials filled! Click Sign In to continue.', 'info');
        }

        function togglePasswordVisibility() {
            const pwd = document.getElementById('login-password');
            if (pwd.type === 'password') {
                pwd.type = 'text';
            } else {
                pwd.type = 'password';
            }
        }
    </script>
</body>
</html>
