<?php
// ============================================================
// register.php  -  Create a new account
// ============================================================
require_once 'helpers.php';

// Somebody who is already logged in does not need this page.
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$conn   = db();
$errors = [];
$name   = '';
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim(isset($_POST['name']) ? $_POST['name'] : '');
    $email   = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $pass    = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // ---- Validation -----------------------------------------------------
    if (!csrf_check()) {
        $errors[] = 'Your session expired. Please fill in the form again.';
    }
    if ($name === '') {
        $errors[] = 'Please enter your full name.';
    } elseif (mb_strlen($name) > 100) {
        $errors[] = 'Your name is too long (100 characters maximum).';
    }
    if ($email === '') {
        $errors[] = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address, for example name@example.com.';
    } elseif (strlen($email) > 190) {
        $errors[] = 'That email address is too long.';
    }
    if ($pass === '') {
        $errors[] = 'Please enter a password.';
    } elseif (strlen($pass) < 6) {
        $errors[] = 'Your password must be at least 6 characters long.';
    }
    if ($confirm !== $pass) {
        $errors[] = 'The two passwords do not match.';
    }

    // ---- Save the account ----------------------------------------------
    if (empty($errors)) {
        // Is the email address already used by somebody else?
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $already_used = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($already_used) {
            $errors[] = 'That email address is already registered. Please log in instead.';
        } else {
            // The password is never stored as plain text.
            $hash = password_hash($pass, PASSWORD_DEFAULT);

            $stmt = $conn->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $name, $email, $hash);

            if ($stmt->execute()) {
                $new_user_id = (int) $conn->insert_id;
                $stmt->close();

                // Every new account starts with two ready-to-scan containers.
                create_container($conn, $new_user_id, 'Glass', 250);
                create_container($conn, $new_user_id, 'Bottle', 750);

                // A scan made before registering belonged to another account.
                unset($_SESSION['pending_scan_code']);

                $_SESSION['flash'] = [
                    'text' => 'Your account was created successfully. You can log in now. '
                        . 'A 250 ml glass and a 750 ml bottle are ready to scan.',
                    'type' => 'success',
                ];
                header('Location: login.php');
                exit;
            }

            $stmt->close();
            $errors[] = 'Sorry, your account could not be created. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - Smart Water Intake Tracker</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

<div class="auth-box">
    <div class="auth-logo">💧</div>
    <h1 class="auth-title">Smart Water Intake Tracker</h1>
    <p class="auth-subtitle">Create your free account</p>

    <?php if (!empty($errors)): ?>
        <div class="message message-error">
            <?php foreach ($errors as $error): ?>
                <div><?php echo e($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="register.php" class="auth-form">
        <?php echo csrf_field(); ?>

        <label for="name">Full name</label>
        <input type="text" id="name" name="name" value="<?php echo e($name); ?>"
               placeholder="e.g. Priya Sharma" maxlength="100" required>

        <label for="email">Email address</label>
        <input type="email" id="email" name="email" value="<?php echo e($email); ?>"
               placeholder="name@example.com" maxlength="190" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password"
               placeholder="At least 6 characters" minlength="6" required>

        <label for="confirm_password">Confirm password</label>
        <input type="password" id="confirm_password" name="confirm_password"
               placeholder="Type your password again" minlength="6" required>

        <button type="submit" class="btn btn-primary btn-block">Register</button>
    </form>

    <p class="auth-switch">
        Already have an account? <a href="login.php">Log in here</a>
    </p>
</div>

<script src="script.js"></script>
</body>
</html>
