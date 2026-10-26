<?php
$page_title = 'Site Settings';
require_once 'header.php';

$message = '';
$message_type = '';

// ============================================
// SAVE SETTINGS
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {

    // Text settings
    $text_keys = [
        'site_name', 'site_tagline',
        'hero_title', 'hero_subtitle',
        'footer_about', 'contact_phone', 'contact_email',
        'contact_address', 'free_delivery_min', 'delivery_fee'
    ];

    foreach ($text_keys as $key) {
        if (isset($_POST[$key])) {
            $val = trim($_POST[$key]);
            // updateSetting() function db.php me pehle se hai jo PDO use karta hai
            updateSetting($key, $val); 
        }
    }

    // Image settings (with file upload)
    $image_keys = [
        'logo_image' => 'branding',
        'hero_image' => 'hero',
        'promo_image_1' => 'promo',
        'promo_image_2' => 'promo',
        'about_image' => 'about'
    ];

    foreach ($image_keys as $key => $folder) {
        // Check file upload
        if (!empty($_FILES[$key . '_file']['name'])) {
            $uploaded = uploadImage($_FILES[$key . '_file'], $folder);
            if ($uploaded) {
                // Get old image using getSetting()
                $old = getSetting($key);
                if (!empty($old) && strpos($old, 'uploads/') !== false) {
                    deleteImage($old);
                }
                updateSetting($key, $uploaded);
            }
        }
        // Check URL input
        elseif (isset($_POST[$key . '_url']) && !empty($_POST[$key . '_url'])) {
            $url = trim($_POST[$key . '_url']);
            $old = getSetting($key);
            if (!empty($old) && strpos($old, 'uploads/') !== false) {
                deleteImage($old);
            }
            updateSetting($key, $url);
        }
        // Check remove
        elseif (isset($_POST['remove_' . $key]) && $_POST['remove_' . $key] == '1') {
            $old = getSetting($key);
            if (!empty($old) && strpos($old, 'uploads/') !== false) {
                deleteImage($old);
            }
            updateSetting($key, '');
        }
    }

    $message = 'Settings saved successfully!';
    $message_type = 'success';
}

// ============================================
// GET ALL SETTINGS
// ============================================
// getAllSettings() function db.php me pehle se hai
$settings = getAllSettings();
?>

