<?php
$page_title = 'Customers';
require_once 'header.php';

// ============================================
// FILTER
// ============================================
$search = trim($_GET['q'] ?? '');
$where = '';
if (!empty($search)) {
    $s = $conn->real_escape_string($search);
    $where = "WHERE u.name LIKE '%$s%' OR u.email LIKE '%$s%' OR u.phone LIKE '%$s%'";
}

// ============================================
// GET CUSTOMERS WITH ORDER STATS
// ============================================
$customers = $conn->query("
    SELECT 
        u.id,
        u.name,
        u.email,
        u.phone,
        u.city,
        u.created_at,
        COUNT(DISTINCT o.id) AS total_orders,
        COALESCE(SUM(CASE WHEN o.status = 'completed' THEN o.total ELSE 0 END), 0) AS total_spent
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    $where
    GROUP BY u.id
    ORDER BY total_spent DESC, u.created_at DESC
");

// ============================================
// STATS
// ============================================
$total_customers = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$total_orders = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'];
$active_customers = $conn->query("SELECT COUNT(DISTINCT user_id) AS c FROM orders")->fetch_assoc()['c'];
$total_revenue = $conn->query("SELECT COALESCE(SUM(total), 0) AS c FROM orders WHERE status = 'completed'")->fetch_assoc()['c'];

// Top customer
$top = $conn->query("
    SELECT u.name, COALESCE(SUM(o.total), 0) AS spent
    FROM users u
    JOIN orders o ON u.id = o.user_id
    WHERE o.status = 'completed'
    GROUP BY u.id
    ORDER BY spent DESC
    LIMIT 1
")->fetch_assoc();
?>

<!-- ============ PAGE HEADER ============ -->
<div class="page-header">
    <div>
        <h1>Customers</h1>
        <p>View all registered customers and their order history</p>
    </div>
</div>

<!-- ============ STATS ============ -->
<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <h3><?= $total_customers ?></h3>
            <p>Total Customers</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-user-check"></i></div>
        <div class="stat-info">
            <h3><?= $active_customers ?></h3>
            <p>Active Customers</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-shopping-bag"></i></div>
        <div class="stat-info">
            <h3><?= $total_orders ?></h3>
            <p>Total Orders</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-crown"></i></div>
        <div class="stat-info">
            <h3><?= $top ? htmlspecialchars($top['name']) : '—' ?></h3>
            <p>Top Customer (<?= $top ? formatPrice($top['spent']) : 'Rs. 0' ?>)</p>
        </div>
    </div>

</div>

<!-- ============ SEARCH ============ -->
<div class="card" style="padding: 16px;">
    <form method="GET" style="display:flex;gap:12px;">
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
               placeholder="Search by name, email, or phone..."
               class="form-control" style="flex:1;">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Search
        </button>
        <?php if ($search): ?>
        <a href="customers.php" class="btn btn-secondary">
            <i class="fas fa-times"></i> Clear
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- ============ CUSTOMERS TABLE ============ -->
<div class="card">
    <div class="card-header">
        <h3>
            <i class="fas fa-users" style="color:#10b981;margin-right:8px;"></i>
            All Customers
        </h3>
        <span style="font-size:14px;color:#64748b;"><?= $customers->num_rows ?> customers</span>
    </div>

    <div class="table-wrap">
        <?php if ($customers->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Contact</th>
                    <th>City</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($c = $customers->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="
                                width:44px;height:44px;
                                background:linear-gradient(135deg,#10b981,#059669);
                                color:#fff;border-radius:50%;
                                display:flex;align-items:center;justify-content:center;
                                font-weight:800;font-size:16px;flex-shrink:0;
                            "><?= strtoupper(substr($c['name'], 0, 1)) ?></div>
                            <div>
                                <div style="font-weight:700;font-size:14px;color:#1e293b;">
                                    <?= htmlspecialchars($c['name']) ?>
                                </div>
                                <div style="font-size:12px;color:#94a3b8;">
                                    ID: #<?= $c['id'] ?>
                                </div>
                            </div>
                        </div>
                    </td>

                    <td style="font-size:13px;">
                        <div><i class="fas fa-envelope" style="color:#10b981;width:14px;"></i> <?= htmlspecialchars($c['email']) ?></div>
                        <?php if ($c['phone']): ?>
                        <div style="color:#64748b;margin-top:3px;">
                            <i class="fas fa-phone" style="width:14px;"></i> <?= htmlspecialchars($c['phone']) ?>
                        </div>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($c['city'] ?: '—') ?>
                    </td>

                    <td>
                        <?php if ($c['total_orders'] > 0): ?>
                        <span class="badge success"><?= $c['total_orders'] ?></span>
                        <?php else: ?>
                        <span class="badge secondary">0</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <strong style="color:#059669;font-size:15px;">
                            <?= formatPrice($c['total_spent']) ?>
                        </strong>
                    </td>

                    <td style="font-size:13px;color:#64748b;">
                        <?= date('d M Y', strtotime($c['created_at'])) ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-users"></i>
            <p>No customers found</p>
            <span>Customers will appear here once they register</span>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>