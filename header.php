<?php
declare(strict_types=1);

/*
 * header.php
 * The top of every page: <head>, site name, navigation and flash messages.
 * The page that includes this file sets $pageTitle first.
 */

/** @var string $pageTitle  set by the including page (tells the code editor it exists) */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- [MOBILE LAYOUT] makes phones use their real screen width instead of zooming out -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> · Lutong Bahay</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site">
    <a class="brand" href="index.php">🍲 Lutong Bahay</a>

    <?php // [MEMBER NAVIGATION] only shown when someone is logged in ?>
    <?php if (isset($_SESSION['user_id'])): ?>
        <nav>
            <a href="index.php">Recipes</a>
            <a href="index.php?mine=1">My Recipes</a>
            <a href="favorites.php">Favorites</a>
            <a href="add.php">Share</a>
            <?php // [LOGOUT] uses POST because logging out changes the session ?>
            <form method="post" action="logout.php" class="inline">
                <button class="link">Log out (<?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') ?>)</button>
            </form>
        </nav>
    <?php endif; ?>
</header>

<main class="card">
<?php
// [SHOW FLASH MESSAGES]
// A flash message is saved in the session right before a redirect,
// shown once here, then removed so it does not appear again on refresh.
?>
<?php if (isset($_SESSION['flash'])): ?>
    <p class="msg success"><?= htmlspecialchars($_SESSION['flash'], ENT_QUOTES, 'UTF-8') ?></p>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
<?php if (isset($_SESSION['flash_error'])): ?>
    <p class="msg error"><?= htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES, 'UTF-8') ?></p>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>
