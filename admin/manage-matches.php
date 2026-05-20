<?php
// admin/manage-matches.php
define('PAGE_TITLE', 'Manage Matches');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$msg = $err = '';
if (isset($_GET['id']) && isset($_GET['action'])) {
    $id     = (int)$_GET['id'];
    $action = in_array($_GET['action'], ['verified','rejected']) ? $_GET['action'] : '';
    if ($action && verifyMatch($id, $action, currentUserId())) {
        $msg = 'Match ' . ucfirst($action) . '.';
    } else {
        $err = 'Could not update match.';
    }
}

$matches = getMatches();
$filter  = $_GET['status'] ?? '';
if ($filter) $matches = array_filter($matches, fn($m) => $m['status'] === $filter);
?>
<?php require __DIR__ . '/../includes/header.php'; ?>

<div class="page-header" style="background:linear-gradient(135deg,#1e1b4b 0%,#3730a3 100%);">
  <div class="container">
    <a href="<?= SITE_URL ?>/admin/index.php" style="color:rgba(255,255,255,.7);font-size:.88rem;"><i class="fa fa-arrow-left"></i> Admin Dashboard</a>
    <h1 style="margin-top:8px;"><i class="fa fa-link"></i> Manage Matches</h1>
    <p>Verify or reject auto-detected matches.</p>
  </div>
</div>

<div class="page-body">
<div class="container">

  <?php if ($msg): ?><div class="alert alert-success" data-auto-dismiss="1"><i class="fa fa-check"></i> <?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-error"   data-auto-dismiss="1"><i class="fa fa-exclamation"></i> <?= htmlspecialchars($err) ?></div><?php endif; ?>

  <!-- Filter -->
  <div class="card mb-3">
    <div class="card-body" style="padding:14px 20px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
      <?php foreach ([''=>'All','pending'=>'Pending','verified'=>'Verified','rejected'=>'Rejected'] as $v=>$l): ?>
      <a href="?status=<?= $v ?>" class="btn btn-sm <?= $filter===$v?'btn-primary':'btn-outline' ?>"><?= $l ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h2>All Matches</h2></div>
    <div class="card-body" style="padding:0;">
      <div class="table-wrapper">
      <table>
        <thead>
          <tr><th>Lost Item</th><th>Found Item</th><th>Category</th><th>Score</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($matches as $m): ?>
          <tr>
            <td>
              <div><?= sanitize($m['lost_name']) ?></div>
              <div style="font-size:.78rem;color:var(--text-muted)"><?= sanitize($m['lost_loc']) ?> · <?= date('d M', strtotime($m['date_lost'])) ?></div>
            </td>
            <td>
              <div><?= sanitize($m['found_name']) ?></div>
              <div style="font-size:.78rem;color:var(--text-muted)"><?= sanitize($m['found_loc']) ?> · <?= date('d M', strtotime($m['date_found'])) ?></div>
            </td>
            <td><span class="badge badge-open"><?= sanitize($m['category']) ?></span></td>
            <td>
              <strong style="color:<?= $m['score']>=70?'var(--success)':($m['score']>=40?'var(--warning)':'var(--danger)') ?>">
                <?= round($m['score']) ?>%
              </strong>
            </td>
            <td><?= statusBadge($m['status']) ?></td>
            <td>
              <?php if ($m['status'] === 'pending'): ?>
              <div style="display:flex;gap:6px;">
                <a href="?id=<?= $m['match_id'] ?>&action=verified&status=<?= $filter ?>" class="btn btn-success btn-sm"
                   data-confirm="Verify this match?"><i class="fa fa-check"></i> Verify</a>
                <a href="?id=<?= $m['match_id'] ?>&action=rejected&status=<?= $filter ?>" class="btn btn-danger btn-sm"
                   data-confirm="Reject this match?"><i class="fa fa-times"></i> Reject</a>
              </div>
              <?php else: ?>
              <span style="font-size:.82rem;color:var(--text-muted);">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$matches): ?>
          <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted);">No matches found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>

</div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
