<?php
$page_title = 'Sales Summary';
require_once 'header.php';

// ============================================
// SALES STATISTICS
// ============================================

// Today's Sales
$today = $conn->query("
    SELECT COUNT(*) AS orders, COALESCE(SUM(total), 0) AS sales 
    FROM orders 
    WHERE DATE(created_at) = CURDATE() AND status = 'completed'
")->fetch_assoc();

// This Week (last 7 days)
$week = $conn->query("
    SELECT COUNT(*) AS orders, COALESCE(SUM(total), 0) AS sales 
    FROM orders 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND status = 'completed'
")->fetch_assoc();

// This Month
$month = $conn->query("
    SELECT COUNT(*) AS orders, COALESCE(SUM(total), 0) AS sales 
    FROM orders 
    WHERE MONTH(created_at) = MONTH(CURDATE()) 
    AND YEAR(created_at) = YEAR(CURDATE()) 
    AND status = 'completed'
")->fetch_assoc();

// All Time
$all_time = $conn->query("
    SELECT COUNT(*) AS orders, COALESCE(SUM(total), 0) AS sales 
    FROM orders 
    WHERE status = 'completed'
")->fetch_assoc();

// Total Breakage Loss (this month)
$breakage = $conn->query("
    SELECT COALESCE(SUM(cost_loss), 0) AS loss, COALESCE(SUM(quantity), 0) AS qty
    FROM breakage 
    WHERE MONTH(breakage_date) = MONTH(CURDATE())
    AND YEAR(breakage_date) = YEAR(CURDATE())
")->fetch_assoc();

// Average Order Value
$avg = $conn->query("
    SELECT COALESCE(AVG(total), 0) AS avg_order 
    FROM orders 
    WHERE status = 'completed'
")->fetch_assoc();

// ============================================
// DAILY SALES CHART (Last 7 Days)
// ============================================
$daily_sales = $conn->query("
    SELECT 
        DATE(created_at) AS date,
        COUNT(*) AS orders,
        SUM(total) AS sales
    FROM orders 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    AND status = 'completed'
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");

$chart_data = [];
while ($row = $daily_sales->fetch_assoc()) {
    $chart_data[] = $row;
}

// ============================================
// TOP SELLING PRODUCTS (Top 5)
// ============================================
$top_products = $conn->query("
    SELECT 
        oi.product_name,
        oi.product_image,
        SUM(oi.quantity) AS total_qty,
        SUM(oi.total) AS total_sales
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status = 'completed'
    GROUP BY oi.product_id
    ORDER BY total_qty DESC
    LIMIT 5
");

// ============================================
// RECENT COMPLETED ORDERS
// ============================================
$recent = $conn->query("
    SELECT order_number, customer_name, total, created_at 
    FROM orders 
    WHERE status = 'completed'
    ORDER BY created_at DESC 
    LIMIT 10
");
?>

<!-- ============ PAGE HEADER ============ -->
<div class="page-header">
    <div>
        <h1>Sales Summary</h1>
        <p>Track your store's performance and revenue</p>
    </div>
    <button onclick="window.print()" class="btn btn-secondary">
        <i class="fas fa-print"></i> Print Report
    </button>
</div>

<!-- ============ SUMMARY CARDS ============ -->
<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-calendar-day"></i></div>
        <div class="stat-info">
            <h3><?= formatPrice($today['sales']) ?></h3>
            <p>Today's Sales (<?= $today['orders'] ?> orders)</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-calendar-week"></i></div>
        <div class="stat-info">
            <h3><?= formatPrice($week['sales']) ?></h3>
            <p>Last 7 Days (<?= $week['orders'] ?> orders)</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-calendar-alt"></i></div>
        <div class="stat-info">
            <h3><?= formatPrice($month['sales']) ?></h3>
            <p>This Month (<?= $month['orders'] ?> orders)</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-chart-line"></i></div>
        <div class="stat-info">
            <h3><?= formatPrice($all_time['sales']) ?></h3>
            <p>All Time (<?= $all_time['orders'] ?> orders)</p>
        </div>
    </div>

</div>

<!-- ============ SECOND ROW STATS ============ -->
<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-receipt"></i></div>
        <div class="stat-info">
            <h3><?= formatPrice($avg['avg_order']) ?></h3>
            <p>Avg. Order Value</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-info">
            <h3><?= formatPrice($breakage['loss']) ?></h3>
            <p>Breakage Loss (This Month)</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-box-open"></i></div>
        <div class="stat-info">
            <h3><?= $breakage['qty'] ?> items</h3>
            <p>Breakage Quantity</p>
        </div>
    </div>

</div>

<!-- ============ CHART SECTION ============ -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-chart-bar" style="color:#10b981;margin-right:8px;"></i> Last 7 Days Sales</h3>
    </div>

    <?php if (count($chart_data) > 0): ?>
    <div style="display:flex;align-items:flex-end;gap:16px;height:220px;padding:20px 10px;">
        <?php 
        $max_sales = max(array_column($chart_data, 'sales'));
        foreach ($chart_data as $day): 
            $height = $max_sales > 0 ? ($day['sales'] / $max_sales) * 100 : 0;
            $date = date('D', strtotime($day['date']));
        ?>
        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:8px;">
            <div style="font-size:11px;font-weight:700;color:#059669;">
                <?= formatPrice($day['sales']) ?>
            </div>
            <div style="
                width:100%;
                height: <?= max($height, 3) ?>%;
                background: linear-gradient(180deg, #10b981, #059669);
                border-radius: 8px 8px 0 0;
                transition: 0.3s;
                min-height: 8px;
                box-shadow: 0 4px 12px rgba(16,185,129,0.3);
            "></div>
            <div style="font-size:12px;font-weight:700;color:#64748b;">
                <?= $date ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-chart-bar"></i>
        <p>No sales data yet</p>
        <span>Chart will appear once you have completed orders</span>
    </div>
    <?php endif; ?>
</div>

<!-- ============ TWO COLUMN ============ -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">

    <!-- TOP PRODUCTS -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-fire" style="color:#f97316;margin-right:8px;"></i> Top Selling Products</h3>
        </div>

        <?php if ($top_products->num_rows > 0): ?>
        <div style="display:flex;flex-direction:column;gap:14px;">
            <?php $rank = 1; while ($p = $top_products->fetch_assoc()): ?>
            <div style="display:flex;align-items:center;gap:14px;padding:12px;border-radius:12px;background:#f8fafc;">
                <div style="
                    width:36px;height:36px;
                    background: <?= $rank == 1 ? '#fbbf24' : ($rank == 2 ? '#94a3b8' : ($rank == 3 ? '#f97316' : '#e2e8f0')) ?>;
                    color: <?= $rank <= 3 ? '#fff' : '#64748b' ?>;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: 800;
                    font-size: 14px;
                    flex-shrink: 0;
                "><?= $rank ?></div>
                <img src="<?= htmlspecialchars($p['product_image']) ?>" 
                     style="width:48px;height:48px;object-fit:cover;border-radius:8px;">
                <div style="flex:1;min-width:0;">
                    <p style="font-size:13px;font-weight:700;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        <?= htmlspecialchars($p['product_name']) ?>
                    </p>
                    <p style="font-size:12px;color:#64748b;margin-top:2px;">
                        <?= $p['total_qty'] ?> sold
                    </p>
                </div>
                <div style="font-size:14px;font-weight:800;color:#059669;white-space:nowrap;">
                    <?= formatPrice($p['total_sales']) ?>
                </div>
            </div>
            <?php $rank++; endwhile; ?>
        </div>
        <?php else: ?>
        <div class="empty-state" style="padding:40px 20px;">
            <i class="fas fa-box-open" style="font-size:50px;"></i>
            <p>No sales yet</p>
            <span>Top products will appear here</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- RECENT COMPLETED ORDERS -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-check-circle" style="color:#10b981;margin-right:8px;"></i> Recent Completed</h3>
        </div>

        <?php if ($recent->num_rows > 0): ?>
        <div style="display:flex;flex-direction:column;gap:10px;">
            <?php while ($r = $recent->fetch_assoc()): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px;border-radius:10px;background:#f8fafc;">
                <div>
                    <p style="font-size:13px;font-weight:700;color:#1e293b;">
                        <?= htmlspecialchars($r['order_number']) ?>
                    </p>
                    <p style="font-size:12px;color:#64748b;margin-top:2px;">
                        <?= htmlspecialchars($r['customer_name']) ?> • 
                        <?= date('d M, h:i A', strtotime($r['created_at'])) ?>
                    </p>
                </div>
                <div style="font-size:14px;font-weight:800;color:#059669;">
                    <?= formatPrice($r['total']) ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="empty-state" style="padding:40px 20px;">
            <i class="fas fa-receipt" style="font-size:50px;"></i>
            <p>No completed orders</p>
            <span>Orders will appear here once completed</span>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once 'footer.php'; ?>