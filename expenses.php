<?php
$page_title = 'Expenses';
require_once 'header.php';

$message = '';

// Add Expense
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_expense'])) {
    $title = trim($_POST['title']);
    $amount = (float)$_POST['amount'];
    $date = $_POST['expense_date'];
    $notes = trim($_POST['notes']);
    
    $stmt = $conn->prepare("INSERT INTO expenses (title, amount, expense_date, notes) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdss", $title, $amount, $date, $notes);
    $stmt->execute();
    $stmt->close();
    $message = "Expense added!";
}

// Delete Expense
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM expenses WHERE id = $id");
    $message = "Expense deleted!";
}

$expenses = $conn->query("SELECT * FROM expenses ORDER BY expense_date DESC");
$total = $conn->query("SELECT SUM(amount) AS t FROM expenses")->fetch_assoc()['t'] ?? 0;
?>

<div class="page-header">
    <div>
        <h1>Expenses</h1>
        <p>Track your store expenses (Rent, Bills, etc.)</p>
    </div>
    <button onclick="document.getElementById('addModal').style.display='flex'" class="btn btn-primary"><i class="fas fa-plus"></i> Add Expense</button>
</div>

<?php if ($message): ?>
<div class="alert alert-success alert-auto-hide"><?= $message ?></div>
<?php endif; ?>

<div class="stats-grid" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-money-bill-wave"></i></div>
        <div class="stat-info">
            <h3>Rs. <?= number_format($total, 0) ?></h3>
            <p>Total Expenses</p>
        </div>
    </div>
</div>

<div class="card">
    <table>
        <thead>
            <tr><th>Date</th><th>Title</th><th>Amount</th><th>Notes</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php while ($e = $expenses->fetch_assoc()): ?>
            <tr>
                <td><?= date('d M Y', strtotime($e['expense_date'])) ?></td>
                <td><strong><?= htmlspecialchars($e['title']) ?></strong></td>
                <td style="color:#dc2626; font-weight:bold;">Rs. <?= number_format($e['amount'], 0) ?></td>
                <td><?= htmlspecialchars($e['notes']) ?></td>
                <td><a href="?delete=<?= $e['id'] ?>" class="btn btn-delete" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#fff; border-radius:20px; max-width:500px; width:100%; padding:24px;">
        <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
            <h2 style="font-size:20px;">Add Expense</h2>
            <i class="fas fa-times" style="cursor:pointer;" onclick="document.getElementById('addModal').style.display='none'"></i>
        </div>
        <form method="POST">
            <div class="form-group"><label>Title (e.g., Electricity Bill)</label><input type="text" name="title" class="form-control" required></div>
            <div class="form-group"><label>Amount (Rs.)</label><input type="number" name="amount" class="form-control" required step="1"></div>
            <div class="form-group"><label>Date</label><input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
            <div class="form-group"><label>Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
            <button type="submit" name="add_expense" class="btn btn-primary btn-full">Save Expense</button>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>