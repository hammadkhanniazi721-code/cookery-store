<?php
$page_title = 'Profit / Loss';
require_once 'header.php';

// Calculations (Using PDO Wrapper Safely)

// 1. Total Sales
$sales_query = $conn->query("SELECT SUM(total) AS t FROM orders WHERE status = 'completed'");
$sales_data = $sales_query->fetch_assoc();
$sales = $sales_data['t'] ?? 0;

// 2. Total Expenses
$expenses_query = $conn->query("SELECT SUM(amount) AS t FROM expenses");
$expenses_data = $expenses_query->fetch_assoc();
$expenses = $expenses_data['t'] ?? 0;

// 3. Total Breakage
$breakage_query = $conn->query("SELECT SUM(cost_loss) AS t FROM breakage");
$breakage_data = $breakage_query->fetch_assoc();
$breakage = $breakage_data['t'] ?? 0;

// 4. Total Salaries
$staff_query = $conn->query("SELECT SUM(salary) AS t FROM staff");
$staff_data = $staff_query->fetch_assoc();
$salaries = $staff_data['t'] ?? 0;

// Final Calculations
$total_out = $expenses + $breakage + $salaries;
$net_profit = $sales - $total_out;
?>

<div class="page-header">
    <div>
        <h1>Profit & Loss Statement</h1>
        <p>Overall financial summary of your store</p>
    </div>
    <button onclick="window.print()" class="btn btn-secondary"><i class="fas fa-print"></i> Print Report</button>
</div>

<div class="stats-grid">
    <!-- Total Sales -->
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-arrow-up"></i></div>
        <div class="stat-info">
            <h3>Rs. <?= number_format($sales, 0) ?></h3>
            <p>Total Sales (Revenue)</p>
        </div>
    </div>
    
    <!-- Total Expenses -->
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-arrow-down"></i></div>
        <div class="stat-info">
            <h3>Rs. <?= number_format($total_out, 0) ?></h3>
            <p>Total Expenses (Out)</p>
        </div>
    </div>

    <!-- Net Profit / Loss -->
    <div class="stat-card">
        <div class="stat-icon <?= $net_profit >= 0 ? 'green' : 'red' ?>">
            <i class="fas fa-wallet"></i>
        </div>
        <div class="stat-info">
            <h3 style="color: <?= $net_profit >= 0 ? '#3b82f6' : '#dc2626' ?>;">
                Rs. <?= number_format($net_profit, 0) ?>
            </h3>
            <p>Net <?= $net_profit >= 0 ? 'Profit' : 'Loss' ?></p>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">
    <div class="card">
        <div class="card-header"><h3>Expense Breakdown</h3></div>
        <table>
            <tr>
                <td>Store Expenses (Bills, Rent)</td>
                <td style="text-align:right;">Rs. <?= number_format($expenses, 0) ?></td>
            </tr>
            <tr>
                <td>Staff Salaries</td>
                <td style="text-align:right;">Rs. <?= number_format($salaries, 0) ?></td>
            </tr>
            <tr>
                <td>Breakage Loss</td>
                <td style="text-align:right;">Rs. <?= number_format($breakage, 0) ?></td>
            </tr>
            <tr style="border-top:2px solid #e2e8f0;">
                <td><strong>Total Out</strong></td>
                <td style="text-align:right;"><strong>Rs. <?= number_format($total_out, 0) ?></strong></td>
            </tr>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>