<?php
// ============================================
// WISHLIST GET API
// ============================================

session_start();
header('Content-Type: application/json');

require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => true, 'logged_in' => false, 'items' => []]);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$sql = "
    SELECT 
        p.id,
        p.name,
        p.price,
        p.old_price,
        p.image,
        p.badge,
        p.stock,
        p.description,
        p.category_id,
        c.name AS category,
        c.slug AS category_slug
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE w.user_id = ?
    ORDER BY w.added_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = [
        'id' => (int)$row['id'],
        'name' => $row['name'],
        'price' => (float)$row['price'],
        'oldPrice' => $row['old_price'] ? (float)$row['old_price'] : null,
        'image' => $row['image'],
        'badge' => $row['badge'],
        'stock' => (int)$row['stock'],
        'description' => $row['description'],
        'category_id' => (int)$row['category_id'],
        'category' => $row['category'],
        'category_slug' => $row['category_slug']
    ];
}

$stmt->close();

echo json_encode([
    'success' => true,
    'logged_in' => true,
    'count' => count($items),
    'items' => $items
]);
?>