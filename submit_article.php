<?php
// submit_article.php

// FIXED: Add the JSON header
header('Content-Type: application/json');

// FIXED: Use __DIR__ for a reliable include path
include __DIR__ . '/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized. Please log in again.']);
    http_response_code(401);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

// --- AI Novelty Score Simulation ---
$novelty_score = rand(750, 980) / 1000.0;
// --- End Simulation ---

try {
    $sql = "INSERT INTO papers (
                user_id, authors_name, paper_title, type, subject_area, 
                abstract, journal_name, publication_years, if_years, 
                authors_countries, doi, link, novelty_score
            ) VALUES (
                :user_id, :authors_name, :paper_title, :type, :subject_area, 
                :abstract, :journal_name, :publication_years, :if_years, 
                :authors_countries, :doi, :link, :novelty_score
            )";
            
    $stmt = $pdo->prepare($sql);
    
    $stmt->execute([
        ':user_id' => $user_id,
        ':authors_name' => $data['authors_name'],
        ':paper_title' => $data['paper_title'],
        ':type' => $data['type'],
        ':subject_area' => $data['subject_area'],
        ':abstract' => $data['abstract'],
        ':journal_name' => $data['journal_name'],
        ':publication_years' => $data['publication_years'],
        ':if_years' => empty($data['if_years']) ? null : $data['if_years'],
        ':authors_countries' => $data['authors_countries'],
        ':doi' => $data['doi'],
        ':link' => $data['link'],
        ':novelty_score' => $novelty_score
    ]);

    echo json_encode(['success' => true, 'post_id' => $pdo->lastInsertId()]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}