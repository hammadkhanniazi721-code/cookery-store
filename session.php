<?php
// ============================================
// SESSION CHECK API
// Check if customer is logged in
// ============================================

session_start();
header('Content-Type: application/json');

require_once '../db.php';

if (isset($_SESSION['user_id'])) {
    // Get fresh user data
    $stmt = $conn->prepare("SELECT id, name, email, phone, city FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user) {
        echo json_encode([
            'logged_in' => true,
            'user' => $user
        ]);
    } else {
        // User deleted but session exists
        session_destroy();
        echo json_encode(['logged_in' => false]);
    }
} else {
    echo json_encode(['logged_in' => false]);
}
?>