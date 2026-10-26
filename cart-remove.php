<?php
// ============================================
// CART REMOVE API
// Remove item completely from cart
// ============================================

session_start();
header('Content-Type: application/json');

require_once '../db.php';

// Login check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first', 'require_login' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Get input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

$product_id = (int)($input['product_id'] ?? 0);

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

// Delete from cart
$stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

if ($affected === 0) {
    echo json_encode(['success' => false, 'message' => 'Item not found in cart']);
    exit;
}

$total = $conn->query("SELECT COALESCE(SUM(quantity), 0) AS total FROM cart WHERE user_id = $user_id")->fetch_assoc()['total'];

echo json_encode([
    'success' => true,
    'message' => 'Item removed from cart',
    'total_items' => (int)$total
]);
?>