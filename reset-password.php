<?php
// ============================================================
// reset-password.php  -  Choose a new password with a reset link
//
// The page is opened with ?token=... from forgot-password.php.
// The token is checked against the users table. If it is valid
// and not expired, the user may type a new password. The token
// is then cleared, so the link works only once.
// ============================================================
require_once 'helpers.php';

// Somebody who is already logged in does not need this page.
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$conn   = db();
$errors = [];

// The token arrives in the link (GET) or in the hidden field of the form (POST).
$token = '';
if (isset($_POST['token']) && is_string($_POST['token'])) {
    $token = trim($_POST['token']);
} elseif (isset($_GET['token']) && is_string($_GET['token'])) {
    $token = trim($_GET['token']);
}

// Look up the user whose reset token matches and has not expired.
$user = null;
if ($token !== '') {
    $token_hash = hash('sha256', $token);
    $now        = date('Y-m-d H:i:s');

    $stmt = $conn->prepare('SELECT id FROM users
                            WHERE reset_token = ? AND reset_expires_at > ? LIMIT 1');
    $stmt->bind_param('ss', $token_hash, $now);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// ------------------------------------------------------------
// Save the new password
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass    = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if ($user) {
        if ($pass === '') {
            $errors[] = 'Please enter a new password.';
        } elseif (strlen($pass) < 6) {
            $errors[] = 'Your password must be at least 6 characters long.';
        }
        if ($confirm !== $pass) {
            $errors[] = 'The two passwords do not match.';
        }
    }

    if (empty($errors) && $user) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        // Clearing the token also makes the link stop working.
        $stmt = $conn->prepare('UPDATE users
                                SET password = ?, reset_token = NULL, reset_expires_at = NULL
                                WHERE id = ?');
        $stmt->bind_param('si', $hash, $user['id']);
        $saved = $stmt->execute();
        $stmt->close();

        if ($saved) {
            set_flash('Your password has been changed. You can log in with your new password now.');
            header('Location: login.php');
            exit;
        }

        $errors[] = 'Sorry, your password could not be changed. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset password - Smart Water Intake Tracker</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

<div class="auth-box">
    <div class="auth-logo">💧</div>
    <h1 class="auth-title">Smart Water Intake Tracker</h1>
    <p class="auth-subtitle">Choose a new password</p>

    <?php if ($user && !empty($errors)): ?>
        <div class="message message-error">
            <?php foreach ($errors as $error): ?>
                <div><?php echo e($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!$user): ?>
        <p class="auth-text">
            The link you opened cannot be used. It may have expired, already been used,
            or a newer link was requested.
        </p>
        <a class="btn btn-primary btn-block" href="forgot-password.php">Create a new link</a>
    <?php else: ?>
        <p class="auth-text">
            Your link is valid for a few more minutes. Type the new password you want to use.
        </p>

        <form method="post" action="reset-password.php" class="auth-form">
            <input type="hidden" name="token" value="<?php echo e($token); ?>">

            <label for="password">New password</label>
            <input type="password" id="password" name="password"
                   placeholder="At least 6 characters" minlength="6" required>

            <label for="confirm_password">Confirm new password</label>
            <input type="password" id="confirm_password" name="confirm_password"
                   placeholder="Type the new password again" minlength="6" required>

            <button type="submit" class="btn btn-primary btn-block">Change password</button>
        </form>
    <?php endif; ?>

    <p class="auth-switch">
        <a href="login.php">Back to log in</a>
    </p>
</div>

<script src="script.js"></script>
</body>
</html>
