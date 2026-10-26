<?php
$page_title = 'Orders';
require_once 'header.php';

$message = '';
$message_type = '';

// ============================================
// UPDATE ORDER STATUS
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'] ?? '';
    
    $allowed = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
    
    if (in_array($new_status, $allowed)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        
        if ($stmt->execute()) {
            $message = "Order status updated successfully!";
            $message_type = 'success';
        } else {
            $message = "Failed to update status";
            $message_type = 'error';
        }
        $stmt->close();
    }
}

// ============================================
// UPDATE TRACKING NUMBER
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_tracking'])) {
    $order_id = (int)$_POST['order_id'];
    $tracking = trim($_POST['tracking_number']);
    
    $stmt = $conn->prepare("UPDATE orders SET tracking_number = ? WHERE id = ?");
    $stmt->bind_param("si", $tracking, $order_id);
    
    if ($stmt->execute()) {
        $message = "Tracking number updated successfully!";
        $message_type = 'success';
    } else {
        $message = "Failed to update tracking number";
        $message_type = 'error';
    }
    $stmt->close();
}

// ============================================
// FILTER BY STATUS
// ============================================
$filter = $_GET['status'] ?? 'all';
$where = '';
if (in_array($filter, ['pending', 'processing', 'shipped', 'completed', 'cancelled'])) {
    $where = "WHERE status = '" . $conn->real_escape_string($filter) . "'";
}

