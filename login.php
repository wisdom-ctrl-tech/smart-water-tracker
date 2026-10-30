<?php
// ============================================================
// login.php  -  Log an existing user in
// ============================================================
require_once 'auth.php';
require_once 'database.php';

// Already logged in? Go straight to the dashboard.
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$conn          = db();
$errors        = [];
$email         = '';
$message       = '';
$message_type  = 'success';

// Set by scan.php when somebody scanned a container while logged out.
$pending_scan  = !empty($_SESSION['pending_scan_code']);

// A message left by register.php, logout.php or reset-password.php.
if (!empty($_SESSION['flash'])) {
    $message      = $_SESSION['flash']['text'];
    $message_type = isset($_SESSION['flash']['type']) ? $_SESSION['flash']['type'] : 'success';
    unset($_SESSION['flash']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $pass  = isset($_POST['password']) ? $_POST['password'] : '';

    if (!csrf_check()) {
        $errors[] = 'Your session expired. Please try again.';
    }
    if ($email === '' || $pass === '') {
        $errors[] = 'Please enter both your email address and your password.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare('SELECT id, name, password FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Same message whether the email or the password was wrong.
        if ($user && password_verify($pass, $user['password'])) {
            session_regenerate_id(true);              // new session id after login
            $_SESSION['user_id']    = (int) $user['id'];
            $_SESSION['user_name']  = $user['name'];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // fresh token

            // If the user scanned a container before logging in, finish that
            // scan now instead of dropping it.
            if (!empty($_SESSION['pending_scan_code'])) {
                $pending = $_SESSION['pending_scan_code'];
                unset($_SESSION['pending_scan_code']);
                header('Location: scan.php?code=' . urlencode($pending));
                exit;
            }

            header('Location: index.php');
            exit;
        }

        $errors[] = 'Incorrect email address or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Smart Water Intake Tracker</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

<div class="auth-box">
    <div class="auth-logo">💧</div>
    <h1 class="auth-title">Smart Water Intake Tracker</h1>
    <p class="auth-subtitle">Log in to your account</p>

    <?php if ($pending_scan): ?>
        <div class="message message-info">
            Log in to record the container you just scanned.
        </div>
    <?php endif; ?>

    <?php if ($message !== ''): ?>
        <div class="message <?php echo $message_type === 'error' ? 'message-error' : 'message-success'; ?>">
            <?php echo e($message); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="message message-error">
            <?php foreach ($errors as $error): ?>
                <div><?php echo e($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="login.php" class="auth-form">
        <?php echo csrf_field(); ?>

        <label for="email">Email address</label>
        <input type="email" id="email" name="email" value="<?php echo e($email); ?>"
               placeholder="name@example.com" required>

        <div class="label-row">
            <label for="password">Password</label>
            <a class="label-link" href="forgot-password.php">Forgot your password?</a>
        </div>
        <input type="password" id="password" name="password"
               placeholder="Your password" required>

        <button type="submit" class="btn btn-primary btn-block">Log in</button>
    </form>

    <p class="auth-switch">
        New here? <a href="register.php">Create an account</a>
    </p>
</div>

<script src="script.js"></script>
</body>
</html>
