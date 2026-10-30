<?php
// ============================================================
// index.php  -  The dashboard (only for logged-in users)
// Shows today's progress, adds water, changes the goal,
// deletes entries and shows a seven-day summary.
// ============================================================
require_once 'helpers.php';

require_login();   // logged-out visitors are sent to login.php

$conn    = db();
$user_id = (int) $_SESSION['user_id'];

// ------------------------------------------------------------
// Handle the three actions that change data (all sent by POST)
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        set_flash('Your session expired. Please reload the page and try again.', 'error');
        header('Location: index.php');
        exit;
    }

    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // ---- 1. Add a water entry -------------------------------------------
    if ($action === 'add') {
        $amount = trim(isset($_POST['amount_ml']) ? $_POST['amount_ml'] : '');

        // Whole numbers only, bigger than zero and not unreasonably large.
        if (!is_numeric($amount) || (int) $amount != (float) $amount
            || (int) $amount <= 0 || (int) $amount > MAX_ENTRY_ML) {
            set_flash('Please enter a whole number of millilitres between 1 and '
                . MAX_ENTRY_ML . '.', 'error');
        } else {
            $amount = (int) $amount;
            $now    = date('Y-m-d H:i:s');

            $stmt = $conn->prepare('INSERT INTO water_logs (user_id, amount_ml, logged_at) VALUES (?, ?, ?)');
            $stmt->bind_param('iis', $user_id, $amount, $now);
            $saved = $stmt->execute();
            $stmt->close();

            if ($saved) {
                set_flash('Added ' . number_format($amount) . ' ml of water. Keep it up!');
            } else {
                set_flash('Sorry, that entry could not be saved. Please try again.', 'error');
            }
        }
    }

    // ---- 2. Delete one of today's entries -------------------------------
    if ($action === 'delete') {
        $log_id = isset($_POST['log_id']) ? (int) $_POST['log_id'] : 0;

        if ($log_id < 1) {
            set_flash('That water entry could not be found.', 'error');
        } else {
            // "AND user_id = ?" makes sure you can only delete your own entries.
            $stmt = $conn->prepare('DELETE FROM water_logs WHERE id = ? AND user_id = ?');
            $stmt->bind_param('ii', $log_id, $user_id);
            $stmt->execute();
            $deleted = $stmt->affected_rows;
            $stmt->close();

            if ($deleted > 0) {
                set_flash('Water entry deleted.');
            } else {
                set_flash('That water entry could not be found.', 'error');
            }
        }
    }

    // ---- 3. Change the daily goal ---------------------------------------
    if ($action === 'goal') {
        $goal = trim(isset($_POST['daily_goal']) ? $_POST['daily_goal'] : '');

        if (!is_numeric($goal) || (int) $goal != (float) $goal
            || (int) $goal < MIN_GOAL_ML || (int) $goal > MAX_GOAL_ML) {
            set_flash('Please enter a daily goal between ' . MIN_GOAL_ML
                . ' and ' . MAX_GOAL_ML . ' ml.', 'error');
        } else {
            $goal = (int) $goal;

            $stmt = $conn->prepare('UPDATE users SET daily_goal = ? WHERE id = ?');
            $stmt->bind_param('ii', $goal, $user_id);
            $stmt->execute();
            $stmt->close();

            set_flash('Your daily goal is now ' . number_format($goal) . ' ml.');
        }
    }

    // After every POST we redirect, so refreshing the page cannot
    // send the same data twice.
    header('Location: index.php');
    exit;
}

// ------------------------------------------------------------
// Read the data we need to display
// ------------------------------------------------------------
$flash        = take_flash();
$message      = $flash['text'];
$message_type = $flash['type'];

// The logged-in user.
$stmt = $conn->prepare('SELECT name, daily_goal FROM users WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {                       // session points to an account that is gone
    session_destroy();
    header('Location: login.php');
    exit;
}

$goal = (int) $user['daily_goal'];
$today = date('Y-m-d');

