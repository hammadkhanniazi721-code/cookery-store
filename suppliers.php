<?php
$page_title = 'Suppliers';
require_once 'header.php';

$message = '';

// Add Supplier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_supplier'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    $stmt = $conn->prepare("INSERT INTO suppliers (name, phone, address) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $phone, $address);
    $stmt->execute();
    $stmt->close();
    $message = "Supplier added!";
}

// Delete Supplier
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM suppliers WHERE id = $id");
    $message = "Supplier deleted!";
}

$suppliers = $conn->query("SELECT * FROM suppliers ORDER BY id DESC");
?>

<div class="page-header">
    <div>
        <h1>Suppliers</h1>
        <p>Manage your wholesale suppliers</p>
    </div>
    <button onclick="document.getElementById('addModal').style.display='flex'" class="btn btn-primary"><i class="fas fa-plus"></i> Add Supplier</button>
</div>

<?php if ($message): ?>
<div class="alert alert-success alert-auto-hide"><?= $message ?></div>
<?php endif; ?>

<div class="card">
    <table>
        <thead>
            <tr><th>ID</th><th>Name</th><th>Phone</th><th>Address</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php while ($s = $suppliers->fetch_assoc()): ?>
            <tr>
                <td><?= $s['id'] ?></td>
                <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                <td><?= htmlspecialchars($s['phone']) ?></td>
                <td><?= htmlspecialchars($s['address']) ?></td>
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
            <h2 style="font-size:20px;">Add Supplier</h2>
            <i class="fas fa-times" style="cursor:pointer;" onclick="document.getElementById('addModal').style.display='none'"></i>
        </div>
        <form method="POST">
            <div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" required></div>
            <div class="form-group"><label>Phone</label><input type="text" name="phone" class="form-control"></div>
            <div class="form-group"><label>Address</label><textarea name="address" class="form-control" rows="2"></textarea></div>
            <button type="submit" name="add_supplier" class="btn btn-primary btn-full">Save</button>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>