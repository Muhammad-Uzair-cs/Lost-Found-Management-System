<?php
// admin/manage-users.php
define('PAGE_TITLE', 'Manage Users');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$msg = $err = '';

// Toggle active status
if (isset($_GET['toggle']) && (int)$_GET['toggle'] !== currentUserId()) {
    $uid = (int)$_GET['toggle'];
    $db  = getDB();
    $s   = $db->prepare("UPDATE users SET is_active = 1 - is_active WHERE user_id = ?");
    if ($s->execute([$uid])) $msg = 'User status updated.';
    else $err = 'Failed to update user.';
}

$db    = getDB();
$users = $db->query(
    "SELECT u.*, COUNT(DISTINCT li.item_id) AS lost_count, COUNT(DISTINCT fi.item_id) AS found_count
     FROM users u
     LEFT JOIN lost_items  li ON u.user_id = li.user_id
     LEFT JOIN found_items fi ON u.user_id = fi.user_id
     GROUP BY u.user_id
     ORDER BY u.created_at DESC"
)->fetchAll();
?>
<?php require __DIR__ . '/../includes/header.php'; ?>

<div class="page-header" style="background:linear-gradient(135deg,#1e1b4b 0%,#3730a3 100%);">
  <div class="container">
    <a href="<?= SITE_URL ?>/admin/index.php" style="color:rgba(255,255,255,.7);font-size:.88rem;"><i class="fa fa-arrow-left"></i> Admin Dashboard</a>
    <h1 style="margin-top:8px;"><i class="fa fa-users"></i> Manage Users</h1>
    <p>View and manage all registered university accounts.</p>
  </div>
</div>

<div class="page-body">
<div class="container">

  <?php if ($msg): ?><div class="alert alert-success" data-auto-dismiss="1"><i class="fa fa-check"></i> <?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-error"   data-auto-dismiss="1"><i class="fa fa-exclamation"></i> <?= htmlspecialchars($err) ?></div><?php endif; ?>

  <div class="card">
    <div class="card-header">
      <h2>All Users</h2>
      <span style="font-size:.85rem;color:var(--text-muted)"><?= count($users) ?> total</span>
    </div>
    <div class="card-body" style="padding:0;">
      <div class="table-wrapper">
      <table>
        <thead>
          <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Lost</th><th>Found</th><th>Joined</th><th>Status</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td style="color:var(--text-muted);font-size:.82rem;"><?= $u['user_id'] ?></td>
            <td><strong><?= sanitize($u['full_name']) ?></strong></td>
            <td style="font-size:.85rem;"><?= sanitize($u['email']) ?></td>
            <td style="font-size:.85rem;"><?= sanitize($u['phone'] ?? '—') ?></td>
            <td>
              <span class="badge <?= $u['role']==='admin'?'badge-verified':'badge-open' ?>">
                <?= ucfirst($u['role']) ?>
              </span>
            </td>
            <td style="text-align:center;"><?= $u['lost_count'] ?></td>
            <td style="text-align:center;"><?= $u['found_count'] ?></td>
            <td style="font-size:.82rem;white-space:nowrap;"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
            <td>
              <span class="badge <?= $u['is_active'] ? 'badge-approved' : 'badge-rejected' ?>">
                <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
              </span>
            </td>
            <td>
              <?php if ($u['user_id'] != currentUserId()): ?>
              <a href="?toggle=<?= $u['user_id'] ?>" class="btn btn-outline btn-sm"
                 data-confirm="<?= $u['is_active'] ? 'Deactivate' : 'Activate' ?> this user?">
                <?= $u['is_active'] ? '<i class="fa fa-ban"></i> Disable' : '<i class="fa fa-check"></i> Enable' ?>
              </a>
              <?php else: ?>
              <span style="font-size:.8rem;color:var(--text-muted);">You</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>

</div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
