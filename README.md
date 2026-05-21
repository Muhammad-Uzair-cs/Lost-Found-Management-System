# Lost & Found Management System
### University Edition — Full-Stack PHP/MySQL Project

## Live Demo

https://warrant-joseph-tales-measuring.trycloudflare.com/lost-found




## 📋 Project Overview

A complete, professional Lost & Found Management System designed for universities. This system allows students and staff to report lost/found items, get automatically matched, submit claim requests, and have administrators verify and resolve cases.

**Tech Stack:**
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla), Leaflet.js (maps), Font Awesome icons
- **Backend:** PHP 7.4+ (PDO prepared statements, MVC-style structure)
- **Database:** MySQL 5.7+ / MariaDB 10.3+ (normalized to 3NF)
- **Maps:** OpenStreetMap via Leaflet.js (free, no API key needed)

---

## 🗄️ Database ER Diagram (Text)

```
┌──────────────┐        ┌──────────────────┐        ┌───────────────┐
│    users     │        │   lost_items     │        │  categories   │
│──────────────│        │──────────────────│        │───────────────│
│ user_id (PK) │──┐     │ item_id (PK)     │        │ category_id   │
│ full_name    │  │     │ user_id (FK)─────│────────│ name          │
│ email        │  └─────│ category_id (FK) │  ┌──── │ icon          │
│ password     │        │ location_id (FK) │  │     └───────────────┘
│ phone        │        │ name             │  │
│ role         │        │ description      │  │     ┌───────────────┐
│ is_active    │        │ date_lost        │  │     │   locations   │
│ created_at   │        │ image_path       │  │     │───────────────│
└──────────────┘        │ status           │  │     │ location_id   │
       │                └──────────────────┘  │     │ building      │
       │                        │             └─────│ room          │
       │                        │                   │ description   │
       │                        ▼                   │ latitude      │
       │                ┌──────────────┐            │ longitude     │
       │                │   matches    │            └───────────────┘
       │                │──────────────│                   │
       │                │ match_id(PK) │                   │
       │                │ lost_item_id │────────────────────┘
       │                │ found_item_id│
       │                │ score        │       ┌──────────────────┐
       │                │ status       │       │   found_items    │
       │                │ verified_by  │       │──────────────────│
       │                └──────────────┘       │ item_id (PK)     │
       │                        │              │ user_id (FK)     │
       │                        └──────────────│ category_id (FK) │
       │                                       │ location_id (FK) │
       │                ┌──────────────┐       │ name             │
       └────────────────│    claims    │       │ description      │
                        │──────────────│       │ date_found       │
                        │ claim_id(PK) │       │ image_path       │
                        │ found_item_id│───────│ status           │
                        │ claimant_id  │       └──────────────────┘
                        │ proof_desc   │
                        │ proof_image  │       ┌──────────────────┐
                        │ status       │       │  notifications   │
                        │ admin_note   │       │──────────────────│
                        │ reviewed_by  │       │ notif_id (PK)    │
                        └──────────────┘       │ user_id (FK)     │
                                               │ message          │
                                               │ is_read          │
                                               └──────────────────┘
```

---

## 🚀 How to Run Locally

### Prerequisites
- XAMPP / WAMP / MAMP / Laragon (includes PHP + MySQL + Apache)
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Step 1: Copy Project Files
```bash
# Place the entire 'lost-found' folder inside your server root:
# XAMPP:   C:\xampp\htdocs\lost-found\
# WAMP:    C:\wamp64\www\lost-found\
# Linux:   /var/www/html/lost-found/
# macOS:   /Applications/XAMPP/htdocs/lost-found/
```

### Step 2: Create the Database
1. Open your browser → go to `http://localhost/phpmyadmin`
2. Click **"New"** to create a new database
3. Name it: `lost_found_db`, Collation: `utf8mb4_unicode_ci`
4. Click **Create**
5. Select `lost_found_db` → click **Import** tab
6. Choose the `database.sql` file from the project root
7. Click **Go** to import

### Step 3: Configure Database Connection
Open `config/db.php` and update:
```php
define('DB_HOST', 'localhost');   // usually 'localhost'
define('DB_NAME', 'lost_found_db');
define('DB_USER', 'root');         // your MySQL username
define('DB_PASS', '');             // your MySQL password (blank for XAMPP default)
define('SITE_URL', 'http://localhost/lost-found');  // change if different
```

### Step 4: Set Upload Permissions
```bash
# Linux/Mac: make uploads writable
chmod 755 uploads/items/

# Windows XAMPP: usually no action needed
```

### Step 5: Access the Application
Open your browser and go to:
```
http://localhost/lost-found/
```

---

## 🔐 Default Credentials

| Role  | Email                     | Password |
|-------|---------------------------|----------|
| Admin | admin@university.edu      | password |
| User  | ahmed@student.edu         | password |
| User  | sara@student.edu          | password |
| User  | usman@student.edu         | password |

