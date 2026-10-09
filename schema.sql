-- ============================================================
-- schema.sql
-- Creates the "lutong_bahay" database with the six required
-- tables, plus starting categories and a few sample rows.
-- Import it in phpMyAdmin (Import tab) or the MySQL console.
-- Sample logins: aling_nena / password123, mang_tonyo / password123
-- ============================================================

-- [START FRESH]
-- Dropping first lets you re-import this file without "already exists" errors.
DROP DATABASE IF EXISTS lutong_bahay;
CREATE DATABASE lutong_bahay CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lutong_bahay;

-- [USERS]
-- UNIQUE username: two members can never share a name.
-- password_hash holds the output of password_hash(), never the real password.
CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(30)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- [CATEGORIES]
CREATE TABLE categories (
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- [RECIPES]
-- user_id / category_id are FOREIGN KEYS: a recipe must belong to a real member and a real category.
-- ON DELETE CASCADE: if a member is deleted, their recipes go too.
-- ON DELETE RESTRICT: a category that still has recipes cannot be deleted.
-- updated_at stays NULL until the recipe is edited, so we can show "edited".
CREATE TABLE recipes (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    title       VARCHAR(100) NOT NULL,
    description VARCHAR(255) NOT NULL,
    steps       TEXT         NOT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NULL DEFAULT NULL,
    FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- [INGREDIENTS]
-- One row per ingredient. Deleting a recipe deletes its ingredients automatically.
CREATE TABLE ingredients (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT UNSIGNED NOT NULL,
    name      VARCHAR(100) NOT NULL,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- [COMMENTS]
CREATE TABLE comments (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipe_id  INT UNSIGNED NOT NULL,
    user_id    INT UNSIGNED NOT NULL,
    body       VARCHAR(500) NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME     NULL DEFAULT NULL,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- [FAVORITES]
-- The PRIMARY KEY is the PAIR (user_id, recipe_id),
-- so the same member cannot save the same recipe twice.
CREATE TABLE favorites (
    user_id    INT UNSIGNED NOT NULL,
    recipe_id  INT UNSIGNED NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, recipe_id),
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- [STARTING CATEGORIES]
INSERT INTO categories (name) VALUES
    ('Ulam (Main Dish)'),
    ('Sabaw (Soup)'),
    ('Merienda (Snack)'),
    ('Kakanin (Rice Cake)'),
    ('Panghimagas (Dessert)'),
    ('Almusal (Breakfast)');

-- [SAMPLE MEMBERS] both passwords are "password123"
INSERT INTO users (username, password_hash) VALUES
    ('aling_nena', '$2y$10$gq.JVHwdyV4E1zBp5YeB/OTYY3wi3T.Ibht59TGXlxxQQXjDIxLFC'),
    ('mang_tonyo', '$2y$10$gq.JVHwdyV4E1zBp5YeB/OTYY3wi3T.Ibht59TGXlxxQQXjDIxLFC');

-- [SAMPLE RECIPES]
INSERT INTO recipes (user_id, category_id, title, description, steps, created_at) VALUES
    (1, 1, 'Chicken Adobo', 'The classic soy-and-vinegar braise every Filipino home knows by heart.',
     '1. Marinate the chicken in soy sauce and garlic for 30 minutes.\n2. Add vinegar, bay leaves and peppercorns. Do not stir until it boils.\n3. Simmer for 30 minutes until tender and the sauce thickens.',
     NOW() - INTERVAL 2 DAY),
    (2, 2, 'Sinigang na Baboy', 'Sour tamarind pork soup, perfect for rainy days.',
     '1. Boil the pork until tender.\n2. Add onion and tomato, then the tamarind mix.\n3. Add radish, sitaw and kangkong. Season with patis.',
     NOW() - INTERVAL 1 DAY),
    (1, 3, 'Turon', 'Crispy banana spring rolls with caramelized sugar.',
     '1. Roll a slice of saba and langka in a wrapper with brown sugar.\n2. Fry until golden.\n3. Sprinkle more sugar while hot so it caramelizes.',
     NOW());

INSERT INTO ingredients (recipe_id, name) VALUES
    (1, '1 kg chicken, cut into pieces'), (1, '1/2 cup soy sauce'), (1, '1/3 cup vinegar'),
    (1, '1 head garlic, crushed'), (1, '3 bay leaves'), (1, '1 tsp whole peppercorns'),
    (2, '1 kg pork ribs'), (2, '1 pack sinigang mix'), (2, '1 radish, sliced'),
    (2, '1 bunch kangkong'), (2, '2 tomatoes'),
    (3, '6 saba bananas'), (3, '1 cup ripe langka strips'), (3, '12 lumpia wrappers'), (3, '1 cup brown sugar');

INSERT INTO comments (recipe_id, user_id, body) VALUES
    (1, 2, 'Just like my lola used to make. Salamat!');

INSERT INTO favorites (user_id, recipe_id) VALUES (2, 1);
