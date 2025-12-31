<?php
// register.php

// FIXED: Added session_start() to enable session variables
session_start();

// FIXED: Add the JSON header
header('Content-Type: application/json');

// FIXED: Use __DIR__ for a reliable include path
include __DIR__ . '/db.php';

$data = json_decode(file_get_contents('php://input'), true);

$full_name = $data['full_name'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$research_field = $data['research_field'] ?? '';
$institution = $data['institution'] ?? '';

if (empty($full_name) || empty($email) || empty($password) || empty($research_field) || empty($institution)) {
    echo json_encode(['success' => false, 'error' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email format.']);
    exit;
}

// FIXED: Added try...catch block for this query
try {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Email already registered.']);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database check failed: ' . $e->getMessage()]);
    exit;
}


// Hash password
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// Insert new user
try {
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, research_field, institution, bio) VALUES (?, ?, ?, ?, ?, ?)");
    // Using an empty string for bio as a default
    $stmt->execute([$full_name, $email, $password_hash, $research_field, $institution, '']); 
    
    $user_id = $pdo->lastInsertId();

    // Log the user in by setting session variables
    $_SESSION['user_id'] = $user_id;
    $_SESSION['full_name'] = $full_name;

    echo json_encode([
        'success' => true,
        'user' => [
            'user_id' => $user_id,
            'full_name' => $full_name,
            'email' => $email
        ]
    ]);

} catch (PDOException $e) {
    // Handle potential duplicate email error during insert, just in case
    if ($e->errorInfo[1] == 1062) {
         echo json_encode(['success' => false, 'error' => 'This email address is already registered.']);
    } else {
         echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}