<!-- PAGE HEADER -->
<div class="page-header">
    <div>
        <h1>Site Settings</h1>
        <p>Manage your website logo, images, and content</p>
    </div>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $message_type ?> alert-auto-hide">
    <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

    <!-- ============ GENERAL SETTINGS ============ -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-cog" style="color:#10b981;margin-right:8px;"></i> General Settings</h3>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Site Name</label>
                <input type="text" name="site_name" class="form-control" value="<?= htmlspecialchars(getSetting('site_name', 'Cookery Store')) ?>" placeholder="e.g. Cookery Store">
            </div>
            <div class="form-group">
                <label>Tagline</label>
                <input type="text" name="site_tagline" class="form-control" value="<?= htmlspecialchars(getSetting('site_tagline', 'Grocery Store')) ?>" placeholder="e.g. Grocery Store">
            </div>
        </div>
    </div>

    <!-- ============ LOGO IMAGE ============ -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-image" style="color:#10b981;margin-right:8px;"></i> Logo Image</h3>
        </div>

        <div style="display:flex;gap:20px;align-items:center;flex-wrap:wrap;">
            <?php if (getSetting('logo_image')): ?>
            <div style="text-align:center;">
                <img src="../<?= htmlspecialchars(strpos(getSetting('logo_image'), 'http') === 0 ? getSetting('logo_image') : getSetting('logo_image')) ?>" 
                     style="max-width:150px;max-height:80px;border-radius:10px;border:2px solid #e2e8f0;padding:8px;background:#f8fafc;"
                     onerror="this.style.display='none'">
                <div style="font-size:12px;color:#64748b;margin-top:6px;">Current Logo</div>
                <label style="display:inline-block;margin-top:8px;font-size:12px;color:#dc2626;cursor:pointer;">
                    <input type="checkbox" name="remove_logo_image" value="1"> Remove
                </label>
            </div>
            <?php endif; ?>

            <div style="flex:1;min-width:280px;">
                <div class="form-group">
                    <label>📤 Upload Logo</label>
                    <input type="file" name="logo_image_file" class="form-control" accept="image/*">
                </div>
                <div style="text-align:center;color:#94a3b8;font-size:12px;margin:6px 0;">— OR —</div>
                <div class="form-group">
                    <label>🔗 Logo URL</label>
                    <input type="text" name="logo_image_url" class="form-control" placeholder="https://example.com/logo.png">
                </div>
            </div>
        </div>
    </div>

    <!-- ============ HERO IMAGE ============ -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-image" style="color:#10b981;margin-right:8px;"></i> Hero Banner Image</h3>
        </div>

        <div style="display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap;">
            <?php if (getSetting('hero_image')): ?>
            <div style="text-align:center;">
                <img src="<?= htmlspecialchars(getSetting('hero_image')) ?>" 
                     style="max-width:200px;max-height:120px;border-radius:10px;border:2px solid #e2e8f0;">
                <div style="font-size:12px;color:#64748b;margin-top:6px;">Current Hero</div>
                <label style="display:inline-block;margin-top:8px;font-size:12px;color:#dc2626;cursor:pointer;">
                    <input type="checkbox" name="remove_hero_image" value="1"> Remove
                </label>
            </div>
            <?php endif; ?>

            <div style="flex:1;min-width:280px;">
                <div class="form-group">
                    <label>📤 Upload Hero Image</label>
                    <input type="file" name="hero_image_file" class="form-control" accept="image/*">
                </div>
                <div style="text-align:center;color:#94a3b8;font-size:12px;margin:6px 0;">— OR —</div>
                <div class="form-group">
                    <label>🔗 Hero URL</label>
                    <input type="text" name="hero_image_url" class="form-control" placeholder="https://example.com/hero.jpg">
                </div>
            </div>
        </div>
    </div>

    <!-- ============ HERO TEXT ============ -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-heading" style="color:#10b981;margin-right:8px;"></i> Hero Text</h3>
        </div>

        <div class="form-group">
            <label>Hero Title</label>
            <input type="text" name="hero_title" class="form-control" value="<?= htmlspecialchars(getSetting('hero_title')) ?>">
        </div>
        <div class="form-group">
            <label>Hero Subtitle</label>
            <textarea name="hero_subtitle" class="form-control" rows="2"><?= htmlspecialchars(getSetting('hero_subtitle')) ?></textarea>
        </div>
    </div>

    <!-- ============ CONTACT INFO ============ -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-phone" style="color:#10b981;margin-right:8px;"></i> Contact Info</h3>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="contact_phone" class="form-control" value="<?= htmlspecialchars(getSetting('contact_phone')) ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="contact_email" class="form-control" value="<?= htmlspecialchars(getSetting('contact_email')) ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Address</label>
            <input type="text" name="contact_address" class="form-control" value="<?= htmlspecialchars(getSetting('contact_address')) ?>">
        </div>
        <div class="form-group">
            <label>Footer About Text</label>
            <textarea name="footer_about" class="form-control" rows="2"><?= htmlspecialchars(getSetting('footer_about')) ?></textarea>
        </div>
    </div>

    <!-- ============ DELIVERY ============ -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-truck" style="color:#10b981;margin-right:8px;"></i> Delivery Settings</h3>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Free Delivery Minimum (Rs.)</label>
                <input type="number" name="free_delivery_min" class="form-control" value="<?= htmlspecialchars(getSetting('free_delivery_min', '1500')) ?>">
            </div>
            <div class="form-group">
                <label>Delivery Fee (Rs.)</label>
                <input type="number" name="delivery_fee" class="form-control" value="<?= htmlspecialchars(getSetting('delivery_fee', '150')) ?>">
            </div>
        </div>
    </div>

    <!-- ============ SAVE BUTTON ============ -->
    <div class="card" style="position:sticky;bottom:20px;background:#1e293b;color:#fff;">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <div style="font-size:14px;color:#cbd5e1;">
                <i class="fas fa-info-circle"></i> Changes save karne ke liye "Save Settings" click karo
            </div>
            <button type="submit" name="save_settings" class="btn btn-primary" style="padding:14px 40px;font-size:16px;">
                <i class="fas fa-save"></i> Save Settings
            </button>
        </div>
    </div>

</form>

<?php require_once 'footer.php'; ?>