<?php
/**
 * Wayside Airbnb Management System
 * Login Page – FINAL WORKING VERSION (MySQL + XAMPP)
 */

require_once __DIR__ . '/includes/functions.php';

// If already logged in → go to dashboard
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password';
    } else {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: dashboard/admin/index.php');
                } elseif ($user['role'] === 'staff') {
                    header('Location: dashboard/staff/index.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            } else {
                $error = 'Invalid email or password';
            }
        } catch (Exception $e) {
            $error = 'Login failed. Try again.';
        }
    }
}

$page_title = 'Login';
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5 text-center">
                    <h2 class="mb-4 text-primary">
                        Wayside Airbnb
                    </h2>
                    <h4 class="mb-4">Welcome Back!</h4>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST" class="mb-4">
                        <div class="mb-3">
                            <input type="email" name="email" class="form-control form-control-lg" 
                                   placeholder="Email" value="admin@wayside.com" required>
                        </div>
                        <div class="mb-3">
                            <input type="password" name="password" class="form-control form-control-lg" 
                                   placeholder="Password" value="Admin123" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            Login Now
                        </button>
                    </form>

                    <div class="text-center">
                        <p class="mb-2">
                            <a href="register.php" class="text-decoration-none">
                                Create new account
                            </a>
                        </p>
                        <small class="text-success">
                            <strong>Demo Admin:</strong> admin@wayside.com / Admin123
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>