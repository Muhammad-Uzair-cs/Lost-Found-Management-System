<?php
// claim.php
define('PAGE_TITLE', 'Submit Claim');
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$itemId = (int)($_GET['id'] ?? 0);
$item   = getFoundItemById($itemId);
if (!$item) { header('Location: ' . SITE_URL . '/found-items.php'); exit; }

// Can't claim own report
if ($item['user_id'] == currentUserId()) {
    header('Location: ' . SITE_URL . '/item-detail.php?type=found&id=' . $itemId);
    exit;
}

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $proof = trim($_POST['proof_desc'] ?? '');
    if (strlen($proof) < 20) {
        $error = 'Please provide a detailed proof of ownership (at least 20 characters).';
    } else {
        $proofImage = null;
        if (!empty($_FILES['proof_image']['name'])) {
            $proofImage = uploadImage($_FILES['proof_image']);
            if ($proofImage === null) $error = 'Invalid image for proof. Use JPG/PNG under 5MB.';
        }
        if (!$error) {
            if (submitClaim($itemId, currentUserId(), $proof, $proofImage)) {
                $success = 'Claim submitted successfully! Admin will review your request.';
            } else {
                $error = 'You have already submitted a claim for this item, or submission failed.';
            }
        }
    }
}
?>
<?php require __DIR__ . '/includes/header.php'; ?>

<div class="page-header" style="background:linear-gradient(135deg,#1e40af 0%,#2563eb 100%);">
  <div class="container">
    <h1><i class="fa fa-hand"></i> Submit Claim Request</h1>
    <p>Provide proof of ownership to claim your item back.</p>
  </div>
</div>

<div class="page-body">
<div class="container">
<div class="two-col">

  <!-- Form -->
  <div>
    <?php if ($error):   ?><div class="alert alert-error"><i class="fa fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><i class="fa fa-circle-check"></i> <?= htmlspecialchars($success) ?> <a href="<?= SITE_URL ?>/dashboard.php">Go to Dashboard</a></div><?php endif; ?>

    <?php if (!$success): ?>
    <div class="card">
      <div class="card-header"><h2>Ownership Proof</h2></div>
      <div class="card-body">
        <form method="POST" enctype="multipart/form-data">

          <div class="alert alert-info" style="margin-bottom:20px;">
            <i class="fa fa-circle-info"></i>
            Your claim will be reviewed by an admin. Please be as specific as possible about how this item belongs to you.
          </div>

          <div class="form-group">
            <label class="form-label">Describe Your Proof of Ownership <span style="color:var(--danger)">*</span></label>
            <textarea name="proof_desc" class="form-control" rows="6" required
              placeholder="Example: This is my black Dell Inspiron 15 laptop. The serial number is DELLXXXX. I have a sticker of Pikachu on the lid and my name 'Ahmed' is written inside the battery compartment. I lost it on Nov 1st in Room 201 of Engineering Block."><?= htmlspecialchars($_POST['proof_desc'] ?? '') ?></textarea>
            <div class="form-hint">Include: serial numbers, unique markings, what's inside, purchase details, etc.</div>
          </div>

          <div class="form-group">
            <label class="form-label">Supporting Image (optional)</label>
            <div class="upload-area" id="uploadArea">
              <div class="upload-icon"><i class="fa fa-image"></i></div>
              <p>Upload a receipt, previous photo, or ownership proof</p>
              <input type="file" name="proof_image" id="itemImage" accept="image/*" style="display:none">
            </div>
            <img id="imagePreview" class="hidden" alt="Preview" style="max-width:200px;border-radius:8px;margin-top:12px;">
          </div>

          <div style="display:flex;gap:12px;">
            <button type="submit" class="btn btn-primary btn-lg"><i class="fa fa-paper-plane"></i> Submit Claim</button>
            <a href="<?= SITE_URL ?>/item-detail.php?type=found&id=<?= $itemId ?>" class="btn btn-outline btn-lg">Cancel</a>
          </div>
        </form>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Sidebar: Item Preview -->
  <div>
    <div class="card sidebar-card">
      <div class="card-header"><h2>Item You're Claiming</h2></div>
      <div class="card-body">
        <?php if ($item['image_path'] && file_exists(UPLOAD_DIR . $item['image_path'])): ?>
          <img src="<?= UPLOAD_URL . $item['image_path'] ?>" alt="<?= sanitize($item['name']) ?>"
               style="width:100%;height:180px;object-fit:cover;border-radius:8px;margin-bottom:14px;">
        <?php else: ?>
          <div style="width:100%;height:120px;background:var(--surface);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:2.5rem;color:#aab4cc;margin-bottom:14px;">
            <i class="fa fa-box-open"></i>
          </div>
        <?php endif; ?>
        <div style="display:flex;flex-direction:column;gap:8px;font-size:.88rem;">
          <div><strong><?= sanitize($item['name']) ?></strong></div>
          <div style="color:var(--text-muted)"><i class="fa fa-tag"></i> <?= sanitize($item['category']) ?></div>
          <div style="color:var(--text-muted)"><i class="fa fa-location-dot"></i> <?= sanitize($item['building']) ?></div>
          <div style="color:var(--text-muted)"><i class="fa fa-calendar"></i> Found: <?= date('d M Y', strtotime($item['date_found'])) ?></div>
          <?php if ($item['description']): ?>
          <div style="margin-top:6px;color:var(--text-muted);font-size:.82rem;"><?= sanitize(substr($item['description'], 0, 150)) ?>...</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="card mt-3">
      <div class="card-header"><h2>Claim Process</h2></div>
      <div class="card-body" style="font-size:.85rem;color:var(--text-muted);">
        <div style="display:flex;flex-direction:column;gap:10px;">
          <div>① Submit claim with proof details</div>
          <div>② Admin reviews your submission</div>
          <div>③ If approved, collect item from Admin Office</div>
          <div>④ If rejected, you'll be notified with a reason</div>
        </div>
        <div class="alert alert-warning" style="margin-top:14px;font-size:.8rem;">
          <i class="fa fa-triangle-exclamation"></i>
          False claims may result in account suspension.
        </div>
      </div>
    </div>
  </div>

</div>
</div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
