<?php
// ============================================================
//  includes/functions.php — Core Business Logic
// ============================================================

require_once __DIR__ . '/../config/db.php';

/* ── Image Upload ─────────────────────────────────────────── */

function uploadImage(array $file): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;

    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo   = finfo_open(FILEINFO_MIME_TYPE);
    $mime    = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed)) return null;
    if ($file['size'] > 5 * 1024 * 1024) return null;  // 5 MB max

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_', true) . '.' . strtolower($ext);
    $dest     = UPLOAD_DIR . $filename;

    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

    if (move_uploaded_file($file['tmp_name'], $dest)) return $filename;
    return null;
}

/* ── Categories & Locations ──────────────────────────────── */

function getCategories(): array {
    return getDB()->query("SELECT * FROM categories ORDER BY name")->fetchAll();
}

function getLocations(): array {
    return getDB()->query("SELECT * FROM locations ORDER BY building")->fetchAll();
}

/* ── Lost Items CRUD ──────────────────────────────────────── */

function reportLostItem(array $data, ?string $imageName): bool {
    $db = getDB();
    $s  = $db->prepare(
        "INSERT INTO lost_items
            (user_id, category_id, location_id, name, description, date_lost, image_path)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $ok = $s->execute([
        $data['user_id'], $data['category_id'], $data['location_id'],
        $data['name'],    $data['description'], $data['date_lost'],
        $imageName
    ]);
    if ($ok) runAutoMatch((int)$db->lastInsertId(), 'lost');
    return $ok;
}

function getLostItems(int $userId = 0, string $status = ''): array {
    $db  = getDB();
    $sql = "SELECT li.*, c.name AS category, l.building, l.room, u.full_name AS reporter
            FROM lost_items li
            JOIN categories c ON li.category_id = c.category_id
            JOIN locations  l ON li.location_id  = l.location_id
            JOIN users      u ON li.user_id       = u.user_id
            WHERE 1=1";
    $params = [];
    if ($userId) { $sql .= " AND li.user_id = ?";  $params[] = $userId; }
    if ($status) { $sql .= " AND li.status  = ?";  $params[] = $status; }
    $sql .= " ORDER BY li.created_at DESC";
    $s = $db->prepare($sql);
    $s->execute($params);
    return $s->fetchAll();
}

function getLostItemById(int $id): ?array {
    $db = getDB();
    $s  = $db->prepare(
        "SELECT li.*, c.name AS category, l.building, l.room, l.latitude, l.longitude, u.full_name AS reporter, u.email AS reporter_email
         FROM lost_items li
         JOIN categories c ON li.category_id = c.category_id
         JOIN locations  l ON li.location_id  = l.location_id
         JOIN users      u ON li.user_id       = u.user_id
         WHERE li.item_id = ?"
    );
    $s->execute([$id]);
    return $s->fetch() ?: null;
}

function deleteLostItem(int $id): bool {
    $db = getDB();
    $s  = $db->prepare("DELETE FROM lost_items WHERE item_id = ?");
    return $s->execute([$id]);
}

/* ── Found Items CRUD ─────────────────────────────────────── */

function reportFoundItem(array $data, ?string $imageName): bool {
    $db = getDB();
    $s  = $db->prepare(
        "INSERT INTO found_items
            (user_id, category_id, location_id, name, description, date_found, image_path)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $ok = $s->execute([
        $data['user_id'], $data['category_id'], $data['location_id'],
        $data['name'],    $data['description'], $data['date_found'],
        $imageName
    ]);
    if ($ok) runAutoMatch((int)$db->lastInsertId(), 'found');
    return $ok;
}

function getFoundItems(int $userId = 0, string $status = ''): array {
    $db  = getDB();
    $sql = "SELECT fi.*, c.name AS category, l.building, l.room, u.full_name AS reporter
            FROM found_items fi
            JOIN categories c ON fi.category_id = c.category_id
            JOIN locations  l ON fi.location_id  = l.location_id
            JOIN users      u ON fi.user_id       = u.user_id
            WHERE 1=1";
    $params = [];
    if ($userId) { $sql .= " AND fi.user_id = ?";  $params[] = $userId; }
    if ($status) { $sql .= " AND fi.status  = ?";  $params[] = $status; }
    $sql .= " ORDER BY fi.created_at DESC";
    $s = $db->prepare($sql);
    $s->execute($params);
    return $s->fetchAll();
}

function getFoundItemById(int $id): ?array {
    $db = getDB();
    $s  = $db->prepare(
        "SELECT fi.*, c.name AS category, l.building, l.room, l.latitude, l.longitude, u.full_name AS reporter, u.email AS reporter_email
         FROM found_items fi
         JOIN categories c ON fi.category_id = c.category_id
         JOIN locations  l ON fi.location_id  = l.location_id
         JOIN users      u ON fi.user_id       = u.user_id
         WHERE fi.item_id = ?"
    );
    $s->execute([$id]);
    return $s->fetch() ?: null;
}

function deleteFoundItem(int $id): bool {
    $db = getDB();
    $s  = $db->prepare("DELETE FROM found_items WHERE item_id = ?");
    return $s->execute([$id]);
}

/* ── Auto-Match Engine ────────────────────────────────────── */

function runAutoMatch(int $newItemId, string $type): void {
    $db = getDB();

    if ($type === 'lost') {
        $lost  = getLostItemById($newItemId);
        if (!$lost) return;
        $found = getFoundItems(0, 'open');

        foreach ($found as $f) {
            $score = computeMatchScore($lost, $f);
            if ($score >= 40) {
                $check = $db->prepare(
                    "SELECT match_id FROM matches WHERE lost_item_id=? AND found_item_id=?"
                );
                $check->execute([$newItemId, $f['item_id']]);
                if (!$check->fetch()) {
                    $ins = $db->prepare(
                        "INSERT INTO matches (lost_item_id, found_item_id, score) VALUES (?,?,?)"
                    );
                    $ins->execute([$newItemId, $f['item_id'], $score]);
                    if ($score >= 70) {
                        updateItemStatus('lost',  $newItemId,    'matched');
                        updateItemStatus('found', $f['item_id'], 'matched');
                        notifyUser($lost['user_id'],
                            "A potential match was found for your lost item: \"{$lost['name']}\".");
                        notifyUser($f['user_id'],
                            "Your found item \"{$f['name']}\" matches a lost report. Please check matches.");
                    }
                }
            }
        }
    } else {
        $foundItem = getFoundItemById($newItemId);
        if (!$foundItem) return;
        $lostList  = getLostItems(0, 'open');

        foreach ($lostList as $l) {
            $score = computeMatchScore($l, $foundItem);
            if ($score >= 40) {
                $check = $db->prepare(
                    "SELECT match_id FROM matches WHERE lost_item_id=? AND found_item_id=?"
                );
                $check->execute([$l['item_id'], $newItemId]);
                if (!$check->fetch()) {
                    $ins = $db->prepare(
                        "INSERT INTO matches (lost_item_id, found_item_id, score) VALUES (?,?,?)"
                    );
                    $ins->execute([$l['item_id'], $newItemId, $score]);
                    if ($score >= 70) {
                        updateItemStatus('lost',  $l['item_id'], 'matched');
                        updateItemStatus('found', $newItemId,    'matched');
                        notifyUser($l['user_id'],
                            "A potential match was found for your lost item: \"{$l['name']}\".");
                        notifyUser($foundItem['user_id'],
                            "Your found item \"{$foundItem['name']}\" matches a lost report.");
                    }
                }
            }
        }
    }
}

function computeMatchScore(array $lost, array $found): float {
    $score = 0;

    // Same category → 40 pts
    if ($lost['category_id'] === $found['category_id']) $score += 40;

    // Same location → 25 pts
    if ($lost['location_id'] === $found['location_id']) $score += 25;

    // Date proximity: within 7 days → 20 pts, within 14 → 10 pts
    $dateLost  = new DateTime($lost['date_lost']);
    $dateFound = new DateTime($found['date_found']);
    $diff      = abs($dateLost->diff($dateFound)->days);
    if ($diff <= 7)  $score += 20;
    elseif ($diff <= 14) $score += 10;

    // Keyword overlap in name/description → up to 15 pts
    $lWords = array_unique(str_word_count(strtolower($lost['name']  . ' ' . $lost['description']),  1));
    $fWords = array_unique(str_word_count(strtolower($found['name'] . ' ' . $found['description']), 1));
    $commonWords = array_intersect($lWords, $fWords);
    $stopWords   = ['a','an','the','is','was','in','at','on','of','to','and','or','with','for','it'];
    $meaningful  = array_diff($commonWords, $stopWords);
    $score      += min(15, count($meaningful) * 3);

    return min(100, $score);
}

function updateItemStatus(string $table, int $id, string $status): void {
    $t  = ($table === 'lost') ? 'lost_items' : 'found_items';
    $pk = ($table === 'lost') ? 'item_id'    : 'item_id';
    $db = getDB();
    $s  = $db->prepare("UPDATE {$t} SET status = ? WHERE {$pk} = ?");
    $s->execute([$status, $id]);
}

/* ── Matches ──────────────────────────────────────────────── */

function getMatches(int $userId = 0): array {
    $db  = getDB();
    $sql = "SELECT m.*,
                   li.name AS lost_name,  li.image_path AS lost_image,  li.date_lost,  li.user_id AS lost_user_id,
                   fi.name AS found_name, fi.image_path AS found_image, fi.date_found, fi.user_id AS found_user_id,
                   cl.building AS lost_loc,  cf.building AS found_loc,
                   cat.name AS category
            FROM matches m
            JOIN lost_items  li  ON m.lost_item_id  = li.item_id
            JOIN found_items fi  ON m.found_item_id = fi.item_id
            JOIN locations   cl  ON li.location_id  = cl.location_id
            JOIN locations   cf  ON fi.location_id  = cf.location_id
            JOIN categories  cat ON li.category_id  = cat.category_id
            WHERE 1=1";
    $params = [];
    if ($userId) {
        $sql .= " AND (li.user_id = ? OR fi.user_id = ?)";
        $params = [$userId, $userId];
    }
    $sql .= " ORDER BY m.matched_at DESC";
    $s = $db->prepare($sql);
    $s->execute($params);
    return $s->fetchAll();
}

function verifyMatch(int $matchId, string $status, int $adminId): bool {
    $db = getDB();
    $s  = $db->prepare("UPDATE matches SET status = ?, verified_by = ? WHERE match_id = ?");
    return $s->execute([$status, $adminId, $matchId]);
}

/* ── Claims ───────────────────────────────────────────────── */

function submitClaim(int $foundItemId, int $userId, string $proof, ?string $proofImage): bool {
    $db = getDB();
    // prevent duplicate claims
    $chk = $db->prepare("SELECT claim_id FROM claims WHERE found_item_id=? AND claimant_id=?");
    $chk->execute([$foundItemId, $userId]);
    if ($chk->fetch()) return false;

    $s = $db->prepare(
        "INSERT INTO claims (found_item_id, claimant_id, proof_desc, proof_image) VALUES (?,?,?,?)"
    );
    return $s->execute([$foundItemId, $userId, $proof, $proofImage]);
}

function getClaims(int $userId = 0): array {
    $db  = getDB();
    $sql = "SELECT cl.*, fi.name AS item_name, fi.image_path, u.full_name AS claimant_name, u.email AS claimant_email,
                   loc.building
            FROM claims cl
            JOIN found_items fi ON cl.found_item_id = fi.item_id
            JOIN users       u  ON cl.claimant_id   = u.user_id
            JOIN locations   loc ON fi.location_id  = loc.location_id
            WHERE 1=1";
    $params = [];
    if ($userId) { $sql .= " AND cl.claimant_id = ?"; $params[] = $userId; }
    $sql .= " ORDER BY cl.submitted_at DESC";
    $s = $db->prepare($sql);
    $s->execute($params);
    return $s->fetchAll();
}

function reviewClaim(int $claimId, string $status, string $note, int $adminId): bool {
    $db = getDB();
    $s  = $db->prepare(
        "UPDATE claims SET status=?, admin_note=?, reviewed_by=?, reviewed_at=NOW() WHERE claim_id=?"
    );
    $ok = $s->execute([$status, $note, $adminId, $claimId]);
    if ($ok && $status === 'approved') {
        // mark found item as claimed
        $c = $db->prepare("SELECT found_item_id, claimant_id FROM claims WHERE claim_id=?");
        $c->execute([$claimId]);
        $row = $c->fetch();
        if ($row) {
            updateItemStatus('found', $row['found_item_id'], 'claimed');
            notifyUser($row['claimant_id'], 'Your claim request has been approved! Please collect your item from the Admin Office.');
        }
    }
    return $ok;
}

/* ── Search ───────────────────────────────────────────────── */

function searchItems(string $keyword, string $type = 'all', int $categoryId = 0, int $locationId = 0): array {
    $db     = getDB();
    $kw     = "%{$keyword}%";
    $results = [];

    if ($type !== 'found') {
        $sql = "SELECT li.item_id, 'lost' AS type, li.name, li.description, li.date_lost AS date_event,
                       li.image_path, li.status, c.name AS category, l.building, l.room
                FROM lost_items li
                JOIN categories c ON li.category_id = c.category_id
                JOIN locations  l ON li.location_id  = l.location_id
                WHERE (li.name LIKE ? OR li.description LIKE ? OR l.building LIKE ?)";
        $params = [$kw, $kw, $kw];
        if ($categoryId) { $sql .= " AND li.category_id = ?"; $params[] = $categoryId; }
        if ($locationId) { $sql .= " AND li.location_id  = ?"; $params[] = $locationId; }
        $s = $db->prepare($sql); $s->execute($params);
        $results = array_merge($results, $s->fetchAll());
    }

    if ($type !== 'lost') {
        $sql = "SELECT fi.item_id, 'found' AS type, fi.name, fi.description, fi.date_found AS date_event,
                       fi.image_path, fi.status, c.name AS category, l.building, l.room
                FROM found_items fi
                JOIN categories c ON fi.category_id = c.category_id
                JOIN locations  l ON fi.location_id  = l.location_id
                WHERE (fi.name LIKE ? OR fi.description LIKE ? OR l.building LIKE ?)";
        $params = [$kw, $kw, $kw];
        if ($categoryId) { $sql .= " AND fi.category_id = ?"; $params[] = $categoryId; }
        if ($locationId) { $sql .= " AND fi.location_id  = ?"; $params[] = $locationId; }
        $s = $db->prepare($sql); $s->execute($params);
        $results = array_merge($results, $s->fetchAll());
    }

    return $results;
}

/* ── Notifications ───────────────────────────────────────── */

function notifyUser(int $userId, string $message): void {
    $db = getDB();
    $s  = $db->prepare("INSERT INTO notifications (user_id, message) VALUES (?,?)");
    $s->execute([$userId, $message]);
}

function getUserNotifications(int $userId): array {
    $db = getDB();
    $s  = $db->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 20");
    $s->execute([$userId]);
    return $s->fetchAll();
}

function markNotificationsRead(int $userId): void {
    $db = getDB();
    $s  = $db->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?");
    $s->execute([$userId]);
}

/* ── Admin Stats ─────────────────────────────────────────── */

function getAdminStats(): array {
    $db = getDB();
    return [
        'total_users'   => $db->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn(),
        'total_lost'    => $db->query("SELECT COUNT(*) FROM lost_items")->fetchColumn(),
        'total_found'   => $db->query("SELECT COUNT(*) FROM found_items")->fetchColumn(),
        'total_matches' => $db->query("SELECT COUNT(*) FROM matches")->fetchColumn(),
        'pending_claims'=> $db->query("SELECT COUNT(*) FROM claims WHERE status='pending'")->fetchColumn(),
        'resolved'      => $db->query("SELECT COUNT(*) FROM claims WHERE status='approved'")->fetchColumn(),
    ];
}

/* ── Utility ─────────────────────────────────────────────── */

function sanitize(string $s): string {
    return htmlspecialchars(trim($s), ENT_QUOTES, 'UTF-8');
}

function itemImageTag(string $path = '', string $alt = 'Item', string $class = ''): string {
    $src = ($path && file_exists(UPLOAD_DIR . $path))
        ? UPLOAD_URL . $path
        : SITE_URL . '/assets/images/no-image.png';
    return "<img src=\"{$src}\" alt=\"" . sanitize($alt) . "\" class=\"{$class}\">";
}

function statusBadge(string $status): string {
    $map = [
        'open'    => 'badge-open',
        'matched' => 'badge-matched',
        'claimed' => 'badge-claimed',
        'closed'  => 'badge-closed',
        'pending' => 'badge-pending',
        'approved'=> 'badge-approved',
        'rejected'=> 'badge-rejected',
        'verified'=> 'badge-verified',
    ];
    $cls = $map[$status] ?? 'badge-open';
    return "<span class=\"badge {$cls}\">" . ucfirst($status) . "</span>";
}
