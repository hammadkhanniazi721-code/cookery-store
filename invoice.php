<?php
require_once '../db.php';
requireAdminLogin(); // Sirf admin dekh sakta hai

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get order
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Order not found.");
}

// Get order items
$items_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items = $items_stmt->get_result();
$settings = getAllSettings();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice #<?= htmlspecialchars($order['order_number']) ?> - Admin</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
    body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f1f5f9; padding: 40px; color: #1e293b; }
    .invoice-box { max-width: 800px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    .header { display: flex; justify-content: space-between; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px; margin-bottom: 30px; }
    .logo h1 { margin: 0; color: #3b82f6; font-size: 28px; }
    .details { margin-bottom: 30px; display: flex; justify-content: space-between; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
    th { background: #f8fafc; font-weight: bold; color: #475569; }
    .totals { text-align: right; }
    .totals div { margin-bottom: 10px; }
    .totals .grand-total { font-size: 20px; font-weight: bold; color: #3b82f6; border-top: 2px solid #e2e8f0; padding-top: 10px; }
    .btn-print { background: #3b82f6; color: #fff; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; text-decoration: none; display: inline-block; margin-top: 20px; }
    @media print {
        body { background: #fff; padding: 0; }
        .invoice-box { box-shadow: none; padding: 0; }
        .btn-print { display: none; }
    }
</style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <div class="logo">
                <h1><?= htmlspecialchars($settings['site_name'] ?? 'Cookery Store') ?></h1>
                <p style="margin:5px 0 0 0; color:#64748b;"><?= htmlspecialchars($settings['contact_address'] ?? '') ?></p>
                <p style="margin:2px 0 0 0; color:#64748b;"><?= htmlspecialchars($settings['contact_phone'] ?? '') ?></p>
            </div>
            <div style="text-align: right;">
                <h2 style="margin: 0; color: #0f172a;">INVOICE</h2>
                <p style="margin: 5px 0 0 0;"><strong>#<?= htmlspecialchars($order['order_number']) ?></strong></p>
                <p style="margin: 2px 0 0 0; color: #64748b;">Date: <?= date('d M Y', strtotime($order['created_at'])) ?></p>
            </div>
        </div>

        <div class="details">
            <div>
                <h4 style="margin: 0 0 10px 0; color: #475569;">Billed To:</h4>
                <strong><?= htmlspecialchars($order['customer_name']) ?></strong><br>
                <?= htmlspecialchars($order['customer_phone']) ?><br>
                <?= htmlspecialchars($order['customer_email']) ?><br>
                <?= htmlspecialchars($order['delivery_address']) ?>, <?= htmlspecialchars($order['city']) ?>
            </div>
            <div style="text-align: right;">
                <h4 style="margin: 0 0 10px 0; color: #475569;">Payment Info:</h4>
                <strong><?= htmlspecialchars($order['payment_method']) ?></strong><br>
                Status: <?= strtoupper($order['status']) ?>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $items->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td>Rs. <?= number_format($item['price'], 0) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td style="text-align: right;">Rs. <?= number_format($item['total'], 0) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="totals">
            <div>Subtotal: Rs. <?= number_format($order['subtotal'], 0) ?></div>
            <div>Delivery Fee: Rs. <?= number_format($order['delivery_fee'], 0) ?></div>
            <div class="grand-total">Total: Rs. <?= number_format($order['total'], 0) ?></div>
        </div>

        <div style="text-align: center; margin-top: 40px;">
            <button onclick="window.print()" class="btn-print"><i class="fas fa-print"></i> Print Invoice</button>
            <a href="orders.php" class="btn-print" style="background: #64748b; text-decoration: none;"><i class="fas fa-arrow-left"></i> Back to Orders</a>
        </div>
    </div>
</body>
</html>