<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <i class="fas fa-utensils"></i>
        <span>Cookery Admin</span>
    </div>
    <nav class="sidebar-nav">
        <p class="nav-title">MAIN</p>
        <a href="index.php" class="nav-item <?= $current_page === 'index.php' ? 'active' : '' ?>"><i class="fas fa-home"></i> <span>Dashboard</span></a>
        <a href="pos.php" class="nav-item <?= $current_page === 'pos.php' ? 'active' : '' ?>"><i class="fas fa-cash-register"></i> <span>POS / Billing</span></a>
        <a href="orders.php" class="nav-item <?= $current_page === 'orders.php' ? 'active' : '' ?>"><i class="fas fa-shopping-bag"></i> <span>Orders</span></a>
        <a href="sales.php" class="nav-item <?= $current_page === 'sales.php' ? 'active' : '' ?>"><i class="fas fa-chart-line"></i> <span>Sales Summary</span></a>

        <p class="nav-title">MANAGE</p>
        <a href="products.php" class="nav-item <?= $current_page === 'products.php' ? 'active' : '' ?>"><i class="fas fa-box"></i> <span>Products</span></a>
        <a href="categories.php" class="nav-item <?= $current_page === 'categories.php' ? 'active' : '' ?>"><i class="fas fa-tags"></i> <span>Categories</span></a>
        <a href="inventory.php" class="nav-item <?= $current_page === 'inventory.php' ? 'active' : '' ?>"><i class="fas fa-warehouse"></i> <span>Inventory</span></a>
        <a href="breakage.php" class="nav-item <?= $current_page === 'breakage.php' ? 'active' : '' ?>"><i class="fas fa-exclamation-triangle"></i> <span>Breakage Tracker</span></a>
        <a href="coupons.php" class="nav-item <?= $current_page === 'coupons.php' ? 'active' : '' ?>"><i class="fas fa-ticket-alt"></i> <span>Coupons</span></a>

        <p class="nav-title">ACCOUNTS</p>
        <a href="suppliers.php" class="nav-item <?= $current_page === 'suppliers.php' ? 'active' : '' ?>"><i class="fas fa-truck-loading"></i> <span>Suppliers</span></a>
        <a href="expenses.php" class="nav-item <?= $current_page === 'expenses.php' ? 'active' : '' ?>"><i class="fas fa-money-bill-wave"></i> <span>Expenses</span></a>
        <a href="profit-loss.php" class="nav-item <?= $current_page === 'profit-loss.php' ? 'active' : '' ?>"><i class="fas fa-file-invoice-dollar"></i> <span>Profit / Loss</span></a>

        <p class="nav-title">USERS & SYSTEM</p>
        <a href="customers.php" class="nav-item <?= $current_page === 'customers.php' ? 'active' : '' ?>"><i class="fas fa-users"></i> <span>Customers</span></a>
        <a href="staff.php" class="nav-item <?= $current_page === 'staff.php' ? 'active' : '' ?>"><i class="fas fa-user-tie"></i> <span>Staff</span></a>
        <a href="settings.php" class="nav-item <?= $current_page === 'settings.php' ? 'active' : '' ?>"><i class="fas fa-cog"></i> <span>Settings</span></a>
        <a href="logout.php" class="nav-item nav-logout"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
    </nav>
</aside>