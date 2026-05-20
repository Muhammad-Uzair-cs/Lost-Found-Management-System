<?php
// ============================================================
//  config/db.php — Database Connection (PDO)
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'lost_found_db');
define('DB_USER', 'root');
define('DB_PASS', '');          // change to your MySQL password
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME',  'Lost & Found — University');
define('SITE_URL',   'http://localhost/lost-found');
define('UPLOAD_DIR', __DIR__ . '/../uploads/items/');
define('UPLOAD_URL', SITE_URL . '/uploads/items/');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}
