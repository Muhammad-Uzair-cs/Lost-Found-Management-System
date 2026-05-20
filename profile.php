<?php
// profile.php
define('PAGE_TITLE', 'My Profile');
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$user  = currentUser();
$msg   = $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone']     ?? '');
    $pass  = $_POST['new_password']   ?? '';
    $pass2 = $_POST['confirm_password'] ?? '';

    if (!$name) {
        $err = 'Name cannot be empty.';
    } elseif ($pass && strlen($pass) < 6) {
        $err = 'New password must be at least 6 characters.';
    } elseif ($pass && $pass !== $pass2) {
        $err = 'Passwords do not match.';
    } else {
        $db = getDB();
        if ($pass) {
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $s = $db->prepare("UPDATE users SET full_name=?, phone=?, password=? WHERE user_id=?");
            $s->execute([$name, $phone, $hash, currentUserId()]);
        } else {
            $s = $db->prepare("UPDATE users SET full_name=?, phone=? WHERE user_id=?");
            $s->execute([$name, $phone, currentUserId()]);
        }
        $_SESSION['full_name'] = $name;
        $msg = 'Profile updated successfully.';
        $user = currentUser();
    }
}

$myLost  = getLostItems(currentUserId());
$myFound = getFoundItems(currentUserId());
$myClaims = getClaims(currentUserId());
?>
<?php require __DIR__ . '/includes/header.php'; ?>

<div class="page-header">
  <div class="container">
    <h1><i class="fa fa-user-circle"></i> My Profile</h1>
    <p>View your activity and update your account information.</p>
  </div>
</div>

<div class="page-body">
<div class="container">
<div class="two-col">

  <div style="display:flex;flex-direction:column;gap:20px;">
    <?php if ($msg): ?><div class="alert alert-success" data-auto-dismiss="1"><i class="fa fa-check"></i> <?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-error"   data-auto-dismiss="1"><i class="fa fa-exclamation"></i> <?= htmlspecialchars($err) ?></div><?php endif; ?>

    <div class="card">
      <div class="card-header"><h2>Edit Profile</h2></div>
      <div class="card-body">
        <form method="POST">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Full Name</label>
              <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Phone</label>
              <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Email (read-only)</label>
            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
          </div>
          <hr style="border:none;border-top:1px solid var(--border);margin:18px 0;">
          <h4 style="font-size:.9rem;font-weight:600;margin-bottom:14px;">Change Password (leave blank to keep current)</h4>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">New Password</label>
              <input type="password" name="new_password" class="form-control" placeholder="Min 6 chars">
            </div>
            <div class="form-group">
              <label class="form-label">Confirm Password</label>
              <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password">
            </div>
          </div>
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Changes</button>
        </form>
      </div>
    </div>

    <!-- My Claims -->
    <?php if ($myClaims): ?>
    <div class="card">
      <div class="card-header"><h2>My Claims</h2></div>
      <div class="card-body" style="padding:0;">
        <table>
          <thead><tr><th>Item</th><th>Submitted</th><th>Status</th><th>Admin Note</th></tr></thead>
          <tbody>
            <?php foreach ($myClaims as $c): ?>
            <tr>
              <td><?= sanitize($c['item_name']) ?></td>
              <td><?= date('d M Y', strtotime($c['submitted_at'])) ?></td>
              <td><?= statusBadge($c['status']) ?></td>
              <td style="font-size:.82rem;color:var(--text-muted)"><?= htmlspecialchars($c['admin_note'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Sidebar -->
  <div>
    <div class="card sidebar-card">
      <div class="card-header"><h2>Activity Summary</h2></div>
      <div class="card-body">
        <div style="display:flex;flex-direction:column;gap:14px;">
          <div style="display:flex;justify-content:space-between;align-items:center;padding:12px;background:var(--surface);border-radius:8px;">
            <div><i class="fa fa-circle-question" style="color:var(--danger)"></i> &nbsp;Lost Reports</div>
            <strong style="font-family:var(--font-display);font-size:1.2rem;"><?= count($myLost) ?></strong>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;padding:12px;background:var(--surface);border-radius:8px;">
            <div><i class="fa fa-box-open" style="color:var(--success)"></i> &nbsp;Found Reports</div>
            <strong style="font-family:var(--font-display);font-size:1.2rem;"><?= count($myFound) ?></strong>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;padding:12px;background:var(--surface);border-radius:8px;">
            <div><i class="fa fa-hand" style="color:var(--info)"></i> &nbsp;Claims Submitted</div>
            <strong style="font-family:var(--font-display);font-size:1.2rem;"><?= count($myClaims) ?></strong>
          </div>
        </div>
        <div style="margin-top:18px;font-size:.82rem;color:var(--text-muted);">
          <i class="fa fa-calendar"></i> Joined: <?= date('d M Y', strtotime($user['created_at'])) ?>
        </div>
        <div style="margin-top:6px;font-size:.82rem;color:var(--text-muted);">
          <i class="fa fa-shield-halved"></i> Role: <?= ucfirst($user['role']) ?>
        </div>
      </div>
    </div>
  </div>

</div>
</div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
