<?php
declare(strict_types=1);

/*
 * logout.php
 * Ends the member's session. Only accepts POST (from the Log out button),
 * because logging out changes something and GET links must never do that.
 */

// [START SESSION]
session_start();

// [LOG OUT ON POST ONLY]
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Empty the session data, then destroy the session itself.
    $_SESSION = [];
    session_destroy();
}

// [REDIRECT]
header('Location: login.php');
exit;
