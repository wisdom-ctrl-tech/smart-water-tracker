<?php
// ============================================================
// forgot-password.php  -  Ask for a password reset link
//
// The visitor types the email address of their account. If the
// address exists, a random one-time token is saved in the
// password_resets table and a link containing that token is
// created. Because XAMPP has no email server, the link is shown
// on the page (see SHOW_RESET_LINK in database.php).
// ============================================================
require_once 'helpers.php';

// Somebody who is already logged in does not need this page.
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$conn   = db();
$errors = [];
$email  = '';
$message      = '';
$message_type = 'success';

/**
 * Builds the full link that the user has to open.
 * Example: http://localhost/smart-water-tracker/reset-password.php?token=...
 */
function reset_link(string $token): string
{
    return app_base_url() . 'reset-password.php?token=' . urlencode($token);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');

    if (!csrf_check()) {
        $errors[] = 'Your session expired. Please try again.';
    }
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

        if ($user) {
            $user_id = (int) $user['id'];
            $now     = date('Y-m-d H:i:s');

            // Old links must stop working as soon as a new one is requested,
            // and expired rows are no longer needed at all.
            $stmt = $conn->prepare('DELETE FROM password_resets WHERE user_id = ? OR expires_at < ?');
            $stmt->bind_param('is', $user_id, $now);
            $stmt->execute();
            $stmt->close();

            // The token the user receives. It is long and random, so it cannot
            // be guessed. Only its hash is stored in the database.
            $token      = bin2hex(random_bytes(32));
            $token_hash = hash('sha256', $token);
            $expires_at = date('Y-m-d H:i:s', time() + RESET_TOKEN_MINUTES * 60);

            $stmt = $conn->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)');
            $stmt->bind_param('iss', $user_id, $token_hash, $expires_at);
            $saved = $stmt->execute();
            $stmt->close();

            if (!$saved) {
                $_SESSION['flash'] = [
                    'text' => 'Sorry, the reset link could not be created. Please try again.',
                    'type' => 'error',
                ];
                header('Location: forgot-password.php');
                exit;
            }

            // Keep the link for the next page.
            if (SHOW_RESET_LINK) {
                $_SESSION['reset_link_url'] = reset_link($token);
            }
        }

        // The same message whether the address exists or not, so this page
        // cannot be used to find out who has an account.
        $_SESSION['flash'] = [
            'text' => 'If an account exists for that email address, a password reset link '
                . 'has been created. It can be used once and expires in '
                . RESET_TOKEN_MINUTES . ' minutes.',
            'type' => 'success',
        ];

        header('Location: forgot-password.php');
        exit;
    }
}

// A message left by the POST above.
if (!empty($_SESSION['flash'])) {
    $message      = $_SESSION['flash']['text'];
    $message_type = isset($_SESSION['flash']['type']) ? $_SESSION['flash']['type'] : 'success';
    unset($_SESSION['flash']);
}

// The link created by the POST above (only when SHOW_RESET_LINK is true).
$reset_url = '';
if (!empty($_SESSION['reset_link_url'])) {
    $reset_url = $_SESSION['reset_link_url'];
    unset($_SESSION['reset_link_url']);
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

    <?php if ($message !== ''): ?>
        <div class="message <?php echo $message_type === 'error' ? 'message-error' : 'message-success'; ?>">
            <?php echo e($message); ?>
            <?php if ($reset_url !== ''): ?>
                <div class="message-link">
                    <a href="<?php echo e($reset_url); ?>">Reset your password</a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="message message-error">
            <?php foreach ($errors as $error): ?>
                <div><?php echo e($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <p class="auth-text">
        Type the email address you registered with and we will create a link that lets
        you choose a new password.
    </p>

    <form method="post" action="forgot-password.php" class="auth-form">
        <?php echo csrf_field(); ?>

        <label for="email">Email address</label>
        <input type="email" id="email" name="email" value="<?php echo e($email); ?>"
               placeholder="name@example.com" maxlength="190" required>

        <button type="submit" class="btn btn-primary btn-block">Create reset link</button>
    </form>

    <p class="auth-switch">
        <a href="login.php">Back to log in</a>
    </p>
</div>

<script src="script.js"></script>
</body>
</html>
