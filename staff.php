<?php
$page_title = 'Staff';
require_once 'header.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_staff'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $role = trim($_POST['role']);
    $salary = (float)$_POST['salary'];
    
    $stmt = $conn->prepare("INSERT INTO staff (name, phone, role, salary) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssd", $name, $phone, $role, $salary);
    $stmt->execute();
    $stmt->close();
    $message = "Staff added!";
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM staff WHERE id = $id");
    $message = "Staff deleted!";
}

$staff = $conn->query("SELECT * FROM staff ORDER BY id DESC");
?>

<div class="page-header">
    <div>
        <h1>Staff Management</h1>
        <p>Manage your employees and their salaries</p>
    </div>
    <button onclick="document.getElementById('addModal').style.display='flex'" class="btn btn-primary"><i class="fas fa-plus"></i> Add Staff</button>
</div>

<?php if ($message): ?>
<div class="alert alert-success alert-auto-hide"><?= $message ?></div>
<?php endif; ?>

<div class="card">
    <table>
        <thead>
            <tr><th>Name</th><th>Phone</th><th>Role</th><th>Salary</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php while ($s = $staff->fetch_assoc()): ?>
            <tr>
                <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                <td><?= htmlspecialchars($s['phone']) ?></td>
                <td><span class="badge info"><?= htmlspecialchars($s['role']) ?></span></td>
                <td>Rs. <?= number_format($s['salary'], 0) ?></td>
                <td><a href="?delete=<?= $s['id'] ?>" class="btn btn-delete" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#fff; border-radius:20px; max-width:500px; width:100%; padding:24px;">
        <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
            <h2 style="font-size:20px;">Add Staff Member</h2>
            <i class="fas fa-times" style="cursor:pointer;" onclick="document.getElementById('addModal').style.display='none'"></i>
        </div>
        <form method="POST">
            <div class="form-group"><label>Full Name</label><input type="text" name="name" class="form-control" required></div>
            <div class="form-group"><label>Phone</label><input type="text" name="phone" class="form-control"></div>
            <div class="form-group"><label>Role (e.g. Manager, Cashier)</label><input type="text" name="role" class="form-control" required></div>
            <div class="form-group"><label>Salary (Rs.)</label><input type="number" name="salary" class="form-control" step="1" required></div>
            <button type="submit" name="add_staff" class="btn btn-primary btn-full">Save</button>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>