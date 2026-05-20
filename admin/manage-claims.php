<?php
// admin/manage-claims.php
define('PAGE_TITLE', 'Manage Claims');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$msg = $err = '';

// Handle review form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim_id'])) {
    $claimId = (int)$_POST['claim_id'];
    $status  = in_array($_POST['status'] ?? '', ['approved','rejected']) ? $_POST['status'] : '';
    $note    = trim($_POST['admin_note'] ?? '');
    if ($status) {
        if (reviewClaim($claimId, $status, $note, currentUserId())) {
            $msg = 'Claim ' . ucfirst($status) . ' successfully.';
        } else {
            $err = 'Failed to update claim.';
        }
    }
}

$claims    = getClaims();
$reviewId  = (int)($_GET['review'] ?? 0);
$reviewing = $reviewId ? array_filter($claims, fn($c) => $c['claim_id'] == $reviewId) : [];
$reviewing = $reviewing ? array_values($reviewing)[0] : null;
?>
<?php require __DIR__ . '/../includes/header.php'; ?>

<div class="page-header" style="background:linear-gradient(135deg,#1e1b4b 0%,#3730a3 100%);">
  <div class="container">
    <a href="<?= SITE_URL ?>/admin/index.php" style="color:rgba(255,255,255,.7);font-size:.88rem;"><i class="fa fa-arrow-left"></i> Admin Dashboard</a>
    <h1 style="margin-top:8px;"><i class="fa fa-file-circle-check"></i> Manage Claims</h1>
    <p>Review and approve or reject item claim requests.</p>
  </div>
</div>

