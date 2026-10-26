<?php
require_once '../db.php';

// Agar already logged in hai to dashboard pe bhejo
if (isAdminLoggedIn()) {
    redirect('index.php');
}

$error = '';

// Form submit hua?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password';
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();

            if (password_verify($password, $admin['password'])) {
                // Login successful
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role'] = $admin['role'];

                redirect('index.php');
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'Email not found';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - Cookery Store</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',Tahoma,sans-serif; }

body {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%);
    padding: 20px;
}

.login-box {
    background: #fff;
    padding: 50px 45px;
    border-radius: 24px;
    width: 100%;
    max-width: 440px;
    box-shadow: 0 30px 80px rgba(0,0,0,0.3);
    animation: slideUp 0.5s ease;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.login-icon {
    width: 90px;
    height: 90px;
    margin: 0 auto 25px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 38px;
    box-shadow: 0 12px 30px rgba(16,185,129,0.35);
}

.login-box h1 {
    text-align: center;
    font-size: 28px;
    color: #1e293b;
    margin-bottom: 8px;
    font-weight: 800;
}

.login-box .subtitle {
    text-align: center;
    color: #94a3b8;
    font-size: 15px;
    margin-bottom: 35px;
}

.error-box {
    background: #fee2e2;
    color: #dc2626;
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 20px;
    font-size: 14px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    border-left: 4px solid #dc2626;
}

.input-group {
    position: relative;
    margin-bottom: 18px;
}

.input-group i {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 17px;
}

.input-group input {
    width: 100%;
    padding: 16px 20px 16px 52px;
    border: 2px solid #f1f5f9;
    border-radius: 14px;
    font-size: 15px;
    outline: none;
    transition: 0.3s;
    background: #f8fafc;
    font-family: inherit;
}

.input-group input:focus {
    border-color: #10b981;
    background: #fff;
    box-shadow: 0 0 0 4px rgba(16,185,129,0.1);
}

.btn-login {
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    border: none;
    border-radius: 14px;
    font-size: 16px;
    font-weight: 800;
    cursor: pointer;
    transition: 0.3s;
    font-family: inherit;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-top: 8px;
}

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(16,185,129,0.4);
}

.info-box {
    background: #ecfdf5;
    border: 1px solid #d1fae5;
    border-radius: 12px;
    padding: 16px 18px;
    margin-top: 25px;
    font-size: 13px;
    color: #065f46;
    line-height: 1.7;
}

.info-box strong { color: #059669; }

.back-link {
    text-align: center;
    margin-top: 20px;
    font-size: 14px;
}

.back-link a {
    color: #64748b;
    text-decoration: none;
    transition: 0.3s;
}

.back-link a:hover { color: #10b981; }

@media (max-width: 480px) {
    .login-box { padding: 35px 25px; }
    .login-box h1 { font-size: 24px; }
    .login-icon { width: 75px; height: 75px; font-size: 32px; }
}
</style>
</head>
<body>
    <div class="login-box">
        <div class="login-icon">
            <i class="fas fa-user-shield"></i>
        </div>
        <h1>Admin Login</h1>
        <p class="subtitle">Sign in to access dashboard</p>

        <?php if (!empty($error)): ?>
        <div class="error-box">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Email Address" required autofocus>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>

        <div class="info-box">
            <strong>Default Login:</strong><br>
            Email: admin@cookerystore.com<br>
            Password: admin123
        </div>

        <div class="back-link">
            <a href="../index.html">
                <i class="fas fa-arrow-left"></i> Back to Store
            </a>
        </div>
    </div>
</body>
</html>