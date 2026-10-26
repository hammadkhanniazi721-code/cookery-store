<?php
// ============================================
// MY ORDERS API
// Get logged-in customer's orders
// ============================================

session_start();
header('Content-Type: application/json');

require_once '../db.php';

// Login check
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'logged_in' => false,
        'message' => 'Please login first'
    ]);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Get orders
$stmt = $conn->prepare("
    SELECT 
        id,
        order_number,
        customer_name,
        delivery_address,
        city,
        subtotal,
        delivery_fee,
        total,
        payment_method,
        status,
        notes,
        created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    // Get order items
    $item_stmt = $conn->prepare("
        SELECT product_name, product_image, price, quantity, total
        FROM order_items
        WHERE order_id = ?
    ");
    $item_stmt->bind_param("i", $row['id']);
    $item_stmt->execute();
    $items_result = $item_stmt->get_result();

    $items = [];
    while ($it = $items_result->fetch_assoc()) {
        $items[] = [
            'name' => $it['product_name'],
            'image' => $it['product_image'],
            'price' => (float)$it['price'],
            'quantity' => (int)$it['quantity'],
            'total' => (float)$it['total']
        ];
    }
    $item_stmt->close();

    $orders[] = [
        'id' => (int)$row['id'],
        'order_number' => $row['order_number'],
        'customer_name' => $row['customer_name'],
        'address' => $row['delivery_address'],
        'city' => $row['city'],
        'subtotal' => (float)$row['subtotal'],
        'delivery_fee' => (float)$row['delivery_fee'],
        'total' => (float)$row['total'],
        'payment_method' => $row['payment_method'],
        'status' => $row['status'],
        'notes' => $row['notes'],
        'created_at' => $row['created_at'],
        'items' => $items
    ];
}

$stmt->close();

echo json_encode([
    'success' => true,
    'logged_in' => true,
    'count' => count($orders),
    'orders' => $orders
]);
?>