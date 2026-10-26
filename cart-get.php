<?php
// ============================================
// CART GET API
// Get all cart items for logged in user
// ============================================

session_start();
header('Content-Type: application/json');

require_once '../db.php';

// Login check
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => true,
        'logged_in' => false,
        'items' => [],
        'total_items' => 0,
        'subtotal' => 0
    ]);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Get cart items with product details
$sql = "
    SELECT 
        c.id AS cart_id,
        c.quantity,
        p.id AS product_id,
        p.name,
        p.price,
        p.image,
        p.stock,
        c.quantity * p.price AS line_total
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
    ORDER BY c.added_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$subtotal = 0;
$total_items = 0;

while ($row = $result->fetch_assoc()) {
    $items[] = [
        'cart_id' => (int)$row['cart_id'],
        'product_id' => (int)$row['product_id'],
        'name' => $row['name'],
        'price' => (float)$row['price'],
        'image' => $row['image'],
        'stock' => (int)$row['stock'],
        'quantity' => (int)$row['quantity'],
        'line_total' => (float)$row['line_total']
    ];
    $subtotal += (float)$row['line_total'];
    $total_items += (int)$row['quantity'];
}

$stmt->close();

$delivery_fee = $total_items > 0 ? 150 : 0;

echo json_encode([
    'success' => true,
    'logged_in' => true,
    'items' => $items,
    'total_items' => $total_items,
    'subtotal' => $subtotal,
    'delivery_fee' => $delivery_fee,
    'total' => $subtotal + $delivery_fee
]);
?>