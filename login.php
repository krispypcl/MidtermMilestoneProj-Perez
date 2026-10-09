<?php
declare(strict_types=1);

/*
 * login.php
 * Checks a member's username and password and starts their logged-in session.
 */

require_once 'db.php';
require_once 'User.php';

// [START SESSION]
session_start();

// [ALREADY LOGGED IN]
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$errors = [];
$username = '';

// [HANDLE LOGIN FORM]
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) filter_input(INPUT_POST, 'username'));
    $password = (string) filter_input(INPUT_POST, 'password');

    // [VALIDATE FORM INPUT]
    if ($username === '' || $password === '') {
        $errors[] = 'Please enter your username and password.';
    }

    if (!$errors) {
        try {
            $userObj = new User($pdo);
            $user = $userObj->login($username, $password);

            if ($user === null) {
                // Same message for both cases, so we don't reveal which usernames exist.
                $errors[] = 'Wrong username or password.';
            } else {
                // [START LOGGED-IN SESSION]
                // session_regenerate_id(true) gives the member a brand-new session id,
                // so an old/stolen session id cannot be used to take over this login.
                session_regenerate_id(true);
                $_SESSION['user_id']  = (int) $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['flash']    = 'Welcome back, ' . $user['username'] . '!';

                // [REDIRECT AFTER LOGIN]
                header('Location: index.php');
                exit;
            }
        } catch (PDOException $e) {
            error_log('Login failed: ' . $e->getMessage());
            $errors[] = 'Something went wrong. Please try again.';
        }
    }
}

$pageTitle = 'Log in';
require_once 'header.php';
?>

<h1>Log in</h1>
<p class="muted">Welcome, kabarangay! Log in to see what everyone is cooking.</p>

<?php // [SHOW ERROR MESSAGES] ?>
<?php if ($errors): ?>
    <ul class="msg error">
        <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php // [LOGIN FORM] ?>
<form method="post" action="login.php">
    <label for="username">Username</label>
    <input id="username" name="username" required maxlength="30"
           value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>">

    <label for="password">Password</label>
    <input id="password" name="password" type="password" required>

    <button>Log in</button>
</form>

<p class="muted">New here? <a href="register.php">Create an account</a></p>

<?php require_once 'footer.php'; ?>
