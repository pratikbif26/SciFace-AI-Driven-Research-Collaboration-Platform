<?php
// login.php

// FIXED: Add the JSON header
header('Content-Type: application/json');

// FIXED: Use __DIR__ for a reliable include path
include __DIR__ . '/db.php';

$data = json_decode(file_get_contents('php://input'), true);

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Email and password are required.']);
    exit;
}

// FIXED: Added try...catch block for this query
try {
    // Find user by email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        // Password is correct!
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['full_name'] = $user['full_name'];

        echo json_encode([
            'success' => true,
            'user' => [
                'user_id' => $user['user_id'],
                'full_name' => $user['full_name'],
                'email' => $user['email']
            ]
        ]);
    } else {
        // Invalid credentials
        echo json_encode(['success' => false, 'error' => 'Invalid email or password.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}