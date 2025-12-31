<?php
// search.php

// FIXED: Add the JSON header
header('Content-Type: application/json');

// FIXED: Use __DIR__ for a reliable include path
include __DIR__ . '/db.php';

try {
    // Base query
    $sql = "SELECT p.*, u.full_name, u.user_id AS author_id 
            FROM papers p 
            JOIN users u ON p.user_id = u.user_id 
            WHERE 1=1";
            
    $params = [];

    // Add filters from GET parameters
    if (!empty($_GET['type'])) {
        $sql .= " AND p.type = ?";
        $params[] = $_GET['type'];
    }
    if (!empty($_GET['subject'])) {
        $sql .= " AND p.subject_area = ?";
        $params[] = $_GET['subject'];
    }
    if (!empty($_GET['country'])) {
        $sql .= " AND p.authors_countries LIKE ?";
        $params[] = '%' . $_GET['country'] . '%';
    }
    if (!empty($_GET['author'])) {
        $sql .= " AND p.authors_name LIKE ?";
        $params[] = '%' . $_GET['author'] . '%';
    }
    if (!empty($_GET['title'])) {
        $sql .= " AND p.paper_title LIKE ?";
        $params[] = '%' . $_GET['title'] . '%';
    }
    if (!empty($_GET['journal'])) {
        $sql .= " AND p.journal_name LIKE ?";
        $params[] = '%' . $_GET['journal'] . '%';
    }

    $sql .= " ORDER BY p.publication_years DESC, p.novelty_score DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $final_results = [];
    foreach ($results as $row) {
        // --- AI Collaborator Simulation ---
        $collab_stmt = $pdo->prepare(
            "SELECT user_id, full_name FROM users WHERE user_id != ? ORDER BY RAND() LIMIT 2"
        );
        $collab_stmt->execute([$row['author_id']]);
        $collaborators = $collab_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $row['suggested_collaborators'] = array_map(function($c) {
            return ['id' => $c['user_id'], 'name' => $c['full_name']];
        }, $collaborators);
        
        $row['post_id'] = $row['paper_id'];
        $row['authors_name'] = $row['full_name']; 
        
        $final_results[] = $row;
    }

    echo json_encode($final_results);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}