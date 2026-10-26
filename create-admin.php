<?php
require_once 'db.php';

// Naya admin
$name = 'Super Admin';
$email = 'admin@cookerystore.com';
$password = 'admin123';
$role = 'super_admin';

// Password hash banao
$hash = password_hash($password, PASSWORD_DEFAULT);

// Pehle purana delete karo
$conn->query("DELETE FROM admins WHERE email = 'admin@cookerystore.com'");

// Naya insert karo
$stmt = $conn->prepare("INSERT INTO admins (name, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $hash, $role);

if ($stmt->execute()) {
    echo "<div style='font-family:sans-serif;padding:40px;max-width:600px;margin:50px auto;background:#ecfdf5;border:2px solid #10b981;border-radius:16px;'>";
    echo "<h1 style='color:#059669;'>✅ Admin Ban Gaya!</h1>";
    echo "<p style='font-size:18px;'><strong>Email:</strong> admin@cookerystore.com</p>";
    echo "<p style='font-size:18px;'><strong>Password:</strong> admin123</p>";
    echo "<p style='font-size:12px;color:#64748b;margin-top:20px;'>Hash: " . htmlspecialchars($hash) . "</p>";
    echo "<br><a href='admin/login.php' style='display:inline-block;padding:14px 28px;background:#10b981;color:#fff;text-decoration:none;border-radius:10px;font-weight:700;'>🔐 Login Karo</a>";
    echo "</div>";
} else {
    echo "❌ Error: " . $stmt->error;
}
$stmt->close();
?>