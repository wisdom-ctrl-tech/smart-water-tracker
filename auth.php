<?php
// ============================================================
// auth.php
// A small helper file with the things every page needs:
// the session, login checks and HTML escaping.
// ============================================================

// Start the session once for every page that includes this file.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Is somebody logged in? */
function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

/** Sends logged-out visitors to the login page. Used by the dashboard. */
function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

/** Escapes text before it is printed, so nobody can inject HTML. */
function e($text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}
