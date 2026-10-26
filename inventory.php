<?php
$page_title = 'Inventory';
require_once 'header.php';

$message = '';
$message_type = '';

// ============================================
// ADD INVENTORY (Stock Add)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_stock'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $cost_price = !empty($_POST['cost_price']) ? (float)$_POST['cost_price'] : null;
    $supplier = trim($_POST['supplier'] ?? '');
    $batch_number = trim($_POST['batch_number'] ?? '');
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;

    if ($product_id <= 0 || $quantity <= 0) {
        $message = 'Please select product and enter quantity';
        $message_type = 'error';
    } else {
        // Insert into inventory
        $stmt = $conn->prepare("INSERT INTO inventory (product_id, quantity_added, quantity_remaining, cost_price, supplier, batch_number, expiry_date, added_by) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param("iiidsssi", $product_id, $quantity, $quantity, $cost_price, $supplier, $batch_number, $expiry_date, $_SESSION['admin_id']);

        if ($stmt->execute()) {
            // Update product stock
            $conn->query("UPDATE products SET stock = stock + $quantity WHERE id = $product_id");
            $message = "Stock added successfully!";
            $message_type = 'success';
        } else {
            $message = 'Failed to add stock';
            $message_type = 'error';
        }
        $stmt->close();
    }
}

// ============================================
// GET ALL PRODUCTS WITH STOCK
// ============================================
$search = trim($_GET['q'] ?? '');
$filter = $_GET['filter'] ?? 'all';

$where = [];
if (!empty($search)) {
    $s = $conn->real_escape_string($search);
    $where[] = "p.name LIKE '%$s%'";
}

if ($filter === 'low') {
    $where[] = "p.stock < 20 AND p.stock > 0";
} elseif ($filter === 'out') {
    $where[] = "p.stock = 0";
} elseif ($filter === 'ok') {
    $where[] = "p.stock >= 20";
}

