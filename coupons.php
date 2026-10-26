<?php
$page_title = 'Coupons';
require_once 'header.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_coupon'])) {
    $code = strtoupper(trim($_POST['code']));
    $discount = (int)$_POST['discount_percent'];
    
    $stmt = $conn->prepare("INSERT INTO coupons (code, discount_percent) VALUES (?, ?)");
    $stmt->bind_param("si", $code, $discount);
    if ($stmt->execute()) {
        $message = "Coupon added!";
    } else {
        $message = "Coupon code already exists!";
    }
    $stmt->close();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM coupons WHERE id = $id");
    $message = "Coupon deleted!";
}

$coupons = $conn->query("SELECT * FROM coupons ORDER BY id DESC");
?>

<div class="page-header">
    <div>
        <h1>Discount Coupons</h1>
        <p>Create codes like SAVE10 for customers</p>
    </div>
    <button onclick="document.getElementById('addModal').style.display='flex'" class="btn btn-primary"><i class="fas fa-plus"></i> Create Coupon</button>
</div>

<?php if ($message): ?>
<div class="alert alert-success alert-auto-hide"><?= $message ?></div>
<?php endif; ?>

<div class="card">
    <table>
        <thead>
            <tr><th>Code</th><th>Discount</th><th>Status</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php while ($c = $coupons->fetch_assoc()): ?>
            <tr>
                <td><strong style="font-size:16px; color:#3b82f6;"><?= htmlspecialchars($c['code']) ?></strong></td>
                <td><?= $c['discount_percent'] ?>% OFF</td>
                <td><span class="badge success"><?= ucfirst($c['status']) ?></span></td>
                <td><a href="?delete=<?= $c['id'] ?>" class="btn btn-delete" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#fff; border-radius:20px; max-width:500px; width:100%; padding:24px;">
        <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
            <h2 style="font-size:20px;">Create Coupon</h2>
            <i class="fas fa-times" style="cursor:pointer;" onclick="document.getElementById('addModal').style.display='none'"></i>
        </div>
        <form method="POST">
            <div class="form-group"><label>Coupon Code</label><input type="text" name="code" class="form-control" placeholder="e.g. EID20" required></div>
            <div class="form-group"><label>Discount (%)</label><input type="number" name="discount_percent" class="form-control" min="1" max="100" required></div>
            <button type="submit" name="add_coupon" class="btn btn-primary btn-full">Save</button>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>