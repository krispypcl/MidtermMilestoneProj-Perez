<?php
declare(strict_types=1);

/*
 * register.php
 * Lets a visitor create a member account.
 * Rules: username 3-30 letters/numbers/underscore, password at least 8 characters.
 */

require_once 'db.php';
require_once 'User.php';

// [START SESSION]
session_start();

// [ALREADY LOGGED IN]
// Members don't need this page, so send them to the recipe list.
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$errors = [];
$username = '';

// [HANDLE REGISTER FORM]
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) filter_input(INPUT_POST, 'username'));
    $password = (string) filter_input(INPUT_POST, 'password');
    $confirm  = (string) filter_input(INPUT_POST, 'confirm');

    // [VALIDATE FORM INPUT]
    // Same rules as the HTML form, checked again because browser checks can be bypassed.
    if (strlen($username) < 3 || strlen($username) > 30) {
        $errors[] = 'Username must be 3 to 30 characters.';
    } elseif (!preg_match('/^[A-Za-z0-9_]+$/', $username)) {
        $errors[] = 'Username can only use letters, numbers and underscores.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    // [SAVE NEW ACCOUNT]
    if (!$errors) {
        try {
            $userObj = new User($pdo);

            if ($userObj->exists($username)) {
                $errors[] = 'That username is already taken.';
            } else {
                $userObj->register($username, $password);

                // [REDIRECT AFTER SAVE]
                // Post/Redirect/Get: refreshing the next page won't resubmit this form.
                $_SESSION['flash'] = 'Account created! You can now log in.';
                header('Location: login.php');
                exit;
            }
        } catch (PDOException $e) {
            error_log('Register failed: ' . $e->getMessage());
            $errors[] = 'Something went wrong. Please try again.';
        }
    }
}

$pageTitle = 'Create account';
require_once 'header.php';
?>

<h1>Join Lutong Bahay</h1>
<p class="muted">Create an account to share and save home-cooked recipes.</p>

<?php // [SHOW ERROR MESSAGES] ?>
<?php if ($errors): ?>
    <ul class="msg error">
        <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php // [REGISTER FORM] the HTML attributes are the browser-side validation ?>
<form method="post" action="register.php">
    <label for="username">Username</label>
    <input id="username" name="username" required minlength="3" maxlength="30"
           pattern="[A-Za-z0-9_]+" title="Letters, numbers and underscores only"
           value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>">

    <label for="password">Password</label>
    <input id="password" name="password" type="password" required minlength="8">

    <label for="confirm">Confirm password</label>
    <input id="confirm" name="confirm" type="password" required minlength="8">

    <button>Create account</button>
</form>

<p class="muted">Already a member? <a href="login.php">Log in</a></p>

<?php require_once 'footer.php'; ?>
