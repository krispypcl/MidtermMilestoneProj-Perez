<?php
declare(strict_types=1);

/*
 * delete.php
 * Deletes a member's OWN recipe. Called by the Delete button on view.php (POST only).
 */

require_once 'db.php';
require_once 'Recipe.php';

// [START SESSION]
session_start();

// [MEMBERS ONLY]
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// [POST ONLY]
// Deleting changes data, so it must never happen from a GET link.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$userId = $_SESSION['user_id'];
$recipeObj = new Recipe($pdo);

// [READ RECIPE ID]
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

// [DELETE OWN RECIPE]
// Recipe::delete() checks the owner in its SQL and returns false if nothing was removed.
if ($id === null || $id === false) {
    $_SESSION['flash_error'] = 'Recipe not found.';
} else {
    try {
        if ($recipeObj->delete($id, $userId)) {
            $_SESSION['flash'] = 'Recipe deleted.';
        } else {
            $_SESSION['flash_error'] = 'You can only delete your own recipes.';
        }
    } catch (PDOException $e) {
        error_log('Delete recipe failed: ' . $e->getMessage());
        $_SESSION['flash_error'] = 'Could not delete the recipe. Please try again.';
    }
}

// [REDIRECT AFTER DELETE]
header('Location: index.php?mine=1');
exit;
