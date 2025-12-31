<?php
// get_profile.php

// FIXED: Add the JSON header
header('Content-Type: application/json');

// FIXED: Use __DIR__ for a reliable include path
include __DIR__ . '/db.php';

// The ID of the profile we are viewing
$profile_user_id = $_GET['user'] ?? 0;

// The ID of the person *currently* logged in
$current_user_id = $_SESSION['user_id'] ?? 0;

if ($profile_user_id == 0) {
    // UPDATED: If no user is specified, show the logged-in user's profile
    if ($current_user_id != 0) {
        $profile_user_id = $current_user_id;
    } else {
        echo json_encode(['success' => false, 'error' => 'No user specified and not logged in.']);
        exit;
    }
}

try {
    // 1. Get User Info
    $stmt = $pdo->prepare("SELECT user_id, full_name, bio, research_field, institution FROM users WHERE user_id = ?");
    $stmt->execute([$profile_user_id]);
    $user_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_info) {
        echo json_encode(['success' => false, 'error' => 'User not found.']);
        exit;
    }

    // 2. Check follow status
    // UPDATED: Don't show follow button for self
    $is_followed_by_me = false;
    if ($current_user_id != 0 && $current_user_id != $profile_user_id) {
        $stmt_follow = $pdo->prepare("SELECT COUNT(*) FROM followers WHERE follower_id = ? AND following_id = ?");
        $stmt_follow->execute([$current_user_id, $profile_user_id]);
        $is_followed_by_me = $stmt_follow->fetchColumn() > 0;
    }
    
    $user_info['is_followed_by_me'] = $is_followed_by_me;
    // Add a flag to know if this is the user's own profile
    $user_info['is_own_profile'] = ($current_user_id == $profile_user_id);


    // 3. Get User Posts (Papers)
    // UPDATED: Selected more fields to match createPostCard function
    $stmt_papers = $pdo->prepare(
        "SELECT paper_id as post_id, paper_title, journal_name, publication_years, doi, link, novelty_score, authors_name, abstract, subject_area, type, user_id as author_id
         FROM papers 
         WHERE user_id = ? 
         ORDER BY publication_years DESC"
    );
    $stmt_papers->execute([$profile_user_id]);
    $user_posts = $stmt_papers->fetchAll(PDO::FETCH_ASSOC);

    // 4. Combine and return
    $response = [
        'success' => true, // Added success flag
        'user_info' => $user_info,
        'user_posts' => $user_posts
    ];
    
    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}