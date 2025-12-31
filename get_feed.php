<?php
// get_feed.php

// FIXED: Add the JSON header
header('Content-Type: application/json');

// FIXED: Use __DIR__ for a reliable include path
include __DIR__ . '/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]); // Return empty array if not logged in
    exit;
}

$current_user_id = $_SESSION['user_id'];
$tab = $_GET['tab'] ?? 'following';

try {
    $sql = "";
    $params = [];

    if ($tab === 'following') {
        $sql = "SELECT p.*, u.full_name, u.user_id AS author_id
                FROM papers p
                JOIN users u ON p.user_id = u.user_id
                WHERE p.user_id IN (SELECT following_id FROM followers WHERE follower_id = ?)
                ORDER BY p.created_at DESC";
        $params[] = $current_user_id;
    } else {
        // Trending feed: Top 10 most novel papers from the last 30 days
        $sql = "SELECT p.*, u.full_name, u.user_id AS author_id
                FROM papers p
                JOIN users u ON p.user_id = u.user_id
                WHERE p.created_at >= NOW() - INTERVAL 30 DAY
                ORDER BY p.novelty_score DESC, p.created_at DESC
                LIMIT 10";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $final_results = [];
    foreach ($results as $row) {
        // --- AI Collaborator Simulation ---
        $collab_stmt = $pdo->prepare(
            "SELECT user_id, full_name FROM users WHERE user_id != ? AND user_id != ? ORDER BY RAND() LIMIT 2"
        );
        $collab_stmt->execute([$row['author_id'], $current_user_id]);
        $collaborators = $collab_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $row['suggested_collaborators'] = array_map(function($c) {
            return ['id' => $c['user_id'], 'name' => $c['full_name']];
        }, $collaborators);
        
        $row['post_id'] = $row['paper_id'];
        $row['authors_name'] = $row['full_name']; // Match mock data structure
        
        $final_results[] = $row;
    }

    echo json_encode($final_results);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}