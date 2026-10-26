<?php
// ============================================
// CART UPDATE API
// Update cart item quantity
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
$action = $input['action'] ?? '';       // 'increase' or 'decrease'
$quantity = isset($input['quantity']) ? (int)$input['quantity'] : null;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

// Find cart item
$stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$cart_item = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$cart_item) {
    echo json_encode(['success' => false, 'message' => 'Item not in cart']);
    exit;
}

$current_qty = (int)$cart_item['quantity'];
$new_qty = $current_qty;

// Change quantity
if ($quantity !== null) {
    // Direct set
    $new_qty = $quantity;
} elseif ($action === 'increase') {
    $new_qty = $current_qty + 1;
} elseif ($action === 'decrease') {
    $new_qty = $current_qty - 1;
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

// If 0 or less, remove item
if ($new_qty <= 0) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ?");
    $stmt->bind_param("i", $cart_item['id']);
    $stmt->execute();
    $stmt->close();

    $total = $conn->query("SELECT COALESCE(SUM(quantity), 0) AS total FROM cart WHERE user_id = $user_id")->fetch_assoc()['total'];

    echo json_encode([
        'success' => true,
        'message' => 'Item removed',
        'removed' => true,
        'total_items' => (int)$total
    ]);
    exit;
}

// Update quantity
$stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
$stmt->bind_param("ii", $new_qty, $cart_item['id']);
$stmt->execute();
$stmt->close();

$total = $conn->query("SELECT COALESCE(SUM(quantity), 0) AS total FROM cart WHERE user_id = $user_id")->fetch_assoc()['total'];

echo json_encode([
    'success' => true,
    'message' => 'Cart updated',
    'quantity' => $new_qty,
    'total_items' => (int)$total
]);
?>