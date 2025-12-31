<?php
// quick_post.php

// FIXED: Add the JSON header
header('Content-Type: application/json');

// FIXED: Use __DIR__ for a reliable include path
include __DIR__ . '/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    http_response_code(401);
    exit;
}

$current_user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$content = $data['content'] ?? '';

if (empty($content)) {
    echo json_encode(['success' => false, 'error' => 'Post content cannot be empty.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO quick_posts (user_id, content) VALUES (?, ?)");
    $stmt->execute([$current_user_id, $content]);
    
    // FIXED: Return the new post ID
    echo json_encode(['success' => true, 'post_id' => $pdo->lastInsertId()]);

} catch (PDOException $e) {
     echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}