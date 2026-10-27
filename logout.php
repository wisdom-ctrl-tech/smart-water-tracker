<?php
// ============================================================
// logout.php  -  Log the user out
// ============================================================
require_once 'auth.php';

// Empty the session data.
$_SESSION = [];

// Remove the session cookie from the browser as well.
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

// Finally destroy the session on the server.
session_destroy();

header('Location: login.php');
exit;
