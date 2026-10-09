<?php
declare(strict_types=1);

/*
 * recipe_form.php
 * The recipe form fields, shared by add.php and edit.php so we only write them once.
 * The including page sets: $formAction, $buttonText, $id, $title, $description,
 * $categoryId, $steps, $ingredients and $categories.
 * The HTML attributes (required, minlength, maxlength) are the browser-side validation.
 * $formAction and $buttonText are fixed text from our own code (not user input),
 * so they are printed as-is. Everything the user typed is escaped.
 */

// [VARIABLES FROM THE INCLUDING PAGE]
// These @var notes tell the code editor where the variables come from,
// so it stops reporting them as "undefined". PHP itself ignores comments.
/** @var string $formAction */
/** @var string $buttonText */
/** @var int $id */
/** @var string $title */
/** @var string $description */
/** @var int $categoryId */
/** @var string $steps */
/** @var array $ingredients */
/** @var array $categories */
?>
<form method="post" action="<?= $formAction ?>">
    <?php // [HIDDEN RECIPE ID] only needed when editing ?>
    <?php if ($id > 0): ?>
        <input type="hidden" name="id" value="<?= (int) $id ?>">
    <?php endif; ?>

    <label for="title">Title</label>
    <input id="title" name="title" required minlength="3" maxlength="100"
           value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>">

    <label for="description">Short description</label>
    <input id="description" name="description" required minlength="10" maxlength="255"
           value="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>">

    <label for="category_id">Category</label>
    <select id="category_id" name="category_id" required>
        <option value="">Choose a category</option>
        <?php foreach ($categories as $category): ?>
            <option value="<?= (int) $category['id'] ?>" <?= (int) $category['id'] === $categoryId ? 'selected' : '' ?>>
                <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
            </option>
        <?php endforeach; ?>
    </select>

    <?php
    // [INGREDIENT FIELDS]
    // Every box is named "ingredients[]" so PHP receives them as one array.
    // The "+ Add ingredient" button (app.js) adds more boxes. Blank boxes are ignored.
    ?>
    <label>Ingredients</label>
    <div id="ingredient-list">
        <?php foreach ($ingredients as $index => $item): ?>
            <input name="ingredients[]" maxlength="100" placeholder="e.g. 3 cloves garlic"
                   <?= $index === 0 ? 'required' : '' ?>
                   value="<?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>">
        <?php endforeach; ?>
    </div>
    <button type="button" id="add-ingredient" class="secondary">+ Add ingredient</button>

    <label for="steps">Cooking steps</label>
    <textarea id="steps" name="steps" required minlength="10" maxlength="5000"
              placeholder="1. ...&#10;2. ..."><?= htmlspecialchars($steps, ENT_QUOTES, 'UTF-8') ?></textarea>

    <button><?= $buttonText ?></button>
</form>
