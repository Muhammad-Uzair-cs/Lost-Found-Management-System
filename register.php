<?php
// register.php
require_once __DIR__ . '/includes/auth.php';
if (isLoggedIn()) { header('Location: ' . SITE_URL . '/dashboard.php'); exit; }

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email']     ?? '');
    $phone    = trim($_POST['phone']     ?? '');
    $pass     = $_POST['password']       ?? '';
    $pass2    = $_POST['password2']      ?? '';

    if (!$name || !$email || !$pass) {
        $error = 'Name, email and password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($pass) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($pass !== $pass2) {
        $error = 'Passwords do not match.';
    } else {
        $result = registerUser($name, $email, $pass, $phone);
        if ($result['success']) {
            $success = $result['message'];
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
<title>Register | Lost &amp; Found</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/main.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-card" style="max-width:500px;">
    <div class="auth-header">
      <div class="auth-logo"><i class="fa-solid fa-magnifying-glass-location"></i></div>
      <div class="auth-title">Create Account</div>
      <div class="auth-subtitle">Lost &amp; Found — University</div>
    </div>
    <div class="auth-body">
      <?php if ($error):   ?><div class="alert alert-error"><i class="fa fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success"><i class="fa fa-circle-check"></i> <?= htmlspecialchars($success) ?> <a href="<?= SITE_URL ?>/login.php">Login now</a></div><?php endif; ?>
      <form method="POST">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input type="text" name="full_name" class="form-control" placeholder="Ahmed Khan"
                 value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="you@student.edu"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Phone (optional)</label>
            <input type="text" name="phone" class="form-control" placeholder="03001234567"
                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Min 6 chars" required>
          </div>
          <div class="form-group">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="password2" class="form-control" placeholder="Repeat password" required>
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:4px;">
          <i class="fa fa-user-plus"></i> Register
        </button>
      </form>
    </div>
    <div class="auth-footer">
      Already have an account? <a href="<?= SITE_URL ?>/login.php">Sign in</a>
    </div>
  </div>
</div>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
