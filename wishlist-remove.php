<?php
// ============================================
// WISHLIST REMOVE API
// ============================================

session_start();
header('Content-Type: application/json');

require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
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

$stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$stmt->close();

echo json_encode([
    'success' => true,
    'message' => 'Removed from wishlist'
]);
?>