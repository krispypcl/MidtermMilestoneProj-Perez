<?php
declare(strict_types=1);

/*
 * comment.php
 * Handles the comment forms on view.php: add, update (edit) and delete.
 * It has no page of its own. It saves the change, then redirects back to the recipe.
 */

require_once 'db.php';

// [START SESSION]
session_start();

// [MEMBERS ONLY]
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// [POST ONLY]
// Comments change the database, so a plain GET visit is just sent away.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$userId = $_SESSION['user_id'];
$errors = [];

// [READ FORM VALUES]
// (int) turns a missing or invalid id into 0.
$action    = (string) filter_input(INPUT_POST, 'action');
$recipeId  = (int) filter_input(INPUT_POST, 'recipe_id', FILTER_VALIDATE_INT);
$commentId = (int) filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);
$body      = trim((string) filter_input(INPUT_POST, 'body'));

if ($recipeId === 0) {
    $_SESSION['flash_error'] = 'Recipe not found.';
    header('Location: index.php');
    exit;
}

// [VALIDATE FORM INPUT]
if (!in_array($action, ['add', 'update', 'delete'], true)) {
    $errors[] = 'Unknown action.';
}
if ($action === 'add' || $action === 'update') {
    if ($body === '') {
        $errors[] = 'Comment cannot be empty.';
    } elseif (mb_strlen($body) > 500) {
        $errors[] = 'Comment must be 500 characters or less.';
    }
}
if (($action === 'update' || $action === 'delete') && $commentId === 0) {
    $errors[] = 'Comment not found.';
}

// [SAVE THE CHANGE]
// "AND user_id = :user_id" on update/delete means a member can only
// change their OWN comment, even if they edit the hidden comment_id in the browser.
if ($errors) {
    $_SESSION['flash_error'] = implode(' ', $errors);
} else {
    try {
        if ($action === 'add') {
            $stmt = $pdo->prepare(
                'INSERT INTO comments (recipe_id, user_id, body) VALUES (:recipe_id, :user_id, :body)'
            );
            $stmt->execute([':recipe_id' => $recipeId, ':user_id' => $userId, ':body' => $body]);
            $_SESSION['flash'] = 'Comment posted.';
        } elseif ($action === 'update') {
            // updated_at = NOW() is what shows the "edited" label.
            $stmt = $pdo->prepare(
                'UPDATE comments SET body = :body, updated_at = NOW() WHERE id = :id AND user_id = :user_id'
            );
            $stmt->execute([':body' => $body, ':id' => $commentId, ':user_id' => $userId]);
            $_SESSION['flash'] = 'Comment updated.';
        } else {
            $stmt = $pdo->prepare('DELETE FROM comments WHERE id = :id AND user_id = :user_id');
            $stmt->execute([':id' => $commentId, ':user_id' => $userId]);
            $_SESSION['flash'] = 'Comment deleted.';
        }
    } catch (PDOException $e) {
        error_log('Comment failed: ' . $e->getMessage());
        $_SESSION['flash_error'] = 'Could not save your comment. Please try again.';
    }
}

// [REDIRECT BACK TO THE RECIPE]
// Post/Redirect/Get: refreshing the recipe page won't post the comment twice.
header('Location: view.php?id=' . $recipeId);
exit;
