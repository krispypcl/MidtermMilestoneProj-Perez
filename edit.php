<?php
declare(strict_types=1);

/*
 * edit.php
 * Lets a member change THEIR OWN recipe.
 * GET  edit.php?id=3 -> shows the form filled with the current recipe.
 * POST (hidden id)   -> validates and saves the changes.
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
$errors = [];
$recipe = null;
$categories = [];

// [READ RECIPE ID]
// First visit: the id comes from the link (GET).
// After submitting: the id comes from the hidden form field (POST).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
} else {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
}
if ($id === null || $id === false) {
    $id = 0;
}

// [LOAD RECIPE AND CHECK OWNER]
// We must know the recipe exists and belongs to this member before showing or saving the form.
try {
    $categories = $recipeObj->categories();
    if ($id > 0) {
        $recipe = $recipeObj->find($id);
    }
} catch (PDOException $e) {
    error_log('Load recipe for edit failed: ' . $e->getMessage());
    $errors[] = 'Could not load this recipe right now. Please try again.';
}

if ($recipe === null && !$errors) {
    $errors[] = 'Recipe not found.';
} elseif ($recipe !== null && (int) $recipe['user_id'] !== $userId) {
    $errors[] = 'You can only edit your own recipes.';
    $recipe = null;
}

// [HANDLE EDIT FORM]
if ($recipe !== null && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim((string) filter_input(INPUT_POST, 'title'));
    $description = trim((string) filter_input(INPUT_POST, 'description'));
    $steps       = trim((string) filter_input(INPUT_POST, 'steps'));

    $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    if ($categoryId === null || $categoryId === false) {
        $categoryId = 0;
    }

    // [READ INGREDIENT LIST]
    $ingredients = [];
    $list = $_POST['ingredients'] ?? [];
    if (!is_array($list)) {
        $list = [];
    }
    foreach ($list as $item) {
        if (is_string($item) && trim($item) !== '') {
            $ingredients[] = trim($item);
        }
    }

    try {
        // [VALIDATE FORM INPUT]
        $errors = $recipeObj->validate($title, $description, $categoryId, $steps, $ingredients);

        if (!$errors) {
            // [UPDATE RECORD] uses a transaction inside Recipe::update()
            $recipeObj->update($id, $userId, $categoryId, $title, $description, $steps, $ingredients);

            // [REDIRECT AFTER SAVE]
            $_SESSION['flash'] = 'Recipe updated.';
            header('Location: view.php?id=' . $id);
            exit;
        }
    } catch (PDOException $e) {
        error_log('Edit recipe failed: ' . $e->getMessage());
        $errors[] = 'Could not save your changes. Please try again.';
    }

    if (!$ingredients) {
        $ingredients = [''];
    }
} elseif ($recipe !== null) {
    // [FILL FORM WITH CURRENT RECIPE]
    $title       = $recipe['title'];
    $description = $recipe['description'];
    $categoryId  = (int) $recipe['category_id'];
    $steps       = $recipe['steps'];

    $ingredients = [];
    try {
        foreach ($recipeObj->ingredients($id) as $row) {
            $ingredients[] = $row['name'];
        }
    } catch (PDOException $e) {
        error_log('Load ingredients failed: ' . $e->getMessage());
        $errors[] = 'Could not load the ingredients. Please try again.';
    }
    if (!$ingredients) {
        $ingredients = [''];
    }
}

$pageTitle = 'Edit recipe';
$formAction = 'edit.php';
$buttonText = 'Save changes';
require_once 'header.php';
?>

<h1>Edit recipe</h1>

<?php // [SHOW ERROR MESSAGES] ?>
<?php if ($errors): ?>
    <ul class="msg error">
        <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php // The form only appears if the recipe exists and belongs to this member. ?>
<?php if ($recipe !== null): ?>
    <?php require_once 'recipe_form.php'; ?>
    <p><a href="view.php?id=<?= (int) $id ?>">Cancel</a></p>
<?php else: ?>
    <p><a href="index.php">← Back to recipes</a></p>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
