<?php
// ============================================
// CUSTOMER REGISTER API
// ============================================

session_start();
header('Content-Type: application/json');

require_once '../db.php';

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Get input (JSON or form)
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$password = $input['password'] ?? '';
$city = trim($input['city'] ?? '');

// Validation
if (empty($name) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Name, email and password are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    $stmt->close();
    exit;
}
$stmt->close();

// Hash password
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Insert user
$stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, city) VALUES (?,?,?,?,?)");
$stmt->bind_param("sssss", $name, $email, $phone, $hashed, $city);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id;
    $stmt->close();

    // Auto login
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;

    echo json_encode([
        'success' => true,
        'message' => 'Registration successful!',
        'user' => [
            'id' => $user_id,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'city' => $city
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Registration failed. Try again.']);
    $stmt->close();
}
?>