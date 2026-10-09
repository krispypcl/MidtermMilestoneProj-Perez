<?php
declare(strict_types=1);

/*
 * add.php
 * Lets a member share a new recipe: title, description, category,
 * any number of ingredients, and cooking steps.
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

// [EMPTY FORM VALUES]
// The form starts blank with one ingredient box.
$id = 0;
$title = '';
$description = '';
$categoryId = 0;
$steps = '';
$ingredients = [''];

// [HANDLE NEW RECIPE FORM]
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim((string) filter_input(INPUT_POST, 'title'));
    $description = trim((string) filter_input(INPUT_POST, 'description'));
    $steps       = trim((string) filter_input(INPUT_POST, 'steps'));

    // (int) turns a missing or invalid category into 0, which validate() rejects.
    $categoryId = (int) filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);

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
        // [VALIDATE FORM INPUT] rules live in Recipe::validate()
        $errors = $recipeObj->validate($title, $description, $categoryId, $steps, $ingredients);

        if (!$errors) {
            // [INSERT NEW RECORD] uses a transaction inside Recipe::create()
            $newId = $recipeObj->create($userId, $categoryId, $title, $description, $steps, $ingredients);

            // [REDIRECT AFTER SAVE]
            $_SESSION['flash'] = 'Recipe shared! Salamat sa pagbabahagi.';
            header('Location: view.php?id=' . $newId);
            exit;
        }
    } catch (PDOException $e) {
        error_log('Add recipe failed: ' . $e->getMessage());
        $errors[] = 'Could not save your recipe. Please try again.';
    }
}

// Always show at least one ingredient box.
if (!$ingredients) {
    $ingredients = [''];
}

// [FETCH CATEGORIES FOR THE DROPDOWN]
$categories = [];
try {
    $categories = $recipeObj->categories();
} catch (PDOException $e) {
    error_log('Load categories failed: ' . $e->getMessage());
    $errors[] = 'Could not load categories. Please try again.';
}

$pageTitle = 'Share a recipe';
$formAction = 'add.php';
$buttonText = 'Share recipe';
require_once 'header.php';
?>

<h1>Share a recipe</h1>

<?php // [SHOW ERROR MESSAGES] ?>
<?php if ($errors): ?>
    <ul class="msg error">
        <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php require_once 'recipe_form.php'; ?>

<?php require_once 'footer.php'; ?>
