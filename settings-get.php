<?php
// ============================================
// SETTINGS GET API
// Frontend ke liye saari settings bhejo
// ============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../db.php';

$settings = [];

$result = $conn->query("SELECT setting_key, setting_value FROM settings");

while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

echo json_encode([
    'success' => true,
    'settings' => $settings
]);
?>