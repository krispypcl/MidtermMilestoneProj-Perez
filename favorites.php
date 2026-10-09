<?php
declare(strict_types=1);

/*
 * favorites.php
 * Lists the recipes the logged-in member saved.
 * The Remove button uses app.js, so the page does not reload.
 */

require_once 'db.php';

// [START SESSION]
session_start();

// [MEMBERS ONLY]
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$errors = [];
$favorites = [];

// [FETCH MY FAVORITES]
// Most recently saved first.
try {
    $stmt = $pdo->prepare(
        'SELECT r.id, r.title, r.description, c.name AS category, u.username
         FROM favorites f
         JOIN recipes r ON r.id = f.recipe_id
         JOIN categories c ON c.id = r.category_id
         JOIN users u ON u.id = r.user_id
         WHERE f.user_id = :user_id
         ORDER BY f.created_at DESC'
    );
    $stmt->execute([':user_id' => $userId]);
    $favorites = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Load favorites failed: ' . $e->getMessage());
    $errors[] = 'Could not load your favorites. Please try again.';
}

$pageTitle = 'My favorites';
require_once 'header.php';
?>

<h1>My favorites</h1>

<?php // [SHOW ERROR MESSAGES] ?>
<?php if ($errors): ?>
    <ul class="msg error">
        <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if (!$favorites && !$errors): ?>
    <p class="muted">You haven't saved any recipes yet. <a href="index.php">Browse recipes</a></p>
<?php endif; ?>

<?php // [DISPLAY FAVORITES LIST] each <li> has class "fav-item" so app.js can remove it ?>
<ul class="list">
    <?php foreach ($favorites as $recipe): ?>
        <li class="fav-item">
            <h3><a href="view.php?id=<?= (int) $recipe['id'] ?>"><?= htmlspecialchars($recipe['title'], ENT_QUOTES, 'UTF-8') ?></a></h3>
            <p class="meta">
                <span class="tag"><?= htmlspecialchars($recipe['category'], ENT_QUOTES, 'UTF-8') ?></span>
                by <?= htmlspecialchars($recipe['username'], ENT_QUOTES, 'UTF-8') ?>
            </p>
            <p><?= htmlspecialchars($recipe['description'], ENT_QUOTES, 'UTF-8') ?></p>
            <button type="button" class="fav-btn secondary" data-id="<?= (int) $recipe['id'] ?>">Remove</button>
        </li>
    <?php endforeach; ?>
</ul>

<?php require_once 'footer.php'; ?>
