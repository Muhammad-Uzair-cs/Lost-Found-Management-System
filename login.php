<?php
// login.php
require_once __DIR__ . '/includes/auth.php';
if (isLoggedIn()) { header('Location: ' . SITE_URL . '/dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    if (!$email || !$password) {
        $error = 'Please fill in all fields.';
    } else {
        $result = loginUser($email, $password);
        if ($result['success']) {
            header('Location: ' . SITE_URL . '/dashboard.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login | Lost &amp; Found</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/main.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-header">
      <div class="auth-logo"><i class="fa-solid fa-magnifying-glass-location"></i></div>
      <div class="auth-title">Lost &amp; Found</div>
      <div class="auth-subtitle">University Management System</div>
    </div>
    <div class="auth-body">
      <h2 style="font-family:var(--font-display);font-size:1.25rem;font-weight:700;margin-bottom:20px;">Welcome Back</h2>
      <?php if ($error): ?>
        <div class="alert alert-error"><i class="fa fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="POST">
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" placeholder="you@university.edu"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:8px;">
          <i class="fa fa-right-to-bracket"></i> Sign In
        </button>
      </form>
      <div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;padding:12px 14px;margin-top:18px;font-size:.82rem;color:#92400e;">
        <strong>Demo Credentials:</strong><br>
        Admin: admin@university.edu / password<br>
        User: ahmed@student.edu / password
      </div>
    </div>
    <div class="auth-footer">
      Don't have an account? <a href="<?= SITE_URL ?>/register.php">Register here</a>
    </div>
  </div>
</div>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