// Total drunk today.
$stmt = $conn->prepare('SELECT COALESCE(SUM(amount_ml), 0) AS total
                        FROM water_logs
                        WHERE user_id = ? AND DATE(logged_at) = ?');
$stmt->bind_param('is', $user_id, $today);
$stmt->execute();
$today_total = (int) $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Today's entries, newest first.
$stmt = $conn->prepare('SELECT id, amount_ml, logged_at
                        FROM water_logs
                        WHERE user_id = ? AND DATE(logged_at) = ?
                        ORDER BY id DESC');
$stmt->bind_param('is', $user_id, $today);
$stmt->execute();
$entries = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Totals of the last seven days (today included).
$stmt = $conn->prepare('SELECT DATE(logged_at) AS day, SUM(amount_ml) AS total
                        FROM water_logs
                        WHERE user_id = ? AND DATE(logged_at) >= ?
                        GROUP BY DATE(logged_at)');
$week_start = date('Y-m-d', strtotime('-6 days'));
$stmt->bind_param('is', $user_id, $week_start);
$stmt->execute();
$result = $stmt->get_result();

$totals_by_day = [];
while ($row = $result->fetch_assoc()) {
    $totals_by_day[$row['day']] = (int) $row['total'];
}
$stmt->close();

$week = [];
for ($i = 6; $i >= 0; $i--) {
    $day      = date('Y-m-d', strtotime("-$i days"));
    $week[]   = [
        'date'  => $day,
        'label' => date('D', strtotime($day)),
        'total' => isset($totals_by_day[$day]) ? $totals_by_day[$day] : 0,
    ];
}
$week_max   = 1;
$week_total = 0;
foreach ($week as $day) {
    $week_total += $day['total'];
    if ($day['total'] > $week_max) {
        $week_max = $day['total'];
    }
}
foreach ($week as $key => $day) {
    // Height of the bar compared with the best day of the week.
    $height = (int) round($day['total'] / $week_max * 100);
    if ($day['total'] > 0 && $height < 6) {
        $height = 6;               // keep small bars visible
    }
    $week[$key]['height'] = $height;
}

// Progress numbers.
$remaining  = max(0, $goal - $today_total);
$percent    = $goal > 0 ? (int) round($today_total / $goal * 100) : 0;
$bar_width  = min(100, $percent);   // the bar never passes 100%

// Simple recommendation.
if ($today_total >= $goal) {
    $tip       = 'Congratulations! You reached your daily goal of '
        . number_format($goal) . ' ml today. Great work!';
    $tip_class = 'tip-done';
} elseif ($today_total >= $goal / 2) {
    $tip       = 'You are making good progress. Keep going and you will reach your goal.';
    $tip_class = 'tip-good';
} else {
    $tip       = 'Your intake is still low today. Consider drinking some water soon.';
    $tip_class = 'tip-low';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Smart Water Intake Tracker</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php
$user_name   = $user['name'];
$active_page = 'dashboard';
include 'topbar.php';
?>

<main class="container">

    <section class="welcome-hero">
        <h1 class="welcome">
            Welcome back, <?php echo e($user['name']); ?>!
        </h1>
        <p class="welcome-sub">Here is your water intake for today (<?php echo e(date('l, d M Y')); ?>).</p>
    </section>

    <?php if ($message !== ''): ?>
        <div id="flash-message"
             class="message <?php echo $message_type === 'error' ? 'message-error' : 'message-success'; ?>">
            <?php echo e($message); ?>
        </div>
    <?php endif; ?>

    <div class="grid">

        <!-- ===== Daily progress ===== -->
        <section class="card span-2">
            <h2>Today's progress</h2>

            <div class="stats">
                <div class="stat">
                    <span class="stat-value"><?php echo number_format($today_total); ?> ml</span>
                    <span class="stat-label">Consumed today</span>
                </div>
                <div class="stat">
                    <span class="stat-value"><?php echo number_format($goal); ?> ml</span>
                    <span class="stat-label">Daily goal</span>
                </div>
                <div class="stat">
                    <span class="stat-value"><?php echo number_format($remaining); ?> ml</span>
                    <span class="stat-label">Remaining</span>
                </div>
            </div>

            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $bar_width; ?>%;"></div>
            </div>
            <p class="progress-caption"><?php echo $percent; ?>% of your daily goal reached</p>

            <p class="tip <?php echo $tip_class; ?>"><?php echo e($tip); ?></p>
        </section>

        <!-- ===== Add water ===== -->
        <section class="card">
            <h2>Add water</h2>
            <p class="card-hint">
                Scan the QR label on your glass or bottle and the amount is logged
                automatically.
            </p>

            <div class="scan-cta">
                <a class="btn btn-primary btn-block" href="scan.php">📷 Scan a container</a>
                <a class="btn btn-water btn-block" href="containers.php">Manage containers &amp; print QR labels</a>
            </div>

            <p class="or-separator"><span>or add an amount manually</span></p>

            <form method="post" action="index.php" class="quick-buttons">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="add">
                <button type="submit" name="amount_ml" value="250" class="btn btn-water">+ 250 ml</button>
                <button type="submit" name="amount_ml" value="500" class="btn btn-water">+ 500 ml</button>
                <button type="submit" name="amount_ml" value="750" class="btn btn-water">+ 750 ml</button>
            </form>

            <form method="post" action="index.php" class="custom-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="add">
                <label for="amount_ml">Custom amount (ml)</label>
                <div class="inline-fields">
                    <input type="number" id="amount_ml" name="amount_ml" min="1"
                           max="<?php echo MAX_ENTRY_ML; ?>" step="1"
                           placeholder="e.g. 300" required>
                    <button type="submit" class="btn btn-primary">Add</button>
                </div>
            </form>
        </section>

        <!-- ===== Daily goal ===== -->
        <section class="card">
            <h2>Daily goal</h2>
            <p class="card-hint">
                Your goal is <?php echo number_format($goal); ?> ml per day.
            </p>

            <form method="post" action="index.php" class="custom-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="goal">
                <label for="daily_goal">Change your goal (ml)</label>
                <div class="inline-fields">
                    <input type="number" id="daily_goal" name="daily_goal"
                           min="<?php echo MIN_GOAL_ML; ?>" max="<?php echo MAX_GOAL_ML; ?>"
                           step="1" value="<?php echo $goal; ?>" required>
                    <button type="submit" class="btn btn-primary">Save goal</button>
                </div>
                <p class="card-note">
                    Between <?php echo number_format(MIN_GOAL_ML); ?> and
                    <?php echo number_format(MAX_GOAL_ML); ?> ml.
                </p>
            </form>
        </section>

        <!-- ===== Today's history ===== -->
        <section class="card span-2">
            <h2>Today's water history</h2>

            <?php if (empty($entries)): ?>
                <p class="empty">You have not logged any water yet today.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Amount</th>
                            <th>Time</th>
                            <th class="right">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $row_number = count($entries); ?>
                        <?php foreach ($entries as $entry): ?>
                            <tr>
                                <td><?php echo $row_number--; ?></td>
                                <td><?php echo number_format((int) $entry['amount_ml']); ?> ml</td>
                                <td><?php echo e(date('h:i A', strtotime($entry['logged_at']))); ?></td>
                                <td class="right">
                                    <form method="post" action="index.php" class="delete-form">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="log_id"
                                               value="<?php echo (int) $entry['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-small">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <!-- ===== Weekly summary ===== -->
        <section class="card span-2">
            <h2>Last 7 days</h2>

            <div class="week-chart">
                <?php foreach ($week as $day): ?>
                    <div class="week-col">
                        <span class="week-value"><?php echo number_format($day['total']); ?></span>
                        <div class="week-bar-wrap">
                            <div class="week-bar" style="height: <?php echo $day['height']; ?>%;"></div>
                        </div>
                        <span class="week-label"><?php echo e($day['label']); ?></span>
                        <span class="week-date"><?php echo e(date('d M', strtotime($day['date']))); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <p class="week-total">
                Total for the last 7 days:
                <strong><?php echo number_format($week_total); ?> ml</strong>
            </p>
        </section>

    </div>

    <p class="footer-note">
        Your daily goal is a personal target that you can change at any time.
        It is not a medical recommendation.
    </p>
</main>

<script src="script.js"></script>
</body>
</html>
