<?php
// ============================================
// DATABASE CONNECTION
// PDO with MySQLi-Compatible Wrapper
// ============================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'cookery_store');

// ============================================
// PDO CONNECTION
// ============================================
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// ============================================
// WRAPPER CLASSES (mysqli jaisa interface)
// ============================================

class PDOStatementWrapper {
    private $stmt;
    private $pdo;

    public function __construct($pdo, $sql) {
        $this->pdo = $pdo;
        $this->stmt = $pdo->prepare($sql);
    }

    public function bind_param($types, ...$params) {
        $i = 1;
        foreach ($params as $param) {
            $type = PDO::PARAM_STR;
            if (is_int($param)) $type = PDO::PARAM_INT;
            elseif (is_bool($param)) $type = PDO::PARAM_BOOL;
            elseif (is_null($param)) $type = PDO::PARAM_NULL;
            $this->stmt->bindValue($i++, $param, $type);
        }
        return $this;
    }

    public function execute() {
        return $this->stmt->execute();
    }

    public function get_result() {
        $this->stmt->execute();
        return new PDOResultWrapper($this->stmt);
    }

    public function fetch() {
        $this->stmt->execute();
        return $this->stmt->fetch();
    }

    public function fetch_assoc() {
        return $this->fetch();
    }

    public function fetchAll() {
        $this->stmt->execute();
        return $this->stmt->fetchAll();
    }

    public function close() {
        $this->stmt->closeCursor();
    }

    public function __get($name) {
        if ($name === 'insert_id') return $this->pdo->lastInsertId();
        if ($name === 'affected_rows') return $this->stmt->rowCount();
        return null;
    }
}

class PDOResultWrapper {
    private $stmt;

    public function __construct($stmt) {
        $this->stmt = $stmt;
    }

    public function fetch_assoc() {
        return $this->stmt->fetch();
    }

    public function fetchAll() {
        return $this->stmt->fetchAll();
    }

    public function fetch() {
        return $this->stmt->fetch();
    }

    public function __get($name) {
        if ($name === 'num_rows') {
            $rows = $this->stmt->fetchAll();
            $this->stmt->execute(); // Reset cursor
            return count($rows);
        }
        return null;
    }
}

class PDOConnWrapper {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function prepare($sql) {
        return new PDOStatementWrapper($this->pdo, $sql);
    }

    public function query($sql) {
        try {
            $stmt = $this->pdo->query($sql);
            return new PDOResultWrapper($stmt);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function real_escape_string($str) {
        return addslashes($str);
    }

    public function set_charset($charset) {
        $this->pdo->exec("SET NAMES $charset");
    }

    public function __get($name) {
        if ($name === 'insert_id') return $this->pdo->lastInsertId();
        return null;
    }
}

// Global $conn (PDO wrapper)
$conn = new PDOConnWrapper($pdo);

// Timezone
date_default_timezone_set('Asia/Karachi');

// ============================================
// HELPER FUNCTIONS
// ============================================

function escape($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function formatPrice($price) {
    return 'Rs. ' . number_format($price, 0);
}

function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) redirect('login.php');
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) redirect('login.php');
}

function currentUser() {
    global $conn;
    if (!isLoggedIn()) return null;
    $id = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT id, name, email, phone, address, city FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function currentAdmin() {
    global $conn;
    if (!isAdminLoggedIn()) return null;
    $id = (int)$_SESSION['admin_id'];
    $stmt = $conn->prepare("SELECT id, name, email, role FROM admins WHERE id = ?");
    $stmt->bind_param("i", $id);
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getSetting($key, $default = '') {
    global $conn;
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? $row['setting_value'] : $default;
}

function getAllSettings() {
    global $conn;
    $settings = [];
    $result = $conn->query("SELECT setting_key, setting_value FROM settings");
    if (!$result) return $settings;
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

function updateSetting($key, $value) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->bind_param("sss", $key, $value, $value);
    return $stmt->execute();
}

function uploadImage($file, $folder = 'products') {
    $upload_dir = __DIR__ . '/uploads/' . $folder . '/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > 5 * 1024 * 1024) return false;
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowed)) return false;
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'img_' . time() . '_' . rand(1000, 9999) . '.' . strtolower($ext);
    $target = $upload_dir . $filename;
    if (move_uploaded_file($file['tmp_name'], $target)) {
        return 'uploads/' . $folder . '/' . $filename;
    }
    return false;
}

function deleteImage($path) {
    if (empty($path)) return;
    if (strpos($path, 'http') === 0) return;
    if (strpos($path, 'uploads/') === false) return;
    $full = __DIR__ . '/' . $path;
    if (file_exists($full)) unlink($full);
}

// ============================================
// DONE
// ============================================
?>