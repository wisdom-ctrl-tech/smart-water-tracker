<?php
// ============================================================
// forgot-password.php  -  Create a password reset link
//
// The visitor types their email address. If an account exists, a
// random token is saved in the users table and a link containing
// that token is shown on the page. XAMPP has no email server, so
// the link is displayed instead of being emailed.
// ============================================================
require_once 'helpers.php';

// Somebody who is already logged in does not need this page.
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$conn      = db();
$errors    = [];
$email     = '';
$message   = '';
$reset_url = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');

    if ($email === '') {
        $errors[] = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address, for example name@example.com.';
    }

    if (empty($errors)) {
        // Is there an account with this address?
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user) {
            $errors[] = 'No account was found with that email address.';
        } else {
            // A long random code. Only its hash is saved in the database.
            $token      = bin2hex(random_bytes(32));
            $token_hash = hash('sha256', $token);
            $expires_at = date('Y-m-d H:i:s', time() + RESET_TOKEN_MINUTES * 60);

            // Saving a new token also cancels any older link.
            $stmt = $conn->prepare('UPDATE users
                                    SET reset_token = ?, reset_expires_at = ?
                                    WHERE id = ?');
            $stmt->bind_param('ssi', $token_hash, $expires_at, $user['id']);
            $saved = $stmt->execute();
            $stmt->close();

            if ($saved) {
                $reset_url = app_base_url() . 'reset-password.php?token=' . urlencode($token);
                $message   = 'A reset link has been created. Open it below to choose a new password. '
                    . 'It works once and expires in ' . RESET_TOKEN_MINUTES . ' minutes.';
            } else {
                $errors[] = 'Sorry, the reset link could not be created. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot password - Smart Water Intake Tracker</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

<div class="auth-box">
    <div class="auth-logo">💧</div>
    <h1 class="auth-title">Smart Water Intake Tracker</h1>
    <p class="auth-subtitle">Forgot your password?</p>

    <?php if (!empty($errors)): ?>
        <div class="message message-error">
            <?php foreach ($errors as $error): ?>
                <div><?php echo e($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($message !== ''): ?>
        <div class="message message-success">
            <?php echo e($message); ?>
            <?php if ($reset_url !== ''): ?>
                <div class="message-link">
                    <a href="<?php echo e($reset_url); ?>">Reset your password</a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($reset_url === ''): ?>
        <p class="auth-text">
            Type the email address you registered with and we will create a link that lets
            you choose a new password.
        </p>

        <form method="post" action="forgot-password.php" class="auth-form">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" value="<?php echo e($email); ?>"
                   placeholder="name@example.com" maxlength="190" required>

            <button type="submit" class="btn btn-primary btn-block">Create reset link</button>
        </form>
    <?php endif; ?>

    <p class="auth-switch">
        <a href="login.php">Back to log in</a>
    </p>
</div>

<script src="script.js"></script>
</body>
</html>
