<?php
$page_title = 'Products';
require_once 'header.php';

$message = '';
$message_type = '';

// ============================================
// ADD / EDIT PRODUCT (with image upload)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $old_price = !empty($_POST['old_price']) ? (float)$_POST['old_price'] : null;
    $image_url = trim($_POST['image_url'] ?? '');
    $badge = trim($_POST['badge'] ?? '');
    $stock = (int)($_POST['stock'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    // Current image (if editing)
    $current_image = trim($_POST['current_image'] ?? '');

    // Handle image upload
    $final_image = $current_image;

    if (!empty($_FILES['image_file']['name'])) {
        $uploaded = uploadImage($_FILES['image_file'], 'products');
        if ($uploaded) {
            // Delete old image if it was local
            if (!empty($current_image) && strpos($current_image, 'uploads/') !== false) {
                deleteImage($current_image);
            }
            $final_image = $uploaded;
        } else {
            $message = 'Image upload failed. Check file type (JPG/PNG) and size (max 5MB)';
            $message_type = 'error';
        }
    } elseif (!empty($image_url)) {
        // Use URL instead
        if (!empty($current_image) && strpos($current_image, 'uploads/') !== false) {
            deleteImage($current_image);
        }
        $final_image = $image_url;
    }

    if (empty($name) || $price <= 0) {
        $message = 'Product name and price are required!';
        $message_type = 'error';
    } elseif ($message_type !== 'error') {
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE products SET name=?, category_id=?, price=?, old_price=?, image=?, badge=?, stock=?, description=? WHERE id=?");
            $stmt->bind_param("siddssisi", $name, $category_id, $price, $old_price, $final_image, $badge, $stock, $description, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO products (name, category_id, price, old_price, image, badge, stock, description) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->bind_param("siddssis", $name, $category_id, $price, $old_price, $final_image, $badge, $stock, $description);
        }

        if ($stmt->execute()) {
            $message = $id > 0 ? 'Product updated successfully!' : 'Product added successfully!';
            $message_type = 'success';
        } else {
            $message = 'Failed: ' . $stmt->error;
            $message_type = 'error';
        }
        $stmt->close();
    }
}

// ============================================
// DELETE PRODUCT (with image delete)
// ============================================
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // Get image path first
    $get = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $get->bind_param("i", $id);
    $get->execute();
    $prod_img = $get->get_result()->fetch_assoc();
    $get->close();

    // Delete image file if local
    if ($prod_img && !empty($prod_img['image']) && strpos($prod_img['image'], 'uploads/') !== false) {
        deleteImage($prod_img['image']);
    }

    // Delete product
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = 'Product deleted!';
        $message_type = 'success';
    }
    $stmt->close();
}

// ============================================
// GET EDIT PRODUCT
// ============================================
$edit_product = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_product = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// ============================================
// GET ALL PRODUCTS
// ============================================
$search = trim($_GET['q'] ?? '');
$where = '';
if (!empty($search)) {
    $s = $conn->real_escape_string($search);
    $where = "WHERE p.name LIKE '%$s%'";
}

