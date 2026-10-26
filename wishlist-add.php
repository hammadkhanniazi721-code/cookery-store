<?php
// ============================================
// WISHLIST ADD API
// ============================================

session_start();
header('Content-Type: application/json');

require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

$product_id = (int)($input['product_id'] ?? 0);

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

// Check product exists
$stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    $stmt->close();
    exit;
}
$stmt->close();

// Insert (ignore if already exists)
$stmt = $conn->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$stmt->close();

// Get total wishlist count
$total = $conn->query("SELECT COUNT(*) AS total FROM wishlist WHERE user_id = $user_id")->fetch_assoc()['total'];

echo json_encode([
    'success' => true,
    'message' => 'Added to wishlist',
    'total_items' => (int)$total
]);
?>