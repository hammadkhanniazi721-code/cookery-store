<?php
// ============================================
// ADMIN DASHBOARD
// ============================================
require_once 'header.php';

// ============================================
// DASHBOARD STATS
// ============================================

// Total Sales (completed orders)
$sales_result = $conn->query("SELECT SUM(total) AS total_sales FROM orders WHERE status = 'completed'");
$total_sales = $sales_result->fetch_assoc()['total_sales'] ?? 0;

// Today's Sales
$today_result = $conn->query("SELECT SUM(total) AS today_sales FROM orders WHERE DATE(created_at) = CURDATE() AND status = 'completed'");
$today_sales = $today_result->fetch_assoc()['today_sales'] ?? 0;

// Total Orders
$orders_result = $conn->query("SELECT COUNT(*) AS total FROM orders");
$total_orders = $orders_result->fetch_assoc()['total'] ?? 0;

// Pending Orders
$pending_result = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status = 'pending'");
$pending_orders = $pending_result->fetch_assoc()['total'] ?? 0;

// Total Products
$products_result = $conn->query("SELECT COUNT(*) AS total FROM products");
$total_products = $products_result->fetch_assoc()['total'] ?? 0;

// Total Customers
$customers_result = $conn->query("SELECT COUNT(*) AS total FROM users");
$total_customers = $customers_result->fetch_assoc()['total'] ?? 0;

// Total Breakage Loss
$breakage_result = $conn->query("SELECT SUM(cost_loss) AS total_loss FROM breakage");
$total_breakage = $breakage_result->fetch_assoc()['total_loss'] ?? 0;

// Recent Orders (5)
$recent_orders = $conn->query("
    SELECT id, order_number, customer_name, total, status, created_at 
    FROM orders 
    ORDER BY created_at DESC 
    LIMIT 5
");

// Low Stock Products (stock < 20)
$low_stock = $conn->query("
    SELECT id, name, stock, image 
    FROM products 
    WHERE stock < 20 
    ORDER BY stock ASC 
    LIMIT 5
");
?>

<!-- ============ PAGE HEADER ============ -->
<div class="page-header">
    <div>
        <h1>Dashboard</h1>
        <p>Welcome back, <?= htmlspecialchars($admin_name) ?>! Here's what's happening today.</p>
    </div>
    <a href="products.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Product
    </a>
</div>

<!-- ============ STATS CARDS (CLICKABLE) ============ -->
<div class="stats-grid">

    <!-- Card 1: Total Sales -->
    <a href="sales.php" class="stat-card" style="text-decoration:none; color:inherit; cursor:pointer; display:flex;">
        <div class="stat-icon green"><i class="fas fa-dollar-sign"></i></div>
        <div class="stat-info">
            <h3><?= formatPrice($total_sales) ?></h3>
            <p>Total Sales</p>
        </div>
    </a>

    <!-- Card 2: Total Orders -->
    <a href="orders.php" class="stat-card" style="text-decoration:none; color:inherit; cursor:pointer; display:flex;">
        <div class="stat-icon blue"><i class="fas fa-shopping-bag"></i></div>
        <div class="stat-info">
            <h3><?= $total_orders ?></h3>
            <p>Total Orders</p>
        </div>
    </a>

    <!-- Card 3: Pending Orders -->
    <a href="orders.php?status=pending" class="stat-card" style="text-decoration:none; color:inherit; cursor:pointer; display:flex;">
        <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <h3><?= $pending_orders ?></h3>
            <p>Pending Orders</p>
        </div>
    </a>

    <!-- Card 4: Customers -->
    <a href="customers.php" class="stat-card" style="text-decoration:none; color:inherit; cursor:pointer; display:flex;">
        <div class="stat-icon purple"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <h3><?= $total_customers ?></h3>
            <p>Customers</p>
        </div>
    </a>

</div>

<!-- ============ SECOND ROW STATS ============ -->
<div class="stats-grid">

    <!-- Card 5: Today's Sales -->
    <a href="sales.php" class="stat-card" style="text-decoration:none; color:inherit; cursor:pointer; display:flex;">
        <div class="stat-icon green"><i class="fas fa-calendar-day"></i></div>
        <div class="stat-info">
            <h3><?= formatPrice($today_sales) ?></h3>
            <p>Today's Sales</p>
        </div>
    </a>

    <!-- Card 6: Total Products -->
    <a href="products.php" class="stat-card" style="text-decoration:none; color:inherit; cursor:pointer; display:flex;">
        <div class="stat-icon blue"><i class="fas fa-box"></i></div>
        <div class="stat-info">
            <h3><?= $total_products ?></h3>
            <p>Total Products</p>
        </div>
    </a>

    <!-- Card 7: Breakage Loss -->
    <a href="breakage.php" class="stat-card" style="text-decoration:none; color:inherit; cursor:pointer; display:flex;">
        <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-info">
            <h3><?= formatPrice($total_breakage) ?></h3>
            <p>Breakage Loss</p>
        </div>
    </a>

    <!-- Card 8: Completed Orders -->
    <a href="orders.php?status=completed" class="stat-card" style="text-decoration:none; color:inherit; cursor:pointer; display:flex;">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <h3><?= $total_orders - $pending_orders ?></h3>
            <p>Completed Orders</p>
        </div>
    </a>

</div>

<!-- ============ TWO COLUMN SECTION ============ -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">

    <!-- RECENT ORDERS -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-clock" style="color:#3b82f6;margin-right:8px;"></i> Recent Orders</h3>
            <a href="orders.php" class="btn btn-secondary">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="table-wrap">
            <?php if ($recent_orders->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $recent_orders->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($order['order_number']) ?></strong></td>
                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td><?= formatPrice($order['total']) ?></td>
                        <td>
                            <?php
                            $status = $order['status'];
                            $badge_class = 'secondary';
                            if ($status === 'completed') $badge_class = 'success';
                            elseif ($status === 'pending') $badge_class = 'warning';
                            elseif ($status === 'cancelled') $badge_class = 'danger';
                            elseif ($status === 'processing') $badge_class = 'info';
                            ?>
                            <span class="badge <?= $badge_class ?>">
                                <?= ucfirst($status) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-shopping-bag"></i>
                <p>No orders yet</p>
                <span>Orders will appear here once customers start buying</span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- LOW STOCK ALERT -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-exclamation-triangle" style="color:#f97316;margin-right:8px;"></i> Low Stock</h3>
            <a href="inventory.php" class="btn btn-secondary">
                View All
            </a>
        </div>

        <?php if ($low_stock->num_rows > 0): ?>
        <div style="display:flex;flex-direction:column;gap:14px;">
            <?php while ($product = $low_stock->fetch_assoc()): ?>
            <div style="display:flex;align-items:center;gap:12px;padding:10px;border-radius:10px;background:#f8fafc;">
                <img src="<?= htmlspecialchars($product['image']) ?>" 
                     alt="<?= htmlspecialchars($product['name']) ?>"
                     style="width:48px;height:48px;object-fit:cover;border-radius:8px;">
                <div style="flex:1;min-width:0;">
                    <p style="font-size:13px;font-weight:700;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        <?= htmlspecialchars($product['name']) ?>
                    </p>
                    <p style="font-size:12px;color:#dc2626;font-weight:700;margin-top:2px;">
                        <i class="fas fa-box"></i> Only <?= $product['stock'] ?> left
                    </p>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="empty-state" style="padding:30px 10px;">
            <i class="fas fa-check-circle" style="font-size:40px;color:#3b82f6;"></i>
            <p style="font-size:14px;">All stocked up!</p>
            <span style="font-size:12px;">No low stock products</span>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once 'footer.php'; ?>