$products = $conn->query("
    SELECT p.*, c.name AS category_name 
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    $where
    ORDER BY p.id DESC
");

// Categories for dropdown
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name");
$cats_array = [];
while ($c = $categories->fetch_assoc()) {
    $cats_array[] = $c;
}
?>

<!-- PAGE HEADER -->
<div class="page-header">
    <div>
        <h1>Product Management</h1>
        <p>Add, edit, and manage your products</p>
    </div>
    <button onclick="openProductModal()" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Product
    </button>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $message_type ?> alert-auto-hide">
    <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<!-- SEARCH -->
<div class="card" style="padding: 16px;">
    <form method="GET" style="display:flex; gap:12px;">
        <input type="text" name="q" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>" 
               class="form-control" style="flex:1;">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Search
        </button>
        <?php if ($search): ?>
        <a href="products.php" class="btn btn-secondary">
            <i class="fas fa-times"></i> Clear
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- PRODUCTS TABLE -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-box" style="color:#10b981;margin-right:8px;"></i> All Products</h3>
        <span style="font-size: 14px; color: #64748b;"><?= $products->num_rows ?> products</span>
    </div>

    <div class="table-wrap">
        <?php if ($products->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Badge</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($p = $products->fetch_assoc()): ?>
                <tr>
                    <td>
                        <img src="<?= htmlspecialchars($p['image']) ?>" 
                             alt=""
                             style="width:60px;height:60px;object-fit:cover;border-radius:10px;">
                    </td>
                    <td>
                        <div style="font-weight:700;font-size:14px;color:#1e293b;">
                            <?= htmlspecialchars($p['name']) ?>
                        </div>
                        <div style="font-size:12px;color:#94a3b8;margin-top:2px;">ID: #<?= $p['id'] ?></div>
                    </td>
                    <td><span class="badge secondary"><?= htmlspecialchars($p['category_name'] ?? '—') ?></span></td>
                    <td>
                        <div style="font-weight:800;color:#059669;font-size:15px;"><?= formatPrice($p['price']) ?></div>
                        <?php if ($p['old_price']): ?>
                        <div style="font-size:12px;color:#94a3b8;text-decoration:line-through;"><?= formatPrice($p['old_price']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($p['stock'] <= 0): ?>
                        <span class="badge danger">Out of Stock</span>
                        <?php elseif ($p['stock'] < 20): ?>
                        <span class="badge warning">Low: <?= $p['stock'] ?></span>
                        <?php else: ?>
                        <span class="badge success"><?= $p['stock'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($p['badge']): ?>
                        <span class="badge info"><?= htmlspecialchars($p['badge']) ?></span>
                        <?php else: ?>
                        <span style="color:#cbd5e1;">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <button class="btn btn-edit" onclick='editProduct(<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?delete=<?= $p['id'] ?>" 
                               class="btn btn-delete"
                               onclick="return confirmDelete('Delete this product and its image?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <p>No products found</p>
            <span>Click "Add New Product" to add your first product</span>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- PRODUCT MODAL -->
<div id="productModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#fff; border-radius:20px; max-width:700px; width:100%; max-height:90vh; overflow-y:auto;">
        <div style="padding:24px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; background:#fff; border-radius:20px 20px 0 0; z-index:10;">
            <h2 id="modalTitle" style="font-size:22px; color:#1e293b; font-weight:800;">Add Product</h2>
            <button onclick="closeModal('productModal')" style="background:none; border:none; font-size:22px; cursor:pointer; color:#94a3b8;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form method="POST" enctype="multipart/form-data" style="padding:24px;">
            <input type="hidden" name="id" id="prodId" value="0">
            <input type="hidden" name="current_image" id="prodCurrentImage" value="">

            <div class="form-group">
                <label>Product Name *</label>
                <input type="text" name="name" id="prodName" class="form-control" required placeholder="e.g. Fresh Red Apples (1kg)">
            </div>

            <div class="form-group">
                <label>Category *</label>
                <select name="category_id" id="prodCategory" class="form-control" required>
                    <option value="">Select Category</option>
                    <?php foreach ($cats_array as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Price (Rs.) *</label>
                    <input type="number" name="price" id="prodPrice" class="form-control" required min="0" step="1">
                </div>
                <div class="form-group">
                    <label>Old Price (Rs.)</label>
                    <input type="number" name="old_price" id="prodOldPrice" class="form-control" min="0" step="1" placeholder="Optional">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Stock Quantity *</label>
                    <input type="number" name="stock" id="prodStock" class="form-control" required min="0" value="0">
                </div>
                <div class="form-group">
                    <label>Badge</label>
                    <select name="badge" id="prodBadge" class="form-control">
                        <option value="">None</option>
                        <option value="Sale">Sale</option>
                        <option value="New">New</option>
                        <option value="Hot">Hot</option>
                    </select>
                </div>
            </div>

            <!-- IMAGE UPLOAD SECTION -->
            <div class="form-group" style="background:#f8fafc;padding:16px;border-radius:12px;border:2px dashed #cbd5e1;">
                <label style="color:#059669;font-size:15px;margin-bottom:10px;display:block;">
                    <i class="fas fa-image"></i> Product Image
                </label>

                <!-- Current image preview -->
                <div id="imagePreview" style="display:none;margin-bottom:12px;">
                    <img id="previewImg" src="" style="max-width:150px;border-radius:10px;border:2px solid #e2e8f0;">
                </div>

                <div style="margin-bottom:12px;">
                    <label style="font-size:13px;color:#64748b;font-weight:600;">📤 Upload from computer:</label>
                    <input type="file" name="image_file" id="prodImageFile" class="form-control" accept="image/*" 
                           style="background:#fff;padding:10px;font-size:13px;">
                </div>

                <div style="text-align:center;color:#94a3b8;font-size:12px;margin:8px 0;">— OR —</div>

                <div>
                    <label style="font-size:13px;color:#64748b;font-weight:600;">🔗 Paste image URL:</label>
                    <input type="text" name="image_url" id="prodImageUrl" class="form-control" 
                           placeholder="https://example.com/image.jpg" style="background:#fff;">
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="prodDesc" class="form-control" rows="3" placeholder="Short description..."></textarea>
            </div>

            <div style="display:flex; gap:12px; padding-top:16px; border-top:1px solid #f1f5f9;">
                <button type="submit" name="save_product" class="btn btn-primary" style="flex:1; justify-content:center;">
                    <i class="fas fa-save"></i> Save Product
                </button>
                <button type="button" onclick="closeModal('productModal')" class="btn btn-secondary">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openProductModal() {
    document.getElementById('modalTitle').textContent = 'Add Product';
    document.getElementById('prodId').value = 0;
    document.getElementById('prodName').value = '';
    document.getElementById('prodCategory').value = '';
    document.getElementById('prodPrice').value = '';
    document.getElementById('prodOldPrice').value = '';
    document.getElementById('prodStock').value = 0;
    document.getElementById('prodBadge').value = '';
    document.getElementById('prodImageUrl').value = '';
    document.getElementById('prodCurrentImage').value = '';
    document.getElementById('prodImageFile').value = '';
    document.getElementById('prodDesc').value = '';
    document.getElementById('imagePreview').style.display = 'none';
    openModal('productModal');
}

function editProduct(product) {
    document.getElementById('modalTitle').textContent = 'Edit Product';
    document.getElementById('prodId').value = product.id;
    document.getElementById('prodName').value = product.name;
    document.getElementById('prodCategory').value = product.category_id;
    document.getElementById('prodPrice').value = product.price;
    document.getElementById('prodOldPrice').value = product.old_price || '';
    document.getElementById('prodStock').value = product.stock;
    document.getElementById('prodBadge').value = product.badge || '';
    document.getElementById('prodDesc').value = product.description || '';
    document.getElementById('prodCurrentImage').value = product.image || '';
    document.getElementById('prodImageUrl').value = '';
    document.getElementById('prodImageFile').value = '';

    // Show preview
    if (product.image) {
        document.getElementById('previewImg').src = product.image;
        document.getElementById('imagePreview').style.display = 'block';
    } else {
        document.getElementById('imagePreview').style.display = 'none';
    }

    openModal('productModal');
}
</script>

<?php require_once 'footer.php'; ?>