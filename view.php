<?php
declare(strict_types=1);

/*
 * view.php
 * Shows one recipe in full (view.php?id=3): details, ingredients, steps,
 * the favorite button, and all comments. The owner also sees Edit/Delete.
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

$userId = $_SESSION['user_id'];
$recipeObj = new Recipe($pdo);

// [READ RECIPE ID FROM GET]
// FILTER_VALIDATE_INT gives null (missing) or false (not a number).
// (int) turns both into 0. No recipe has id 0, so it is treated as "not found" below.
$id = (int) filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// [FETCH RECIPE, INGREDIENTS, COMMENTS]
try {
    $recipe = $recipeObj->find($id);

    // [RECIPE NOT FOUND]
    // Missing, invalid or deleted id: go back to the list with a message.
    if ($recipe === null) {
        $_SESSION['flash_error'] = 'Recipe not found.';
        header('Location: index.php');
        exit;
    }

    $ingredients = $recipeObj->ingredients($id);

    // Oldest comments first so the conversation reads top to bottom.
    $stmt = $pdo->prepare(
        "SELECT c.id, c.user_id, c.body, u.username,
                DATE_FORMAT(c.created_at, '%b %e, %Y %l:%i %p') AS posted_on,
                DATE_FORMAT(c.updated_at, '%b %e, %Y %l:%i %p') AS edited_on
         FROM comments c
         JOIN users u ON u.id = c.user_id
         WHERE c.recipe_id = :recipe_id
         ORDER BY c.created_at, c.id"
    );
    $stmt->execute([':recipe_id' => $id]);
    $comments = $stmt->fetchAll();

    // [IS THIS RECIPE IN MY FAVORITES?]
    $stmt = $pdo->prepare('SELECT 1 FROM favorites WHERE user_id = :user_id AND recipe_id = :recipe_id');
    $stmt->execute([':user_id' => $userId, ':recipe_id' => $id]);
    $isFavorite = $stmt->fetch() !== false;
} catch (PDOException $e) {
    error_log('View recipe failed: ' . $e->getMessage());
    $_SESSION['flash_error'] = 'Could not load this recipe right now. Please try again.';
    header('Location: index.php');
    exit;
}

// [IS THIS MY RECIPE?]
// Only the member who posted it sees the Edit and Delete buttons.
$isOwner = (int) $recipe['user_id'] === $userId;

$pageTitle = $recipe['title'];
require_once 'header.php';
?>

<?php // [RECIPE DETAILS] ?>
<h1><?= htmlspecialchars($recipe['title'], ENT_QUOTES, 'UTF-8') ?></h1>
<p class="meta">
    <span class="tag"><?= htmlspecialchars($recipe['category'], ENT_QUOTES, 'UTF-8') ?></span>
    by <?= htmlspecialchars($recipe['username'], ENT_QUOTES, 'UTF-8') ?>
    · <?= htmlspecialchars($recipe['posted_on'], ENT_QUOTES, 'UTF-8') ?>
    <?php // [EDITED LABEL] updated_at is only filled after an edit ?>
    <?php if ($recipe['edited_on'] !== null): ?>
        · edited <?= htmlspecialchars($recipe['edited_on'], ENT_QUOTES, 'UTF-8') ?>
    <?php endif; ?>
</p>

<div class="actions">
    <?php // [FAVORITE BUTTON] app.js sends this to toggle_favorite.php without reloading ?>
    <button type="button" class="fav-btn <?= $isFavorite ? 'saved' : '' ?>" data-id="<?= (int) $recipe['id'] ?>">
        <?= $isFavorite ? '♥ Saved' : '♡ Save to favorites' ?>
    </button>

    <?php // [OWNER ONLY BUTTONS] ?>
    <?php if ($isOwner): ?>
        <a class="btn secondary" href="edit.php?id=<?= (int) $recipe['id'] ?>">Edit</a>
        <form method="post" action="delete.php" class="inline"
              onsubmit="return confirm('Delete this recipe? This cannot be undone.');">
            <input type="hidden" name="id" value="<?= (int) $recipe['id'] ?>">
            <button class="danger">Delete</button>
        </form>
    <?php endif; ?>
</div>

<p><?= htmlspecialchars($recipe['description'], ENT_QUOTES, 'UTF-8') ?></p>

<h2>Ingredients</h2>
<ul>
    <?php foreach ($ingredients as $item): ?>
        <li><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></li>
    <?php endforeach; ?>
</ul>

<h2>Cooking steps</h2>
<?php // nl2br() runs AFTER htmlspecialchars(), so only our own <br> tags are added ?>
<p><?= nl2br(htmlspecialchars($recipe['steps'], ENT_QUOTES, 'UTF-8')) ?></p>

<?php // [COMMENTS LIST] ?>
<h2>Comments (<?= count($comments) ?>)</h2>

<?php if (!$comments): ?>
    <p class="muted">No comments yet. Be the first to say something!</p>
<?php endif; ?>

<ul class="list">
    <?php foreach ($comments as $comment): ?>
        <li>
            <p class="meta">
                <strong><?= htmlspecialchars($comment['username'], ENT_QUOTES, 'UTF-8') ?></strong>
                · <?= htmlspecialchars($comment['posted_on'], ENT_QUOTES, 'UTF-8') ?>
                <?php if ($comment['edited_on'] !== null): ?>
                    · edited <?= htmlspecialchars($comment['edited_on'], ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </p>
            <p><?= nl2br(htmlspecialchars($comment['body'], ENT_QUOTES, 'UTF-8')) ?></p>

            <?php // [EDIT / DELETE OWN COMMENT] <details> opens the edit box without any JavaScript ?>
            <?php if ((int) $comment['user_id'] === $userId): ?>
                <details>
                    <summary>Edit or delete</summary>
                    <form method="post" action="comment.php">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="recipe_id" value="<?= (int) $recipe['id'] ?>">
                        <input type="hidden" name="comment_id" value="<?= (int) $comment['id'] ?>">
                        <textarea name="body" required maxlength="500"><?= htmlspecialchars($comment['body'], ENT_QUOTES, 'UTF-8') ?></textarea>
                        <button>Save</button>
                    </form>
                    <form method="post" action="comment.php" onsubmit="return confirm('Delete this comment?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="recipe_id" value="<?= (int) $recipe['id'] ?>">
                        <input type="hidden" name="comment_id" value="<?= (int) $comment['id'] ?>">
                        <button class="danger">Delete comment</button>
                    </form>
                </details>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>

<?php // [ADD COMMENT FORM] ?>
<form method="post" action="comment.php">
    <input type="hidden" name="action" value="add">
    <input type="hidden" name="recipe_id" value="<?= (int) $recipe['id'] ?>">
    <label for="body">Leave a comment</label>
    <textarea id="body" name="body" required maxlength="500" placeholder="Did you try it? Any tips?"></textarea>
    <button>Post comment</button>
</form>

<?php require_once 'footer.php'; ?>
