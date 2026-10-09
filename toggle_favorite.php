<?php
declare(strict_types=1);

/*
 * toggle_favorite.php
 * Called in the background by app.js (fetch). If the recipe is already in the
 * member's favorites it is removed, otherwise it is added.
 * It answers with JSON (not HTML), so the page that called it never reloads.
 */

require_once 'db.php';

// [START SESSION]
session_start();

// [JSON RESPONSE]
// Tells the browser this answer is JSON data, not a web page.
header('Content-Type: application/json');

// [MEMBERS ONLY + POST ONLY]
// We answer with JSON instead of redirecting, because JavaScript is waiting for JSON.
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Please log in first.']);
    exit;
}

$userId = $_SESSION['user_id'];

// [READ RECIPE ID]
$recipeId = filter_input(INPUT_POST, 'recipe_id', FILTER_VALIDATE_INT);
if ($recipeId === null || $recipeId === false) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Recipe not found.']);
    exit;
}

try {
    // [IS IT ALREADY SAVED?]
    $stmt = $pdo->prepare('SELECT 1 FROM favorites WHERE user_id = :user_id AND recipe_id = :recipe_id');
    $stmt->execute([':user_id' => $userId, ':recipe_id' => $recipeId]);
    $alreadySaved = $stmt->fetch() !== false;

    if ($alreadySaved) {
        // [REMOVE FAVORITE]
        $stmt = $pdo->prepare('DELETE FROM favorites WHERE user_id = :user_id AND recipe_id = :recipe_id');
        $saved = false;
    } else {
        // [ADD FAVORITE]
        // The FOREIGN KEY rejects recipe ids that don't exist (caught below).
        $stmt = $pdo->prepare('INSERT INTO favorites (user_id, recipe_id) VALUES (:user_id, :recipe_id)');
        $saved = true;
    }
    $stmt->execute([':user_id' => $userId, ':recipe_id' => $recipeId]);

    // [SEND RESULT TO JAVASCRIPT]
    echo json_encode(['ok' => true, 'saved' => $saved]);
} catch (PDOException $e) {
    error_log('Toggle favorite failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Could not update your favorites. Please try again.']);
}