<div class="page-body">
<div class="container">

  <?php if ($msg): ?><div class="alert alert-success" data-auto-dismiss="1"><i class="fa fa-circle-check"></i> <?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-error"   data-auto-dismiss="1"><i class="fa fa-circle-exclamation"></i> <?= htmlspecialchars($err) ?></div><?php endif; ?>

  <?php if ($reviewing): ?>
  <!-- Review Modal-like panel -->
  <div class="card mb-3" style="border-top:4px solid var(--primary-lt);">
    <div class="card-header">
      <h2>Reviewing Claim #<?= $reviewing['claim_id'] ?></h2>
      <a href="<?= SITE_URL ?>/admin/manage-claims.php" class="btn btn-outline btn-sm"><i class="fa fa-times"></i> Close</a>
    </div>
    <div class="card-body">
      <div class="two-col" style="gap:28px;">
        <div>
          <h3 style="font-size:.95rem;margin-bottom:12px;font-family:var(--font-display);">Claim Details</h3>
          <table style="font-size:.88rem;width:100%;">
            <tr><th style="padding:8px 0;color:var(--text-muted);font-weight:500;width:38%;vertical-align:top;">Item</th><td style="padding:8px 0;font-weight:700;"><?= sanitize($reviewing['item_name']) ?></td></tr>
            <tr><th style="padding:8px 0;color:var(--text-muted);font-weight:500;vertical-align:top;">Claimant</th><td style="padding:8px 0;"><?= sanitize($reviewing['claimant_name']) ?><br><a href="mailto:<?= sanitize($reviewing['claimant_email']) ?>" style="font-size:.82rem;"><?= sanitize($reviewing['claimant_email']) ?></a></td></tr>
            <tr><th style="padding:8px 0;color:var(--text-muted);font-weight:500;vertical-align:top;">Location</th><td style="padding:8px 0;"><?= sanitize($reviewing['building']) ?></td></tr>
            <tr><th style="padding:8px 0;color:var(--text-muted);font-weight:500;vertical-align:top;">Submitted</th><td style="padding:8px 0;"><?= date('d M Y H:i', strtotime($reviewing['submitted_at'])) ?></td></tr>
            <tr><th style="padding:8px 0;color:var(--text-muted);font-weight:500;vertical-align:top;">Current Status</th><td style="padding:8px 0;"><?= statusBadge($reviewing['status']) ?></td></tr>
          </table>

          <div style="margin-top:16px;">
            <h4 style="font-size:.88rem;font-weight:600;margin-bottom:8px;">Proof of Ownership:</h4>
            <div style="background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:14px;font-size:.88rem;line-height:1.7;">
              <?= nl2br(htmlspecialchars($reviewing['proof_desc'])) ?>
            </div>
          </div>

          <?php if ($reviewing['proof_image'] && file_exists(UPLOAD_DIR . $reviewing['proof_image'])): ?>
          <div style="margin-top:14px;">
            <h4 style="font-size:.88rem;font-weight:600;margin-bottom:8px;">Proof Image:</h4>
            <img src="<?= UPLOAD_URL . $reviewing['proof_image'] ?>" style="max-width:300px;border-radius:8px;border:1px solid var(--border);">
          </div>
          <?php endif; ?>
        </div>

        <div>
          <?php if ($reviewing['image_path'] && file_exists(UPLOAD_DIR . $reviewing['image_path'])): ?>
          <div style="margin-bottom:16px;">
            <h4 style="font-size:.88rem;font-weight:600;margin-bottom:8px;">Item Photo:</h4>
            <img src="<?= UPLOAD_URL . $reviewing['image_path'] ?>" style="width:100%;max-height:200px;object-fit:cover;border-radius:8px;">
          </div>
          <?php endif; ?>

          <?php if ($reviewing['status'] === 'pending'): ?>
          <form method="POST" style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:18px;">
            <input type="hidden" name="claim_id" value="<?= $reviewing['claim_id'] ?>">
            <h4 style="font-family:var(--font-display);margin-bottom:14px;">Admin Decision</h4>
            <div class="form-group">
              <label class="form-label">Admin Note (optional)</label>
              <textarea name="admin_note" class="form-control" rows="3"
                placeholder="Reason for approval or rejection..."></textarea>
            </div>
            <div style="display:flex;gap:10px;margin-top:4px;">
              <button type="submit" name="status" value="approved" class="btn btn-success flex-1" style="justify-content:center;">
                <i class="fa fa-check"></i> Approve
              </button>
              <button type="submit" name="status" value="rejected" class="btn btn-danger flex-1"
                      style="justify-content:center;"
                      onclick="return confirm('Reject this claim?')">
                <i class="fa fa-times"></i> Reject
              </button>
            </div>
          </form>
          <?php else: ?>
          <div class="alert alert-info">This claim has already been <?= $reviewing['status'] ?>.</div>
          <?php if ($reviewing['admin_note']): ?>
          <div style="margin-top:8px;font-size:.88rem;"><strong>Admin Note:</strong> <?= htmlspecialchars($reviewing['admin_note']) ?></div>
          <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- All Claims Table -->
  <div class="card">
    <div class="card-header"><h2>All Claim Requests</h2></div>
    <div class="card-body" style="padding:0;">
      <div class="table-wrapper">
      <table>
        <thead>
          <tr><th>#</th><th>Item</th><th>Claimant</th><th>Location</th><th>Submitted</th><th>Status</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($claims as $c): ?>
          <tr>
            <td style="color:var(--text-muted);font-size:.82rem;"><?= $c['claim_id'] ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:8px;">
                <?php if ($c['image_path'] && file_exists(UPLOAD_DIR . $c['image_path'])): ?>
                <img src="<?= UPLOAD_URL . $c['image_path'] ?>" class="td-img">
                <?php endif; ?>
                <span><?= sanitize($c['item_name']) ?></span>
              </div>
            </td>
            <td><?= sanitize($c['claimant_name']) ?></td>
            <td><?= sanitize($c['building']) ?></td>
            <td><?= date('d M Y', strtotime($c['submitted_at'])) ?></td>
            <td><?= statusBadge($c['status']) ?></td>
            <td>
              <a href="?review=<?= $c['claim_id'] ?>" class="btn btn-outline btn-sm">
                <?= $c['status'] === 'pending' ? 'Review' : 'View' ?>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$claims): ?>
          <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-muted);">No claims yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>

</div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
