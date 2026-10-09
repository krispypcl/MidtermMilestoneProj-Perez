<?php
declare(strict_types=1);

/*
 * Recipe.php
 * A class that groups all database work for recipes and their ingredients:
 * listing, searching, reading one, validating, creating, updating and deleting.
 * Several pages (index, view, add, edit, delete) share these methods.
 */

class Recipe
{
    // [STORED CONNECTION]
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // [LIST CATEGORIES]
    // No outside value is used here, so a plain query() is safe.
    public function categories(): array
    {
        return $this->pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
    }

    // [CHECK CATEGORY EXISTS]
    public function categoryExists(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM categories WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() !== false;
    }

    // [SEARCH AND FILTER RECIPES]
    // We only ADD fixed SQL text for each filter that is used.
    // The actual values always go into $params, never into the SQL string.
    // 0 means "no filter" for $categoryId and $ownerId.
    public function search(string $keyword, int $categoryId, int $ownerId): array
    {
        $sql = "SELECT r.id, r.title, r.description, c.name AS category, u.username,
                       DATE_FORMAT(r.created_at, '%b %e, %Y') AS posted_on,
                       r.updated_at
                FROM recipes r
                JOIN categories c ON c.id = r.category_id
                JOIN users u ON u.id = r.user_id
                WHERE 1 = 1";
        $params = [];

        // Keyword matches the title, description, or any ingredient.
        // Each placeholder name is used once (required when emulated prepares are off).
        if ($keyword !== '') {
            $sql .= ' AND (r.title LIKE :kw1 OR r.description LIKE :kw2
                      OR r.id IN (SELECT recipe_id FROM ingredients WHERE name LIKE :kw3))';
            $params[':kw1'] = '%' . $keyword . '%';
            $params[':kw2'] = '%' . $keyword . '%';
            $params[':kw3'] = '%' . $keyword . '%';
        }

        if ($categoryId > 0) {
            $sql .= ' AND r.category_id = :category_id';
            $params[':category_id'] = $categoryId;
        }

        if ($ownerId > 0) {
            $sql .= ' AND r.user_id = :owner_id';
            $params[':owner_id'] = $ownerId;
        }

        // Latest recipes first.
        $sql .= ' ORDER BY r.created_at DESC, r.id DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    // [GET ONE RECIPE]
    // Returns null when no recipe has this id, so pages can show "not found".
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT r.*, c.name AS category, u.username,
                    DATE_FORMAT(r.created_at, '%b %e, %Y') AS posted_on,
                    DATE_FORMAT(r.updated_at, '%b %e, %Y %l:%i %p') AS edited_on
             FROM recipes r
             JOIN categories c ON c.id = r.category_id
             JOIN users u ON u.id = r.user_id
             WHERE r.id = :id"
        );
        $stmt->execute([':id' => $id]);
        $recipe = $stmt->fetch();

        if ($recipe === false) {
            return null;
        }

        return $recipe;
    }

    // [GET INGREDIENTS OF A RECIPE]
    public function ingredients(int $recipeId): array
    {
        $stmt = $this->pdo->prepare('SELECT name FROM ingredients WHERE recipe_id = :recipe_id ORDER BY id');
        $stmt->execute([':recipe_id' => $recipeId]);

        return $stmt->fetchAll();
    }

    // [VALIDATE RECIPE FORM]
    // Server-side copy of the rules in the HTML form.
    // Browser checks can be skipped, so the server must always check again.
    public function validate(string $title, string $description, int $categoryId, string $steps, array $ingredients): array
    {
        $errors = [];

        if (mb_strlen($title) < 3 || mb_strlen($title) > 100) {
            $errors[] = 'Title must be 3 to 100 characters.';
        }
        if (mb_strlen($description) < 10 || mb_strlen($description) > 255) {
            $errors[] = 'Description must be 10 to 255 characters.';
        }
        if (!$this->categoryExists($categoryId)) {
            $errors[] = 'Please choose a category.';
        }
        if (count($ingredients) === 0) {
            $errors[] = 'Add at least one ingredient.';
        }
        foreach ($ingredients as $item) {
            if (mb_strlen($item) > 100) {
                $errors[] = 'Each ingredient must be 100 characters or less.';
                break;
            }
        }
        if (mb_strlen($steps) < 10 || mb_strlen($steps) > 5000) {
            $errors[] = 'Cooking steps must be 10 to 5000 characters.';
        }

        return $errors;
    }

    // [CREATE RECIPE + INGREDIENTS IN ONE TRANSACTION]
    // Saving a recipe takes several INSERTs (1 recipe + many ingredients).
    // A transaction makes it all-or-nothing: if one insert fails,
    // rollBack() undoes the others, so we never get a recipe with missing ingredients.
    public function create(int $userId, int $categoryId, string $title, string $description, string $steps, array $ingredients): int
    {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO recipes (user_id, category_id, title, description, steps)
                 VALUES (:user_id, :category_id, :title, :description, :steps)'
            );
            $stmt->execute([
                ':user_id'     => $userId,
                ':category_id' => $categoryId,
                ':title'       => $title,
                ':description' => $description,
                ':steps'       => $steps,
            ]);
            $recipeId = (int) $this->pdo->lastInsertId();

            $this->saveIngredients($recipeId, $ingredients);

            $this->pdo->commit();

            return $recipeId;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e; // let the page log it and show a friendly message
        }
    }

    // [UPDATE RECIPE + INGREDIENTS IN ONE TRANSACTION]
    // Three write steps: update the recipe, delete old ingredients, insert the new list.
    // updated_at = NOW() is what lets members see the recipe was "edited".
    // "AND user_id = :user_id" means only the owner's recipe can change.
    public function update(int $id, int $userId, int $categoryId, string $title, string $description, string $steps, array $ingredients): void
    {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                'UPDATE recipes
                 SET category_id = :category_id, title = :title, description = :description,
                     steps = :steps, updated_at = NOW()
                 WHERE id = :id AND user_id = :user_id'
            );
            $stmt->execute([
                ':category_id' => $categoryId,
                ':title'       => $title,
                ':description' => $description,
                ':steps'       => $steps,
                ':id'          => $id,
                ':user_id'     => $userId,
            ]);

            $stmt = $this->pdo->prepare('DELETE FROM ingredients WHERE recipe_id = :recipe_id');
            $stmt->execute([':recipe_id' => $id]);

            $this->saveIngredients($id, $ingredients);

            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // [DELETE OWN RECIPE]
    // One DELETE is enough: ON DELETE CASCADE in the database removes the
    // recipe's ingredients, comments and favorites in the same step.
    // Returns false if nothing was deleted (wrong id, or not the owner).
    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM recipes WHERE id = :id AND user_id = :user_id');
        $stmt->execute([':id' => $id, ':user_id' => $userId]);

        return $stmt->rowCount() > 0;
    }

    // [INSERT INGREDIENT ROWS]
    // Private: only used inside create() and update(), which already started a transaction.
    // The statement is prepared once and executed once per ingredient.
    private function saveIngredients(int $recipeId, array $ingredients): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO ingredients (recipe_id, name) VALUES (:recipe_id, :name)');

        foreach ($ingredients as $item) {
            $stmt->execute([':recipe_id' => $recipeId, ':name' => $item]);
        }
    }
}