> ⚠️ **Change these passwords** before deploying to production!

---

## 📁 Folder Structure

```
lost-found/
├── index.php                   # Entry point (redirects to login/dashboard)
├── login.php                   # Login page
├── register.php                # Registration page
├── dashboard.php               # User dashboard
├── lost-items.php              # Browse lost items
├── found-items.php             # Browse found items
├── report-lost.php             # Report a lost item
├── report-found.php            # Report a found item
├── item-detail.php             # Item details + map
├── matches.php                 # View matches
├── search.php                  # Smart search
├── claim.php                   # Submit claim request
├── notifications.php           # Notifications
├── profile.php                 # User profile
├── logout.php                  # Logout
├── delete-item.php             # Delete handler
├── database.sql                # Full database schema + seed data
│
├── config/
│   └── db.php                  # Database config + connection
│
├── includes/
│   ├── auth.php                # Auth helpers (login, register, guards)
│   ├── functions.php           # Core business logic (CRUD, matching, search)
│   ├── header.php              # Shared HTML header + navbar
│   └── footer.php             # Shared HTML footer
│
├── admin/
│   ├── index.php               # Admin dashboard
│   ├── manage-claims.php       # Review/approve/reject claims
│   ├── manage-matches.php      # Verify/reject matches
│   ├── manage-items.php        # View/delete all items
│   ├── manage-users.php        # Manage user accounts
│   └── verify-match.php        # Match verification handler
│
├── assets/
│   ├── css/
│   │   └── main.css            # Complete UI stylesheet
│   ├── js/
│   │   └── main.js             # JavaScript (upload preview, maps, etc.)
│   └── images/
│       └── no-image.png        # Placeholder image
│
└── uploads/
    └── items/                  # Uploaded item images (auto-created)
```

---

## ⚙️ Core Features Explained

### 🤖 Auto-Matching Algorithm
Located in `includes/functions.php → computeMatchScore()`

When a new lost/found item is reported, the system automatically:
1. Scans all open items of the opposite type
2. Computes a match score (0–100) using:
   - **Category match** → 40 points
   - **Same location** → 25 points
   - **Date proximity** (within 7 days → 20pts, within 14 → 10pts)
   - **Keyword overlap** in name/description → up to 15 points
3. Matches scoring ≥ 70 are marked high-confidence and users are notified
4. All matches ≥ 40 are stored for admin review

### 🔍 Smart Search
Located in `search.php` + `includes/functions.php → searchItems()`
- Searches both lost and found items simultaneously
- Searches in: item name, description, location building name
- Filterable by: type (lost/found), category, location

### 📍 Map Integration
- Uses **Leaflet.js** with **OpenStreetMap** (no API key needed)
- Item detail pages show item location on an interactive map
- Location coordinates stored in the `locations` table

### 🔒 Security Features
- Passwords hashed with **bcrypt** (PHP `password_hash`)
- All DB queries use **PDO prepared statements** (SQL injection prevention)
- Input sanitized with `htmlspecialchars()` (XSS prevention)
- Session-based authentication with role guards
- File upload validation (MIME type check, size limit, extension filter)

---

## 🧪 Sample Data Included

The `database.sql` includes:
- 4 users (1 admin + 3 students)
- 9 item categories
- 8 university locations with GPS coordinates
- 5 lost item reports
- 4 found item reports
- 1 verified match
- 1 approved claim
- 2 notifications

---

## 📊 DBMS Concepts Demonstrated

| Concept              | Where Used                                      |
|----------------------|-------------------------------------------------|
| 3NF Normalization    | Separate tables for users, locations, categories|
| Foreign Keys         | All item tables reference users, locations, cats|
| Indexes              | On status, category_id, date columns            |
| Transactions         | Claim approval updates multiple tables          |
| Aggregate Functions  | Admin stats (COUNT, GROUP BY)                   |
| JOINs                | All list queries use multi-table JOINs          |
| Prepared Statements  | All queries use PDO `prepare()` + `execute()`   |
| ENUM Types           | Status fields (open/matched/claimed/closed)     |
| ON DELETE CASCADE    | Items deleted when user is deleted              |

---

## 💡 Software Engineering Concepts

| Concept            | Implementation                              |
|--------------------|---------------------------------------------|
| MVC-like Pattern   | config/ (Model), includes/ (Controller), *.php (View) |
| DRY Principle      | Shared header/footer, reusable functions    |
| CRUD Operations    | Full Create/Read/Update/Delete for all entities |
| Authentication     | Session-based with role guards              |
| Input Validation   | Server-side validation on all forms         |
| Modular Design     | Separate files for auth, functions, config  |
| Error Handling     | Try-catch on DB, user-friendly error messages |

---

## 🌐 Browser Compatibility
- Chrome 90+ ✅
- Firefox 88+ ✅
- Edge 90+ ✅
- Safari 14+ ✅

---

*Built for University DBMS + Software Engineering Project Submission*
