<?php
// ============================================
// CATEGORIES API
// Fetch all categories with product count
// ============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../db.php';

$sql = "
    SELECT 
        c.id,
        c.name,
        c.slug,
        c.image,
        c.description,
        COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id
    GROUP BY c.id
    ORDER BY c.name ASC
";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'Query failed: ' . $conn->error
    ]);
    exit;
}

$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = [
        'id' => (int)$row['id'],
        'name' => $row['name'],
        'slug' => $row['slug'],
        'image' => $row['image'],
        'description' => $row['description'],
        'product_count' => (int)$row['product_count']
    ];
}

echo json_encode([
    'success' => true,
    'count' => count($categories),
    'categories' => $categories
]);
?>