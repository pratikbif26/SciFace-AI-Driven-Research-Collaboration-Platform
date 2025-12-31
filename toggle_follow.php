<?php
// toggle_follow.php

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
$user_to_follow_id = $data['user_id'] ?? 0;

if ($current_user_id == $user_to_follow_id) {
     echo json_encode(['success' => false, 'error' => 'You cannot follow yourself.']);
     exit;
}

// FIXED: Added check for valid user ID
if ($user_to_follow_id == 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid user ID.']);
    exit;
}

try {
    // Check if already following
    $stmt = $pdo->prepare("SELECT follow_id FROM followers WHERE follower_id = ? AND following_id = ?");
    $stmt->execute([$current_user_id, $user_to_follow_id]);
    $follow_entry = $stmt->fetch();

    if ($follow_entry) {
        // Already following -> Unfollow
        $stmt = $pdo->prepare("DELETE FROM followers WHERE follow_id = ?");
        $stmt->execute([$follow_entry['follow_id']]);
        echo json_encode(['success' => true, 'is_following' => false]);
    } else {
        // Not following -> Follow
        $stmt = $pdo->prepare("INSERT INTO followers (follower_id, following_id) VALUES (?, ?)");
        $stmt->execute([$current_user_id, $user_to_follow_id]);
        echo json_encode(['success' => true, 'is_following' => true]);
    }

} catch (PDOException $e) {
     echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}