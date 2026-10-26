<?php
$page_title = 'Breakage Tracker';
require_once 'header.php';

$message = '';
$message_type = '';

// ============================================
// RECORD BREAKAGE
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['record_breakage'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $reason = $_POST['reason'] ?? 'damaged';
    $notes = trim($_POST['notes'] ?? '');
    $breakage_date = $_POST['breakage_date'] ?? date('Y-m-d');

    $allowed_reasons = ['damaged', 'expired', 'broken', 'lost', 'other'];
    if (!in_array($reason, $allowed_reasons)) {
        $reason = 'damaged';
    }

    if ($product_id <= 0 || $quantity <= 0) {
        $message = 'Please select product and enter quantity';
        $message_type = 'error';
    } else {
        // Product ka price le lo
        $p_stmt = $conn->prepare("SELECT price, stock FROM products WHERE id = ?");
        $p_stmt->bind_param("i", $product_id);
        $p_stmt->execute();
        $prod = $p_stmt->get_result()->fetch_assoc();
        $p_stmt->close();

        if (!$prod) {
            $message = 'Product not found';
            $message_type = 'error';
        } else {
            $cost_loss = $prod['price'] * $quantity;

            // Insert breakage
            $stmt = $conn->prepare("INSERT INTO breakage (product_id, quantity, reason, notes, cost_loss, recorded_by, breakage_date) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param("iissdis", $product_id, $quantity, $reason, $notes, $cost_loss, $_SESSION['admin_id'], $breakage_date);

            if ($stmt->execute()) {
                // Stock kam karo
                $conn->query("UPDATE products SET stock = GREATEST(stock - $quantity, 0) WHERE id = $product_id");
                $message = "Breakage recorded successfully! Loss: " . formatPrice($cost_loss);
                $message_type = 'success';
            } else {
                $message = 'Failed to record breakage';
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
}

// ============================================
// DELETE BREAKAGE RECORD
// ============================================
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM breakage WHERE id = $id");
    $message = 'Breakage record deleted!';
    $message_type = 'success';
}

// ============================================
// FILTERS
// ============================================
$filter_reason = $_GET['reason'] ?? 'all';
$filter_period = $_GET['period'] ?? 'all';

$where = [];
if (in_array($filter_reason, ['damaged', 'expired', 'broken', 'lost', 'other'])) {
    $where[] = "b.reason = '" . $conn->real_escape_string($filter_reason) . "'";
}

if ($filter_period === 'today') {
    $where[] = "b.breakage_date = CURDATE()";
} elseif ($filter_period === 'week') {
    $where[] = "b.breakage_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
} elseif ($filter_period === 'month') {
    $where[] = "MONTH(b.breakage_date) = MONTH(CURDATE()) AND YEAR(b.breakage_date) = YEAR(CURDATE())";
}

$where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// ============================================
// GET BREAKAGE RECORDS
// ============================================
$records = $conn->query("
    SELECT b.*, p.name AS product_name, p.image AS product_image, a.name AS admin_name
    FROM breakage b
    LEFT JOIN products p ON b.product_id = p.id
    LEFT JOIN admins a ON b.recorded_by = a.id
    $where_sql
    ORDER BY b.breakage_date DESC, b.id DESC
");

// ============================================
// STATISTICS
// ============================================
$total_loss = $conn->query("SELECT COALESCE(SUM(cost_loss), 0) AS total FROM breakage")->fetch_assoc()['total'];
$total_qty = $conn->query("SELECT COALESCE(SUM(quantity), 0) AS total FROM breakage")->fetch_assoc()['total'];
$total_records = $conn->query("SELECT COUNT(*) AS total FROM breakage")->fetch_assoc()['total'];

$month_loss = $conn->query("
    SELECT COALESCE(SUM(cost_loss), 0) AS total 
    FROM breakage 
    WHERE MONTH(breakage_date) = MONTH(CURDATE()) 
    AND YEAR(breakage_date) = YEAR(CURDATE())
")->fetch_assoc()['total'];

$today_loss = $conn->query("
    SELECT COALESCE(SUM(cost_loss), 0) AS total 
    FROM breakage 
    WHERE breakage_date = CURDATE()
")->fetch_assoc()['total'];

// ============================================
// REASON BREAKDOWN
// ============================================
$reasons_data = [];
$reasons_list = ['damaged', 'expired', 'broken', 'lost', 'other'];
foreach ($reasons_list as $r) {
    $r_data = $conn->query("
        SELECT COALESCE(SUM(cost_loss), 0) AS loss, COALESCE(SUM(quantity), 0) AS qty, COUNT(*) AS cnt
        FROM breakage 
        WHERE reason = '$r'
    ")->fetch_assoc();
    $reasons_data[$r] = $r_data;
}

// ============================================
// TOP BREAKAGE PRODUCTS
// ============================================
$top_products = $conn->query("
    SELECT 
        p.name, p.image,
        SUM(b.quantity) AS total_qty,
        SUM(b.cost_loss) AS total_loss,
        COUNT(b.id) AS times
    FROM breakage b
    JOIN products p ON b.product_id = p.id
    GROUP BY b.product_id
    ORDER BY total_loss DESC
    LIMIT 5
");
?>

<!-- ============ PAGE HEADER ============ -->
<div class="page-header">
    <div>
        <h1>Breakage Tracker</h1>
        <p>Track damaged, expired, and lost products</p>
    </div>
    <button onclick="openModal('breakageModal')" class="btn btn-primary">
        <i class="fas fa-exclamation-triangle"></i> Record Breakage
    </button>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $message_type ?> alert-auto-hide">
    <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<!-- ============ STATS CARDS ============ -->
<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-info">
            <h3><?= formatPrice($total_loss) ?></h3>
            <p>Total Loss (All Time)</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-calendar-day"></i></div>
        <div class="stat-info">
            <h3><?= formatPrice($today_loss) ?></h3>
            <p>Today's Loss</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-calendar-alt"></i></div>
        <div class="stat-info">
            <h3><?= formatPrice($month_loss) ?></h3>
            <p>This Month's Loss</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-box-open"></i></div>
        <div class="stat-info">
            <h3><?= $total_qty ?> items</h3>
            <p>Total Quantity Lost</p>
        </div>
    </div>

</div>

<!-- ============ REASON BREAKDOWN ============ -->
<div class="card">
    <div class="card-header">
        <h3>
            <i class="fas fa-chart-pie" style="color:#10b981;margin-right:8px;"></i>
            Loss by Reason
        </h3>
        <span style="font-size:14px;color:#64748b;"><?= $total_records ?> total records</span>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;">
        <?php 
        $reason_info = [
            'damaged' => ['label' => 'Damaged', 'icon' => 'fa-truck', 'color' => 'orange'],
            'expired' => ['label' => 'Expired', 'icon' => 'fa-clock', 'color' => 'red'],
            'broken'  => ['label' => 'Broken', 'icon' => 'fa-heart-broken', 'color' => 'purple'],
            'lost'    => ['label' => 'Lost', 'icon' => 'fa-question-circle', 'color' => 'blue'],
            'other'   => ['label' => 'Other', 'icon' => 'fa-ellipsis-h', 'color' => 'green'],
        ];
        foreach ($reason_info as $key => $info): 
            $data = $reasons_data[$key];
        ?>
        <div style="padding:18px;border-radius:14px;background:#f8fafc;border:1px solid #f1f5f9;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                <div class="stat-icon <?= $info['color'] ?>" style="width:38px;height:38px;font-size:15px;">
                    <i class="fas <?= $info['icon'] ?>"></i>
                </div>
                <div>
                    <div style="font-size:14px;font-weight:800;color:#1e293b;"><?= $info['label'] ?></div>
                    <div style="font-size:11px;color:#94a3b8;"><?= $data['cnt'] ?> times</div>
                </div>
            </div>
            <div style="font-size:18px;font-weight:800;color:#dc2626;">
                <?= formatPrice($data['loss']) ?>
            </div>
            <div style="font-size:12px;color:#64748b;margin-top:2px;">
                <?= $data['qty'] ?> items lost
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ============ FILTERS ============ -->
<div class="card" style="padding: 12px;">
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        <span style="font-size:13px;font-weight:700;color:#64748b;padding:0 8px;">Reason:</span>
        <a href="?reason=all&period=<?= $filter_period ?>" class="btn <?= $filter_reason === 'all' ? 'btn-primary' : 'btn-secondary' ?>" style="font-size:13px;">All</a>
        <a href="?reason=damaged&period=<?= $filter_period ?>" class="btn <?= $filter_reason === 'damaged' ? 'btn-primary' : 'btn-secondary' ?>" style="font-size:13px;">Damaged</a>
        <a href="?reason=expired&period=<?= $filter_period ?>" class="btn <?= $filter_reason === 'expired' ? 'btn-primary' : 'btn-secondary' ?>" style="font-size:13px;">Expired</a>
        <a href="?reason=broken&period=<?= $filter_period ?>" class="btn <?= $filter_reason === 'broken' ? 'btn-primary' : 'btn-secondary' ?>" style="font-size:13px;">Broken</a>
        <a href="?reason=lost&period=<?= $filter_period ?>" class="btn <?= $filter_reason === 'lost' ? 'btn-primary' : 'btn-secondary' ?>" style="font-size:13px;">Lost</a>

        <span style="font-size:13px;font-weight:700;color:#64748b;padding:0 8px;margin-left:auto;">Period:</span>
        <a href="?period=all&reason=<?= $filter_reason ?>" class="btn <?= $filter_period === 'all' ? 'btn-primary' : 'btn-secondary' ?>" style="font-size:13px;">All</a>
        <a href="?period=today&reason=<?= $filter_reason ?>" class="btn <?= $filter_period === 'today' ? 'btn-primary' : 'btn-secondary' ?>" style="font-size:13px;">Today</a>
        <a href="?period=week&reason=<?= $filter_reason ?>" class="btn <?= $filter_period === 'week' ? 'btn-primary' : 'btn-secondary' ?>" style="font-size:13px;">Week</a>
        <a href="?period=month&reason=<?= $filter_reason ?>" class="btn <?= $filter_period === 'month' ? 'btn-primary' : 'btn-secondary' ?>" style="font-size:13px;">Month</a>
    </div>
</div>

<!-- ============ BREAKAGE RECORDS TABLE ============ -->
<div class="card">
    <div class="card-header">
        <h3>
            <i class="fas fa-list" style="color:#10b981;margin-right:8px;"></i>
            Breakage Records
        </h3>
        <span style="font-size:14px;color:#64748b;"><?= $records->num_rows ?> records</span>
    </div>

    <div class="table-wrap">
        <?php if ($records->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Reason</th>
                    <th>Loss</th>
                    <th>Notes</th>
                    <th>Recorded By</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $reason_badges = [
                    'damaged' => 'warning',
                    'expired' => 'danger',
                    'broken' => 'danger',
                    'lost' => 'info',
                    'other' => 'secondary',
                ];
                while ($r = $records->fetch_assoc()): 
                ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <img src="<?= htmlspecialchars($r['product_image'] ?? '') ?>" 
                                 style="width:48px;height:48px;object-fit:cover;border-radius:8px;">
                            <div style="font-weight:700;font-size:14px;color:#1e293b;">
                                <?= htmlspecialchars($r['product_name'] ?? 'Deleted Product') ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <strong style="font-size:16px;color:#dc2626;">
                            -<?= $r['quantity'] ?>
                        </strong>
                    </td>
                    <td>
                        <span class="badge <?= $reason_badges[$r['reason']] ?? 'secondary' ?>">
                            <?= ucfirst($r['reason']) ?>
                        </span>
                    </td>
                    <td>
                        <strong style="color:#dc2626;font-size:15px;">
                            <?= formatPrice($r['cost_loss']) ?>
                        </strong>
                    </td>
                    <td style="font-size:13px;color:#64748b;max-width:200px;">
                        <?= htmlspecialchars($r['notes'] ?: '—') ?>
                    </td>
                    <td style="font-size:13px;">
                        <?= htmlspecialchars($r['admin_name'] ?? 'Unknown') ?>
                    </td>
                    <td style="font-size:13px;color:#64748b;">
                        <?= date('d M Y', strtotime($r['breakage_date'])) ?>
                    </td>
                    <td>
                        <a href="?delete=<?= $r['id'] ?>" 
                           class="btn btn-delete"
                           onclick="return confirmDelete('Delete this breakage record?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-check-circle" style="color:#10b981;"></i>
            <p>No breakage records</p>
            <span>Great! No breakage recorded yet. Click "Record Breakage" to add.</span>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============ TOP BREAKAGE PRODUCTS ============ -->
<?php if ($top_products->num_rows > 0): ?>
<div class="card">
    <div class="card-header">
        <h3>
            <i class="fas fa-chart-bar" style="color:#dc2626;margin-right:8px;"></i>
            Top 5 Most Broken Products
        </h3>
    </div>

    <div style="display:flex;flex-direction:column;gap:12px;">
        <?php $rank = 1; while ($p = $top_products->fetch_assoc()): ?>
        <div style="display:flex;align-items:center;gap:14px;padding:14px;border-radius:12px;background:#fef2f2;border:1px solid #fee2e2;">
            <div style="
                width:36px;height:36px;
                background:#dc2626;color:#fff;
                border-radius:50%;display:flex;align-items:center;justify-content:center;
                font-weight:800;font-size:14px;flex-shrink:0;
            "><?= $rank ?></div>
            <img src="<?= htmlspecialchars($p['image']) ?>" style="width:48px;height:48px;object-fit:cover;border-radius:8px;">
            <div style="flex:1;min-width:0;">
                <div style="font-size:14px;font-weight:700;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    <?= htmlspecialchars($p['name']) ?>
                </div>
                <div style="font-size:12px;color:#64748b;margin-top:2px;">
                    <?= $p['total_qty'] ?> items • <?= $p['times'] ?> times
                </div>
            </div>
            <div style="font-size:15px;font-weight:800;color:#dc2626;white-space:nowrap;">
                <?= formatPrice($p['total_loss']) ?>
            </div>
        </div>
        <?php $rank++; endwhile; ?>
    </div>
</div>
<?php endif; ?>

<!-- ============ RECORD BREAKAGE MODAL ============ -->
<div id="breakageModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#fff; border-radius:20px; max-width:550px; width:100%; max-height:90vh; overflow-y:auto;">
        <div style="padding:24px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;">
            <h2 style="font-size:22px; color:#1e293b; font-weight:800;">
                <i class="fas fa-exclamation-triangle" style="color:#dc2626;margin-right:8px;"></i>
                Record Breakage
            </h2>
            <button onclick="closeModal('breakageModal')" style="background:none; border:none; font-size:22px; cursor:pointer; color:#94a3b8;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form method="POST" style="padding:24px;">
            <div class="form-group">
                <label>Product *</label>
                <select name="product_id" class="form-control" required>
                    <option value="">-- Select Product --</option>
                    <?php 
                    $prods = $conn->query("SELECT id, name, stock, price FROM products ORDER BY name");
                    while ($p = $prods->fetch_assoc()): 
                    ?>
                    <option value="<?= $p['id'] ?>">
                        <?= htmlspecialchars($p['name']) ?> (Stock: <?= $p['stock'] ?>, Rs. <?= $p['price'] ?>)
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Quantity *</label>
                    <input type="number" name="quantity" class="form-control" required min="1" value="1">
                </div>
                <div class="form-group">
                    <label>Date *</label>
                    <input type="date" name="breakage_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Reason *</label>
                <select name="reason" class="form-control" required>
                    <option value="damaged">Damaged</option>
                    <option value="expired">Expired</option>
                    <option value="broken">Broken</option>
                    <option value="lost">Lost</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label>Notes (Optional)</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="e.g. During transport, fell from shelf..."></textarea>
            </div>

            <div style="background:#fee2e2; padding:14px; border-radius:10px; margin-bottom:20px; font-size:13px; color:#991b1b;">
                <i class="fas fa-info-circle"></i>
                <strong>Note:</strong> Ye record karne ke baad product ka stock kam ho jayega aur loss amount calculate hoga.
            </div>

            <div style="display:flex; gap:12px; padding-top:16px; border-top:1px solid #f1f5f9;">
                <button type="submit" name="record_breakage" class="btn btn-primary" style="flex:1; justify-content:center; background:linear-gradient(135deg,#dc2626,#991b1b);">
                    <i class="fas fa-save"></i> Record Breakage
                </button>
                <button type="button" onclick="closeModal('breakageModal')" class="btn btn-secondary">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>