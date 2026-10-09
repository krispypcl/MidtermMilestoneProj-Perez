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

// [READ RECIPE ID]
// First visit: the id comes from the link (GET).
// After submitting: the id comes from the hidden form field (POST).
// (int) turns a missing or invalid id into 0, which matches no recipe.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
} else {
    $id = (int) filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
}

// [LOAD RECIPE, INGREDIENTS AND CATEGORIES]
try {
    $recipe = $recipeObj->find($id);
    $categories = $recipeObj->categories();

    $ingredients = [];
    foreach ($recipeObj->ingredients($id) as $row) {
        $ingredients[] = $row['name'];
    }
} catch (PDOException $e) {
    error_log('Load recipe for edit failed: ' . $e->getMessage());
    $_SESSION['flash_error'] = 'Could not load this recipe right now. Please try again.';
    header('Location: index.php');
    exit;
}

// [RECIPE NOT FOUND]
if ($recipe === null) {
    $_SESSION['flash_error'] = 'Recipe not found.';
    header('Location: index.php');
    exit;
}

// [OWNER CHECK]
// Members may only edit what they themselves posted.
if ((int) $recipe['user_id'] !== $userId) {
    $_SESSION['flash_error'] = 'You can only edit your own recipes.';
    header('Location: view.php?id=' . $id);
    exit;
}

// [FILL FORM WITH CURRENT RECIPE]
// These are the starting values. If the form was submitted, they are replaced below.
$title       = $recipe['title'];
$description = $recipe['description'];
$categoryId  = (int) $recipe['category_id'];
$steps       = $recipe['steps'];

// [HANDLE EDIT FORM]
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim((string) filter_input(INPUT_POST, 'title'));
    $description = trim((string) filter_input(INPUT_POST, 'description'));
    $steps       = trim((string) filter_input(INPUT_POST, 'steps'));
    $categoryId  = (int) filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);

    // [READ INGREDIENT LIST]
    // ingredients[] arrives as an array. Keep only non-blank text values.
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
}

// Always show at least one ingredient box.
if (!$ingredients) {
    $ingredients = [''];
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

<?php require_once 'recipe_form.php'; ?>
<p><a href="view.php?id=<?= (int) $id ?>">Cancel</a></p>

<?php require_once 'footer.php'; ?>
