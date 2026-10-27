<?php
// ============================================================
// auth.php
// A small helper file with the things every page needs:
// the session, login checks, CSRF protection and HTML escaping.
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

/** The CSRF token of this session (created once and then reused). */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** The hidden input that must be inside every form which changes data. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Checks the CSRF token that a form sent back. */
function csrf_check(): bool
{
    if (empty($_POST['csrf_token']) || !is_string($_POST['csrf_token'])) {
        return false;
    }
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

/** Escapes text before it is printed, so nobody can inject HTML. */
function e($text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}
