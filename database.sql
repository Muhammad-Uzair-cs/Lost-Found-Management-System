-- ============================================================
--  Lost & Found Management System — University of Knowledge
--  Database Schema | MySQL 5.7+
--  Normalized to 3NF
-- ============================================================

CREATE DATABASE IF NOT EXISTS lost_found_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE lost_found_db;

-- ─────────────────────────────────────────
-- TABLE: users
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    user_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name    VARCHAR(100) NOT NULL,
    email        VARCHAR(150) NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,          -- bcrypt hash
    phone        VARCHAR(20),
    role         ENUM('admin','user') NOT NULL DEFAULT 'user',
    avatar       VARCHAR(255)         DEFAULT 'default.png',
    is_active    TINYINT(1)           DEFAULT 1,
    created_at   DATETIME             DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME             DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
-- TABLE: locations
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS locations (
    location_id  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    building     VARCHAR(100) NOT NULL,
    room         VARCHAR(50),
    description  VARCHAR(255),
    latitude     DECIMAL(10,8),
    longitude    DECIMAL(11,8),
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
-- TABLE: categories
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categories (
    category_id  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(80) NOT NULL UNIQUE,
    icon         VARCHAR(50) DEFAULT 'tag'
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
-- TABLE: lost_items
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS lost_items (
    item_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        INT UNSIGNED NOT NULL,
    category_id    INT UNSIGNED NOT NULL,
    location_id    INT UNSIGNED NOT NULL,
    name           VARCHAR(150) NOT NULL,
    description    TEXT,
    date_lost      DATE NOT NULL,
    image_path     VARCHAR(255) DEFAULT NULL,
    status         ENUM('open','matched','claimed','closed') DEFAULT 'open',
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_lost_user     FOREIGN KEY (user_id)     REFERENCES users(user_id)     ON DELETE CASCADE,
    CONSTRAINT fk_lost_cat      FOREIGN KEY (category_id) REFERENCES categories(category_id),
    CONSTRAINT fk_lost_loc      FOREIGN KEY (location_id) REFERENCES locations(location_id)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
-- TABLE: found_items
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS found_items (
    item_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        INT UNSIGNED NOT NULL,
    category_id    INT UNSIGNED NOT NULL,
    location_id    INT UNSIGNED NOT NULL,
    name           VARCHAR(150) NOT NULL,
    description    TEXT,
    date_found     DATE NOT NULL,
    image_path     VARCHAR(255) DEFAULT NULL,
    status         ENUM('open','matched','claimed','closed') DEFAULT 'open',
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_found_user    FOREIGN KEY (user_id)     REFERENCES users(user_id)     ON DELETE CASCADE,
    CONSTRAINT fk_found_cat     FOREIGN KEY (category_id) REFERENCES categories(category_id),
    CONSTRAINT fk_found_loc     FOREIGN KEY (location_id) REFERENCES locations(location_id)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
-- TABLE: matches
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS matches (
    match_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lost_item_id    INT UNSIGNED NOT NULL,
    found_item_id   INT UNSIGNED NOT NULL,
    score           DECIMAL(5,2) DEFAULT 0.00,   -- match confidence 0-100
    status          ENUM('pending','verified','rejected') DEFAULT 'pending',
    matched_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    verified_by     INT UNSIGNED DEFAULT NULL,
    CONSTRAINT fk_match_lost    FOREIGN KEY (lost_item_id)  REFERENCES lost_items(item_id)  ON DELETE CASCADE,
    CONSTRAINT fk_match_found   FOREIGN KEY (found_item_id) REFERENCES found_items(item_id) ON DELETE CASCADE,
    CONSTRAINT fk_match_admin   FOREIGN KEY (verified_by)   REFERENCES users(user_id)       ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
-- TABLE: claims
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS claims (
    claim_id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    found_item_id   INT UNSIGNED NOT NULL,
    claimant_id     INT UNSIGNED NOT NULL,
    proof_desc      TEXT NOT NULL,
    proof_image     VARCHAR(255) DEFAULT NULL,
    status          ENUM('pending','approved','rejected') DEFAULT 'pending',
    admin_note      TEXT DEFAULT NULL,
    reviewed_by     INT UNSIGNED DEFAULT NULL,
    submitted_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    reviewed_at     DATETIME DEFAULT NULL,
    CONSTRAINT fk_claim_item    FOREIGN KEY (found_item_id) REFERENCES found_items(item_id) ON DELETE CASCADE,
    CONSTRAINT fk_claim_user    FOREIGN KEY (claimant_id)   REFERENCES users(user_id)       ON DELETE CASCADE,
    CONSTRAINT fk_claim_admin   FOREIGN KEY (reviewed_by)   REFERENCES users(user_id)       ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
-- TABLE: notifications
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS notifications (
    notif_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    message     TEXT NOT NULL,
    is_read     TINYINT(1) DEFAULT 0,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────
-- INDEXES
-- ─────────────────────────────────────────
CREATE INDEX idx_lost_status   ON lost_items(status);
CREATE INDEX idx_found_status  ON found_items(status);
CREATE INDEX idx_lost_cat      ON lost_items(category_id);
CREATE INDEX idx_found_cat     ON found_items(category_id);
CREATE INDEX idx_lost_date     ON lost_items(date_lost);
CREATE INDEX idx_found_date    ON found_items(date_found);

-- ─────────────────────────────────────────
-- SEED DATA
-- ─────────────────────────────────────────

-- Admin user  (password = Admin@1234)
INSERT INTO users (full_name, email, password, phone, role) VALUES
('System Admin',       'admin@university.edu',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '03001234567', 'admin');

-- Normal users  (password = Pass@1234)
INSERT INTO users (full_name, email, password, phone, role) VALUES
('Ahmed Khan',         'ahmed@student.edu',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '03111234567', 'user'),
('Sara Malik',         'sara@student.edu',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '03221234567', 'user'),
('Usman Ali',          'usman@student.edu',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '03331234567', 'user');

-- Categories
INSERT INTO categories (name, icon) VALUES
('Electronics',   'laptop'),
('Books',         'book'),
('Clothing',      'shirt'),
('Accessories',   'watch'),
('Keys',          'key'),
('Bags',          'shopping-bag'),
('Documents',     'file-text'),
('Sports',        'activity'),
('Other',         'box');

-- Locations
INSERT INTO locations (building, room, description, latitude, longitude) VALUES
('Main Library',         'Ground Floor',  'Near entrance',                    33.9980, 71.4844),
('Engineering Block',    'Room 201',      'Second floor corridor',             33.9985, 71.4849),
('Student Center',       'Cafeteria',     'Near food court',                   33.9975, 71.4840),
('Sports Complex',       'Locker Room',   'Main sports hall',                  33.9970, 71.4835),
('Admin Building',       'Reception',     'Ground floor reception desk',       33.9990, 71.4855),
('Science Block',        'Lab 301',       'Third floor biology lab',           33.9978, 71.4848),
('Hostel Block A',       'Common Room',   'Ground floor common area',          33.9965, 71.4830),
('Parking Lot',          'Sector B',      'Main parking area near gate',       33.9960, 71.4825);

-- Lost Items
INSERT INTO lost_items (user_id, category_id, location_id, name, description, date_lost, status) VALUES
(2, 1, 2, 'Black Laptop',          'Dell Inspiron 15, black color, has a sticker on the lid',     '2024-11-01', 'open'),
(3, 5, 1, 'University ID + Keys',  'Blue lanyard with two keys and university ID card',            '2024-11-03', 'matched'),
(4, 4, 3, 'Silver Watch',          'Casio analog watch, silver bracelet, small scratch on glass', '2024-11-05', 'open'),
(2, 2, 1, 'Calculus Textbook',     'Stewart Calculus 8th edition, name written inside cover',     '2024-11-07', 'open'),
(3, 3, 4, 'Blue Hoodie',           'University branded hoodie, size M, blue color',               '2024-11-10', 'open');

-- Found Items
INSERT INTO found_items (user_id, category_id, location_id, name, description, date_found, status) VALUES
(4, 5, 1, 'Keys on Lanyard',       'Found blue lanyard with keys and an ID card near library',    '2024-11-04', 'matched'),
(2, 4, 3, 'Watch',                 'Silver analog watch found near cafeteria seating',            '2024-11-06', 'open'),
(3, 1, 2, 'Laptop Bag with Laptop','Black bag with Dell laptop inside, found in corridor',        '2024-11-02', 'open'),
(4, 3, 4, 'Blue Hoodie University','Blue hoodie with university logo, found in locker room',      '2024-11-11', 'open');

-- Matches
INSERT INTO matches (lost_item_id, found_item_id, score, status) VALUES
(2, 1, 92.50, 'verified');

-- Claims
INSERT INTO claims (found_item_id, claimant_id, proof_desc, status) VALUES
(1, 3, 'The ID card on the lanyard belongs to me (Sara Malik, Roll# CS-2021-045). The keys are for my room 214 and my bike lock.', 'approved');

-- Notifications
INSERT INTO notifications (user_id, message) VALUES
(3, 'Great news! A match was found for your lost item: University ID + Keys.'),
(3, 'Your claim request for "Keys on Lanyard" has been approved. Please collect from Admin Office.');
