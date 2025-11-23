<?php
// src/config/auth.php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validation
if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    exit();
}

try {
    // Fetch user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 'Active' LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    // Verify credentials
    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        exit();
    }
    
    // Create session
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['fullname'] = $user['fullname'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['login_time'] = time();
    
    // Redirect based on role
    $redirect = ($user['role'] === 'Admin') ? 'dashboard.php' : 'pos.php';
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'redirect' => $redirect
    ]);
    
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?>