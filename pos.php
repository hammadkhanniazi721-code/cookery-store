<?php
$page_title = 'POS / Billing';
require_once 'header.php';

$message = '';

// Handle Bill Generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_bill'])) {
    $customer_name = trim($_POST['customer_name'] ?? 'Walk-in Customer');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $product_id = (int)$_POST['product_id'];
    $qty = (int)$_POST['quantity'];
    
    // Get product
    $stmt = $conn->prepare("SELECT name, price, stock FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $prod = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($prod && $qty > 0 && $prod['stock'] >= $qty) {
        $total = $prod['price'] * $qty;
        $order_number = 'POS-' . date('Ymd') . '-' . rand(1000, 9999);
        
        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (order_number, customer_name, customer_phone, delivery_address, city, subtotal, delivery_fee, total, payment_method, status) VALUES (?, ?, ?, 'Walk-in', 'In-Store', ?, 0, ?, 'COD', 'completed')");
        $stmt->bind_param("sssdd", $order_number, $customer_name, $customer_phone, $total, $total);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();
        
        // Insert order item
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, total) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisdid", $order_id, $product_id, $prod['name'], $prod['price'], $qty, $total);
        $stmt->execute();
        $stmt->close();
        
        // Update stock
        $conn->query("UPDATE products SET stock = stock - $qty WHERE id = $product_id");
        
        $message = "Bill Generated! Order #$order_number. <a href='invoice.php?id=$order_id' target='_blank'>Print Invoice</a>";
    } else {
        $message = "Out of stock or invalid quantity!";
    }
}

$products = $conn->query("SELECT id, name, price, stock FROM products WHERE stock > 0 ORDER BY name");
?>

<div class="page-header">
    <div>
        <h1>POS / Billing</h1>
        <p>Generate direct bills for walk-in customers</p>
    </div>
</div>

<?php if ($message): ?>
<div class="alert alert-success alert-auto-hide"><?= $message ?></div>
<?php endif; ?>

<div class="card" style="max-width: 600px;">
    <div class="card-header"><h3>Create New Bill</h3></div>
    <form method="POST">
        <div class="form-group">
            <label>Customer Name</label>
            <input type="text" name="customer_name" class="form-control" placeholder="Walk-in Customer">
        </div>
        <div class="form-group">
            <label>Phone (Optional)</label>
            <input type="text" name="customer_phone" class="form-control">
        </div>
        <div class="form-group">
            <label>Select Product</label>
            <select name="product_id" class="form-control" required>
                <option value="">Choose...</option>
                <?php while ($p = $products->fetch_assoc()): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (Rs. <?= $p['price'] ?>) - Stock: <?= $p['stock'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Quantity</label>
            <input type="number" name="quantity" class="form-control" value="1" min="1" required>
        </div>
        <button type="submit" name="generate_bill" class="btn btn-primary btn-full">Generate Bill</button>
    </form>
</div>

<?php require_once 'footer.php'; ?>