<?php
// ============================================
// PLACE ORDER API
// Create order from cart items
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

$customer_name = trim($input['name'] ?? '');
$customer_phone = trim($input['phone'] ?? '');
$customer_email = trim($input['email'] ?? '');
$delivery_address = trim($input['address'] ?? '');
$city = trim($input['city'] ?? '');
$notes = trim($input['notes'] ?? '');
$payment_method = $input['payment_method'] ?? 'COD';

// Validation
if (empty($customer_name) || empty($customer_phone) || empty($delivery_address)) {
    echo json_encode(['success' => false, 'message' => 'Name, phone and address are required']);
    exit;
}

// Get cart items
$stmt = $conn->prepare("
    SELECT 
        c.quantity, 
        p.id AS product_id, 
        p.name, 
        p.price, 
        p.image, 
        p.stock
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_result = $stmt->get_result();
$stmt->close();

$cart_items = [];
$subtotal = 0;

while ($row = $cart_result->fetch_assoc()) {
    $cart_items[] = $row;
    $subtotal += (float)$row['price'] * (int)$row['quantity'];
}

if (empty($cart_items)) {
    echo json_encode(['success' => false, 'message' => 'Your cart is empty']);
    exit;
}

// Calculate delivery fee
$delivery_fee = $subtotal >= 1500 ? 0 : 150;
$total = $subtotal + $delivery_fee;

// Generate order number
$order_number = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);

// Start transaction
$conn->begin_transaction();

try {
    // Insert order
    $stmt = $conn->prepare("
        INSERT INTO orders 
        (user_id, order_number, customer_name, customer_phone, customer_email, 
         delivery_address, city, subtotal, delivery_fee, total, payment_method, notes, status) 
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?, 'pending')
    ");
    $stmt->bind_param(
        "issssssdddss",
        $user_id,
        $order_number,
        $customer_name,
        $customer_phone,
        $customer_email,
        $delivery_address,
        $city,
        $subtotal,
        $delivery_fee,
        $total,
        $payment_method,
        $notes
    );
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Insert order items + update stock
    $stmt_item = $conn->prepare("
        INSERT INTO order_items 
        (order_id, product_id, product_name, product_image, price, quantity, total) 
        VALUES (?,?,?,?,?,?,?)
    ");

    $stmt_stock = $conn->prepare("UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?");

    foreach ($cart_items as $item) {
        $line_total = (float)$item['price'] * (int)$item['quantity'];

        $stmt_item->bind_param(
            "iissdid",
            $order_id,
            $item['product_id'],
            $item['name'],
            $item['image'],
            $item['price'],
            $item['quantity'],
            $line_total
        );
        $stmt_item->execute();

        $stmt_stock->bind_param("ii", $item['quantity'], $item['product_id']);
        $stmt_stock->execute();
    }

    $stmt_item->close();
    $stmt_stock->close();

    // Clear cart
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    // Commit
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully!',
        'order_number' => $order_number,
        'order_id' => $order_id,
        'total' => $total
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Order failed: ' . $e->getMessage()
    ]);
}
?>