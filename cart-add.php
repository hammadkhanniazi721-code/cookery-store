<?php
// ============================================
// CART ADD API
// Add product to cart (database)
// ============================================

session_start();
header('Content-Type: application/json');

require_once '../db.php';

// Login check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first', 'require_login' => true]);
    exit;
}

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Get input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

$product_id = (int)($input['product_id'] ?? 0);
$quantity = (int)($input['quantity'] ?? 1);

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
    exit;
}

// Check product exists
$stmt = $conn->prepare("SELECT id, name, stock FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// Check if already in cart
$stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Update OR Insert
if ($existing) {
    $new_qty = $existing['quantity'] + $quantity;
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_qty, $existing['id']);
    $stmt->execute();
    $stmt->close();
} else {
    $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?,?,?)");
    $stmt->bind_param("iii", $user_id, $product_id, $quantity);
    $stmt->execute();
    $stmt->close();
}

// Get total cart count (SIRF EK BAAR)
$total = $conn->query("SELECT COALESCE(SUM(quantity), 0) AS total FROM cart WHERE user_id = $user_id")->fetch_assoc()['total'];

// SIRF EK RESPONSE
echo json_encode([
    'success' => true,
    'message' => 'Added to cart',
    'total_items' => (int)$total
]);
?>