$where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$products = $conn->query("
    SELECT p.id, p.name, p.image, p.stock, p.price, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    $where_sql
    ORDER BY p.stock ASC, p.name ASC
");

// Counts
$total = $conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'];
$low = $conn->query("SELECT COUNT(*) AS c FROM products WHERE stock < 20 AND stock > 0")->fetch_assoc()['c'];
$out = $conn->query("SELECT COUNT(*) AS c FROM products WHERE stock = 0")->fetch_assoc()['c'];
$ok = $conn->query("SELECT COUNT(*) AS c FROM products WHERE stock >= 20")->fetch_assoc()['c'];

// Total stock value
$value = $conn->query("SELECT SUM(stock * price) AS total FROM products")->fetch_assoc()['total'] ?? 0;

// Recent inventory entries
$recent = $conn->query("
    SELECT i.*, p.name AS product_name, p.image AS product_image, a.name AS admin_name
    FROM inventory i
    LEFT JOIN products p ON i.product_id = p.id
    LEFT JOIN admins a ON i.added_by = a.id
    ORDER BY i.added_at DESC
    LIMIT 10
");
?>

<!-- ============ PAGE HEADER ============ -->
<div class="page-header">
    <div>
        <h1>Inventory Management</h1>
        <p>Track stock levels and add new inventory</p>
    </div>
    <button onclick="openModal('addStockModal')" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Stock
    </button>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $message_type ?> alert-auto-hide">
    <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<!-- ============ STATS ============ -->
<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-boxes"></i></div>
        <div class="stat-info">
            <h3><?= $total ?></h3>
            <p>Total Products</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <h3><?= $ok ?></h3>
            <p>In Stock (OK)</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-info">
            <h3><?= $low ?></h3>
            <p>Low Stock</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-times-circle"></i></div>
        <div class="stat-info">
            <h3><?= $out ?></h3>
            <p>Out of Stock</p>
        </div>
    </div>

</div>

<div class="card">
    <div class="card-body">
        <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
            <div class="stat-icon green" style="width:50px;height:50px;font-size:20px;">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div>
                <div style="font-size:13px;color:#64748b;font-weight:700;">TOTAL STOCK VALUE</div>
                <div style="font-size:24px;color:#059669;font-weight:800;"><?= formatPrice($value) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- ============ FILTERS ============ -->
<div class="card" style="padding: 12px;">
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        <a href="?filter=all" class="btn <?= $filter === 'all' ? 'btn-primary' : 'btn-secondary' ?>" style="font-size:13px;">
            All <span style="background:rgba(0,0,0,0.15);padding:2px 8px;border-radius:10px;margin-left:4px;font-size:11px;"><?= $total ?></span>
        </a>
        <a href="?filter=ok" class="btn <?= $filter === 'ok' ? 'btn-primary' : 'btn-secondary' ?>" style="font-size:13px;">
            OK <span style="background:rgba(0,0,0,0.15);padding:2px 8px;border-radius:10px;margin-left:4px;font-size:11px;"><?= $ok ?></span>
        </a>
        <a href="?filter=low" class="btn <?= $filter === 'low' ? 'btn-primary' : 'btn-secondary' ?>" style="font-size:13px;">
            Low Stock <span style="background:rgba(0,0,0,0.15);padding:2px 8px;border-radius:10px;margin-left:4px;font-size:11px;"><?= $low ?></span>
        </a>
        <a href="?filter=out" class="btn <?= $filter === 'out' ? 'btn-primary' : 'btn-secondary' ?>" style="font-size:13px;">
            Out of Stock <span style="background:rgba(0,0,0,0.15);padding:2px 8px;border-radius:10px;margin-left:4px;font-size:11px;"><?= $out ?></span>
        </a>

        <form method="GET" style="margin-left:auto;display:flex;gap:8px;">
            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
            <input type="text" name="q" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>" 
                   class="form-control" style="width:220px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
</div>

<!-- ============ INVENTORY TABLE ============ -->
<div class="card">
    <div class="card-header">
        <h3>
            <i class="fas fa-warehouse" style="color:#10b981;margin-right:8px;"></i>
            Stock Levels
        </h3>
        <span style="font-size:14px;color:#64748b;"><?= $products->num_rows ?> products</span>
    </div>

    <div class="table-wrap">
        <?php if ($products->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Stock Value</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($p = $products->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <img src="<?= htmlspecialchars($p['image']) ?>" 
                                 style="width:48px;height:48px;object-fit:cover;border-radius:8px;">
                            <div style="font-weight:700;font-size:14px;color:#1e293b;">
                                <?= htmlspecialchars($p['name']) ?>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge secondary"><?= htmlspecialchars($p['category_name'] ?? '—') ?></span></td>
                    <td style="font-weight:700;"><?= formatPrice($p['price']) ?></td>
                    <td><strong style="font-size:16px;"><?= $p['stock'] ?></strong></td>
                    <td>
                        <?php if ($p['stock'] == 0): ?>
                        <span class="badge danger">Out of Stock</span>
                        <?php elseif ($p['stock'] < 20): ?>
                        <span class="badge warning">Low Stock</span>
                        <?php else: ?>
                        <span class="badge success">In Stock</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-weight:700;color:#059669;">
                        <?= formatPrice($p['stock'] * $p['price']) ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-warehouse"></i>
            <p>No products found</p>
            <span>Try changing filters or search</span>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============ RECENT STOCK ADDITIONS ============ -->
<div class="card">
    <div class="card-header">
        <h3>
            <i class="fas fa-history" style="color:#10b981;margin-right:8px;"></i>
            Recent Stock Additions
        </h3>
    </div>

    <?php if ($recent->num_rows > 0): ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity Added</th>
                    <th>Cost Price</th>
                    <th>Supplier</th>
                    <th>Added By</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($r = $recent->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <img src="<?= htmlspecialchars($r['product_image'] ?? '') ?>" 
                                 style="width:40px;height:40px;object-fit:cover;border-radius:8px;">
                            <span style="font-weight:700;font-size:13px;">
                                <?= htmlspecialchars($r['product_name'] ?? 'Deleted') ?>
                            </span>
                        </div>
                    </td>
                    <td><span class="badge success">+<?= $r['quantity_added'] ?></span></td>
                    <td><?= $r['cost_price'] ? formatPrice($r['cost_price']) : '—' ?></td>
                    <td><?= htmlspecialchars($r['supplier'] ?: '—') ?></td>
                    <td><?= htmlspecialchars($r['admin_name'] ?? 'Unknown') ?></td>
                    <td style="font-size:13px;color:#64748b;">
                        <?= date('d M Y', strtotime($r['added_at'])) ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state" style="padding:40px 20px;">
        <i class="fas fa-history" style="font-size:50px;"></i>
        <p>No stock additions yet</p>
        <span>Stock additions will appear here</span>
    </div>
    <?php endif; ?>
</div>

<!-- ============ ADD STOCK MODAL ============ -->
<div id="addStockModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#fff; border-radius:20px; max-width:550px; width:100%; max-height:90vh; overflow-y:auto;">
        <div style="padding:24px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;">
            <h2 style="font-size:22px; color:#1e293b; font-weight:800;">Add Stock</h2>
            <button onclick="closeModal('addStockModal')" style="background:none; border:none; font-size:22px; cursor:pointer; color:#94a3b8;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form method="POST" style="padding:24px;">
            <div class="form-group">
                <label>Select Product *</label>
                <select name="product_id" class="form-control" required>
                    <option value="">-- Choose Product --</option>
                    <?php 
                    $all_prods = $conn->query("SELECT id, name, stock FROM products ORDER BY name");
                    while ($p = $all_prods->fetch_assoc()): 
                    ?>
                    <option value="<?= $p['id'] ?>">
                        <?= htmlspecialchars($p['name']) ?> (Current: <?= $p['stock'] ?>)
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Quantity to Add *</label>
                <input type="number" name="quantity" class="form-control" required min="1" value="10">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Cost Price (Rs.)</label>
                    <input type="number" name="cost_price" class="form-control" min="0" step="1" placeholder="Optional">
                </div>
                <div class="form-group">
                    <label>Expiry Date</label>
                    <input type="date" name="expiry_date" class="form-control">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Supplier</label>
                    <input type="text" name="supplier" class="form-control" placeholder="e.g. Local Farm">
                </div>
                <div class="form-group">
                    <label>Batch Number</label>
                    <input type="text" name="batch_number" class="form-control" placeholder="Optional">
                </div>
            </div>

            <div style="display:flex; gap:12px; padding-top:16px; border-top:1px solid #f1f5f9;">
                <button type="submit" name="add_stock" class="btn btn-primary" style="flex:1; justify-content:center;">
                    <i class="fas fa-plus"></i> Add Stock
                </button>
                <button type="button" onclick="closeModal('addStockModal')" class="btn btn-secondary">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>