// ============================================
// GET ORDERS
// ============================================
$orders = $conn->query("
    SELECT id, order_number, customer_name, customer_phone, customer_email,
           delivery_address, city, total, payment_method, status, tracking_number, created_at
    FROM orders
    $where
    ORDER BY created_at DESC
");

// Count per status
$counts = [];
$statuses = ['all', 'pending', 'processing', 'shipped', 'completed', 'cancelled'];
foreach ($statuses as $s) {
    if ($s === 'all') {
        $counts[$s] = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'];
    } else {
        $counts[$s] = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE status = '$s'")->fetch_assoc()['c'];
    }
}
?>

<!-- ============ PAGE HEADER ============ -->
<div class="page-header">
    <div>
        <h1>Orders Management</h1>
        <p>View, filter and manage all customer orders</p>
    </div>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $message_type ?> alert-auto-hide">
    <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<!-- ============ FILTER TABS ============ -->
<div class="card" style="padding: 12px;">
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <?php foreach ($statuses as $s): ?>
        <a href="?status=<?= $s ?>" 
           class="btn <?= $filter === $s ? 'btn-primary' : 'btn-secondary' ?>"
           style="font-size: 13px;">
            <?= ucfirst($s) ?>
            <span style="background: rgba(0,0,0,0.15); padding: 2px 8px; border-radius: 10px; margin-left: 4px; font-size: 11px;">
                <?= $counts[$s] ?>
            </span>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- ============ ORDERS TABLE ============ -->
<div class="card">
    <div class="card-header">
        <h3>
            <i class="fas fa-shopping-bag" style="color:#10b981;margin-right:8px;"></i>
            <?= ucfirst($filter) ?> Orders
        </h3>
        <span style="font-size: 14px; color: #64748b;">
            <?= $orders->num_rows ?> total
        </span>
    </div>

    <div class="table-wrap">
        <?php if ($orders->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Contact</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($o = $orders->fetch_assoc()): ?>
                <tr>
                    <td>
                        <strong style="color: #059669;">
                            <?= htmlspecialchars($o['order_number']) ?>
                        </strong>
                    </td>

                    <td>
                        <div style="font-weight: 700;">
                            <?= htmlspecialchars($o['customer_name']) ?>
                        </div>
                        <?php if ($o['city']): ?>
                        <div style="font-size: 12px; color: #94a3b8;">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= htmlspecialchars($o['city']) ?>
                        </div>
                        <?php endif; ?>
                    </td>

                    <td style="font-size: 13px;">
                        <div><i class="fas fa-phone" style="color:#10b981;"></i> <?= htmlspecialchars($o['customer_phone']) ?></div>
                        <?php if ($o['customer_email']): ?>
                        <div style="color: #64748b; margin-top: 3px;">
                            <i class="fas fa-envelope"></i> <?= htmlspecialchars($o['customer_email']) ?>
                        </div>
                        <?php endif; ?>
                    </td>

                    <td>
                        <strong style="font-size: 15px; color: #1e293b;">
                            Rs. <?= number_format($o['total'], 0) ?>
                        </strong>
                    </td>

                    <td>
                        <?php
                        $status = $o['status'];
                        $badge = 'secondary';
                        if ($status === 'completed') $badge = 'success';
                        elseif ($status === 'pending') $badge = 'warning';
                        elseif ($status === 'cancelled') $badge = 'danger';
                        elseif ($status === 'processing') $badge = 'info';
                        elseif ($status === 'shipped') $badge = 'info';
                        ?>
                        <span class="badge <?= $badge ?>">
                            <?= ucfirst($status) ?>
                        </span>
                    </td>

                    <td style="font-size: 13px; color: #64748b;">
                        <?= date('d M Y', strtotime($o['created_at'])) ?>
                        <div style="font-size: 11px;">
                            <?= date('h:i A', strtotime($o['created_at'])) ?>
                        </div>
                    </td>

                    <td>
    <div style="display:flex; gap:5px;">
        <button class="btn btn-edit" onclick='openOrderModal(<?= json_encode($o, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
            <i class="fas fa-eye"></i> View
        </button>
        
        <?php if ($o['status'] === 'completed'): ?>
        <a href="invoice.php?id=<?= $o['id'] ?>" target="_blank" class="btn btn-primary" style="padding: 6px 12px; font-size: 13px;">
            <i class="fas fa-file-invoice"></i> Invoice
        </a>
        <?php endif; ?>
    </div>
</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-shopping-bag"></i>
            <p>No orders found</p>
            <span>
                <?= $filter === 'all' ? 'Orders will appear here once customers start buying' : "No $filter orders" ?>
            </span>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============ ORDER DETAIL MODAL ============ -->
<div id="orderModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#fff; border-radius:20px; max-width:700px; width:100%; max-height:90vh; overflow-y:auto;">
        <div style="padding:24px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;">
            <h2 style="font-size:22px; color:#1e293b; font-weight:800;">Order Details</h2>
            <button onclick="closeModal('orderModal')" style="background:none; border:none; font-size:22px; cursor:pointer; color:#94a3b8;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div style="padding:24px;">
            <div id="orderDetails"></div>
        </div>
    </div>
</div>

<script>
function openOrderModal(order) {
    const html = `
        <div style="margin-bottom:20px;">
            <div style="font-size:13px; color:#94a3b8; font-weight:700; text-transform:uppercase;">Order Number</div>
            <div style="font-size:20px; color:#059669; font-weight:800; margin-top:4px;">${order.order_number}</div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
            <div>
                <div style="font-size:12px; color:#94a3b8; font-weight:700;">CUSTOMER</div>
                <div style="font-size:15px; font-weight:700; color:#1e293b; margin-top:4px;">${order.customer_name}</div>
            </div>
            <div>
                <div style="font-size:12px; color:#94a3b8; font-weight:700;">PHONE</div>
                <div style="font-size:15px; font-weight:700; color:#1e293b; margin-top:4px;">${order.customer_phone}</div>
            </div>
        </div>

        ${order.customer_email ? `
        <div style="margin-bottom:20px;">
            <div style="font-size:12px; color:#94a3b8; font-weight:700;">EMAIL</div>
            <div style="font-size:15px; font-weight:700; color:#1e293b; margin-top:4px;">${order.customer_email}</div>
        </div>
        ` : ''}

        <div style="margin-bottom:20px; padding:14px; background:#f8fafc; border-radius:10px;">
            <div style="font-size:12px; color:#94a3b8; font-weight:700;">DELIVERY ADDRESS</div>
            <div style="font-size:14px; color:#1e293b; margin-top:4px; line-height:1.6;">
                ${order.delivery_address}<br>
                ${order.city ? 'City: ' + order.city : ''}
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:20px;">
            <div style="padding:12px; background:#ecfdf5; border-radius:10px; text-align:center;">
                <div style="font-size:11px; color:#64748b; font-weight:700;">TOTAL</div>
                <div style="font-size:16px; color:#059669; font-weight:800; margin-top:4px;">Rs. ${parseFloat(order.total).toFixed(0)}</div>
            </div>
            <div style="padding:12px; background:#dbeafe; border-radius:10px; text-align:center;">
                <div style="font-size:11px; color:#64748b; font-weight:700;">PAYMENT</div>
                <div style="font-size:16px; color:#1e40af; font-weight:800; margin-top:4px;">${order.payment_method}</div>
            </div>
            <div style="padding:12px; background:#fef3c7; border-radius:10px; text-align:center;">
                <div style="font-size:11px; color:#64748b; font-weight:700;">DATE</div>
                <div style="font-size:14px; color:#92400e; font-weight:800; margin-top:4px;">${order.created_at.split(' ')[0]}</div>
            </div>
        </div>

        <form method="POST" style="border-top:1px solid #f1f5f9; padding-top:20px;">
            <input type="hidden" name="order_id" value="${order.id}">
            <div class="form-group">
                <label>Update Status</label>
                <div style="display:flex; gap:10px;">
                    <select name="status" class="form-control" style="flex:1;">
                        <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>Pending</option>
                        <option value="processing" ${order.status === 'processing' ? 'selected' : ''}>Processing</option>
                        <option value="shipped" ${order.status === 'shipped' ? 'selected' : ''}>Shipped</option>
                        <option value="completed" ${order.status === 'completed' ? 'selected' : ''}>Completed</option>
                        <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                    </select>
                    <button type="submit" name="update_status" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                </div>
            </div>
        </form>

       
    `;

    document.getElementById('orderDetails').innerHTML = html;
    document.getElementById('orderModal').style.display = 'flex';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
</script>

<?php require_once 'footer.php'; ?>