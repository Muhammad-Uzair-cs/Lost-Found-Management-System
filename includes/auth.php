<?php
// ============================================================
//  includes/auth.php — Session & Auth Helpers
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

/* ── Guards ───────────────────────────────────────────────── */

function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: ' . SITE_URL . '/dashboard.php');
        exit;
    }
}

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

/* ── Current user helpers ─────────────────────────────────── */

function currentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    static $user = null;
    if ($user === null) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
    }
    return $user;
}

/* ── Registration ─────────────────────────────────────────── */

function registerUser(string $name, string $email, string $password, string $phone): array {
    $db = getDB();
    // Check duplicate email
    $s = $db->prepare("SELECT user_id FROM users WHERE email = ?");
    $s->execute([$email]);
    if ($s->fetch()) {
        return ['success' => false, 'message' => 'Email already registered.'];
    }
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $s = $db->prepare(
        "INSERT INTO users (full_name, email, password, phone) VALUES (?, ?, ?, ?)"
    );
    $s->execute([$name, $email, $hash, $phone]);
    return ['success' => true, 'message' => 'Registration successful! Please log in.'];
}

/* ── Login ───────────────────────────────────────────────── */

function loginUser(string $email, string $password): array {
    $db = getDB();
    $s = $db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
    $s->execute([$email]);
    $user = $s->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    $_SESSION['user_id']   = $user['user_id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email']     = $user['email'];
    $_SESSION['role']      = $user['role'];
    $_SESSION['avatar']    = $user['avatar'];

    return ['success' => true, 'role' => $user['role']];
}

/* ── Logout ──────────────────────────────────────────────── */

function logoutUser(): void {
    session_destroy();
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

/* ── Notifications ───────────────────────────────────────── */

function getUnreadCount(): int {
    if (!isLoggedIn()) return 0;
    $db = getDB();
    $s = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $s->execute([currentUserId()]);
    return (int) $s->fetchColumn();
}
