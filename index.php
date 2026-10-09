<?php
declare(strict_types=1);

/*
 * index.php
 * The home page for members: shows the latest recipes first,
 * with a keyword search, a category filter and a "My Recipes" view.
 * It only READS data, so all filters come from GET (they appear in the URL).
 */

require_once 'db.php';
require_once 'Recipe.php';

// [START SESSION]
session_start();

// [MEMBERS ONLY]
// Guests have no user_id in the session, so they are sent to the login page.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$recipeObj = new Recipe($pdo);
$errors = [];
$categories = [];
$recipes = [];

// [READ SEARCH AND FILTER VALUES FROM GET]
// filter_input() returns null when a value is missing; (string) turns that into ''.
// FILTER_VALIDATE_INT returns false for anything that is not a whole number.
$keyword = trim((string) filter_input(INPUT_GET, 'q'));

$categoryId = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);
if ($categoryId === null || $categoryId === false) {
    $categoryId = 0; // 0 = all categories
}

// [MY RECIPES FILTER] (our own extra feature)
// index.php?mine=1 shows only the recipes the logged-in member posted.
$mineOnly = filter_input(INPUT_GET, 'mine', FILTER_VALIDATE_INT) === 1;
$ownerId = 0;
if ($mineOnly) {
    $ownerId = $userId;
}

// [FETCH CATEGORIES AND RECIPES]
try {
    $categories = $recipeObj->categories();
    $recipes = $recipeObj->search($keyword, $categoryId, $ownerId);
} catch (PDOException $e) {
    error_log('Recipe list failed: ' . $e->getMessage());
    $errors[] = 'Could not load recipes right now. Please try again.';
}

$pageTitle = 'Recipes';
require_once 'header.php';
?>

<?php if ($mineOnly): ?>
    <h1>My Recipes</h1>
<?php else: ?>
    <h1>Latest Recipes</h1>
<?php endif; ?>

<?php // [SEARCH FORM] method="get" because searching only reads data ?>
<form method="get" action="index.php" class="search">
    <?php if ($mineOnly): ?>
        <input type="hidden" name="mine" value="1">
    <?php endif; ?>
    <input name="q" placeholder="Search title, description or ingredient" maxlength="100"
           value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>">
    <select name="category">
        <option value="0">All categories</option>
        <?php foreach ($categories as $category): ?>
            <option value="<?= (int) $category['id'] ?>" <?php if ((int) $category['id'] === $categoryId): ?>selected<?php endif; ?>>
                <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button>Search</button>
</form>

<?php // [SHOW ERROR MESSAGES] ?>
<?php if ($errors): ?>
    <ul class="msg error">
        <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php
// [DISPLAY RECIPE LIST]
// Ids are printed with (int): an integer can never contain HTML, so it is already safe
// (and htmlspecialchars() only accepts strings under strict_types).
// Every text value from the database goes through htmlspecialchars().
?>
<?php if (!$recipes && !$errors): ?>
    <p class="muted">No recipes found. Try another keyword or <a href="add.php">share one</a>!</p>
<?php endif; ?>

<ul class="list">
    <?php foreach ($recipes as $recipe): ?>
        <li>
            <h3><a href="view.php?id=<?= (int) $recipe['id'] ?>"><?= htmlspecialchars($recipe['title'], ENT_QUOTES, 'UTF-8') ?></a></h3>
            <p class="meta">
                <span class="tag"><?= htmlspecialchars($recipe['category'], ENT_QUOTES, 'UTF-8') ?></span>
                by <?= htmlspecialchars($recipe['username'], ENT_QUOTES, 'UTF-8') ?>
                · <?= htmlspecialchars($recipe['posted_on'], ENT_QUOTES, 'UTF-8') ?>
                <?php if ($recipe['updated_at'] !== null): ?>· edited<?php endif; ?>
            </p>
            <p><?= htmlspecialchars($recipe['description'], ENT_QUOTES, 'UTF-8') ?></p>
        </li>
    <?php endforeach; ?>
</ul>

<?php require_once 'footer.php'; ?>
