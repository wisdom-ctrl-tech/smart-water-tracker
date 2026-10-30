<?php
// ============================================================
// containers.php  -  Manage the bottles and glasses a user can scan
//
// Each container gets a unique code. The QR label printed here
// opens scan.php?code=... on a phone, which logs that container's
// volume as a water entry.
// ============================================================
require_once 'helpers.php';

require_login();

$conn    = db();
$user_id = (int) $_SESSION['user_id'];
$errors  = [];
$name    = '';
$volume  = '';

// ------------------------------------------------------------
// Add or delete a container
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        set_flash('Your session expired. Please reload the page and try again.', 'error');
        header('Location: containers.php');
        exit;
    }

    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // ---- Add ----------------------------------------------------------
    if ($action === 'add') {
        $name   = trim(isset($_POST['name']) ? $_POST['name'] : '');
        $volume = trim(isset($_POST['volume_ml']) ? $_POST['volume_ml'] : '');

        if ($name === '') {
            $errors[] = 'Please give the container a name.';
        } elseif (mb_strlen($name) > 60) {
            $errors[] = 'The container name is too long (60 characters maximum).';
        }

        if (!is_numeric($volume) || (int) $volume != (float) $volume
            || (int) $volume < 1 || (int) $volume > MAX_ENTRY_ML) {
            $errors[] = 'Please enter a whole number of millilitres between 1 and '
                . MAX_ENTRY_ML . '.';
        }

        if (empty($errors)) {
            $volume = (int) $volume;

            if (create_container($conn, $user_id, $name, $volume) > 0) {
                set_flash('Container "' . $name . '" added. Its QR label is ready below.');
            } else {
                set_flash('Sorry, the container could not be saved. Please try again.', 'error');
            }

            header('Location: containers.php');
            exit;
        }
    }

    // ---- Delete -------------------------------------------------------
    if ($action === 'delete') {
        $container_id = isset($_POST['container_id']) ? (int) $_POST['container_id'] : 0;

        // "AND user_id = ?" makes sure you can only delete your own containers.
        $stmt = $conn->prepare('DELETE FROM containers WHERE id = ? AND user_id = ?');
        $stmt->bind_param('ii', $container_id, $user_id);
        $stmt->execute();
        $deleted = $stmt->affected_rows;
        $stmt->close();

        if ($deleted > 0) {
            set_flash('Container deleted.');
        } else {
            set_flash('That container could not be found.', 'error');
        }

        header('Location: containers.php');
        exit;
    }
}

$flash        = take_flash();
$message      = $flash['text'];
$message_type = $flash['type'];

// The user's containers, oldest first.
$stmt = $conn->prepare('SELECT id, name, volume_ml, code FROM containers
                        WHERE user_id = ? ORDER BY id');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$containers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// A phone cannot open "localhost", so warn when the labels would point there.
$show_lan_warning = app_is_local_host() && APP_BASE_URL === '';

$user_name   = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'there';
$active_page = 'containers';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Containers - Smart Water Intake Tracker</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="labels-page">

<?php include 'topbar.php'; ?>

<main class="container">

    <section class="welcome-hero no-print">
        <h1 class="welcome">Your containers</h1>
        <p class="welcome-sub">
            Add a bottle or glass, then print its QR label. Scanning the label
            logs that container's volume without typing.
        </p>
    </section>

    <?php if ($message !== ''): ?>
        <div id="flash-message"
             class="message <?php echo $message_type === 'error' ? 'message-error' : 'message-success'; ?> no-print">
            <?php echo e($message); ?>
        </div>
    <?php endif; ?>

    <!-- ===== Add a container ===== -->
    <section class="card no-print">
        <h2>Add a container</h2>
        <p class="card-hint">
            Give it a name and the amount it holds. A 250 ml glass and a 750 ml
            bottle are already set up for new accounts.
        </p>

        <?php if (!empty($errors)): ?>
            <div class="message message-error">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="containers.php" class="container-form">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="add">

            <div class="form-row">
                <div class="form-field">
                    <label for="name">Container name</label>
                    <input type="text" id="name" name="name" maxlength="60"
                           value="<?php echo e($name); ?>"
                           placeholder="e.g. Blue bottle" required>
                </div>
                <div class="form-field">
                    <label for="volume_ml">Volume (ml)</label>
                    <input type="number" id="volume_ml" name="volume_ml" min="1"
                           max="<?php echo MAX_ENTRY_ML; ?>" step="1"
                           value="<?php echo e($volume); ?>"
                           placeholder="e.g. 750" required>
                </div>
                <div class="form-submit">
                    <button type="submit" class="btn btn-primary">Add container</button>
                </div>
            </div>
        </form>
    </section>

    <!-- ===== QR labels ===== -->
    <section class="card">
        <div class="section-head no-print">
            <h2>QR labels</h2>
            <?php if (!empty($containers)): ?>
                <button type="button" class="btn btn-primary" id="print-labels">🖨 Print labels</button>
            <?php endif; ?>
        </div>

        <p class="card-hint no-print">
            Print this page and stick a label on each container. A phone camera
            opens the app and logs the container's volume.
            Labels point to <code><?php echo e(app_base_url()); ?></code>.
        </p>

        <?php if ($show_lan_warning): ?>
            <div class="notice no-print">
                <strong>Phones cannot open "localhost".</strong>
                Open this page using your computer's Wi-Fi address (for example
                <code>http://192.168.1.10/smart-water-tracker/containers.php</code>)
                before printing, or set <code>APP_BASE_URL</code> in
                <code>database.php</code> to that address. Then print the labels again.
            </div>
        <?php endif; ?>

        <?php if (empty($containers)): ?>
            <p class="empty">You have no containers yet. Add one above to create its QR label.</p>
        <?php else: ?>
            <div class="qr-grid">
                <?php foreach ($containers as $container): ?>
                    <?php
                    $scan_url = app_base_url() . 'scan.php?code=' . urlencode($container['code']);
                    ?>
                    <article class="qr-card">
                        <div class="qr-code"
                             data-qr="<?php echo e($scan_url); ?>"
                             data-label="<?php echo e($container['name']); ?>"></div>

                        <div class="qr-info">
                            <div class="qr-name"><?php echo e($container['name']); ?></div>
                            <div class="qr-volume"><?php echo number_format((int) $container['volume_ml']); ?> ml</div>
                            <div class="qr-code-text"><?php echo e($container['code']); ?></div>
                            <div class="qr-hint">Scan to log water</div>
                        </div>

                        <div class="qr-actions no-print">
                            <a class="btn btn-small btn-water"
                               href="scan.php?code=<?php echo urlencode($container['code']); ?>">Log now (test)</a>
                            <form method="post" action="containers.php" class="delete-form"
                                  data-confirm="Delete this container and its QR label?">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="container_id"
                                       value="<?php echo (int) $container['id']; ?>">
                                <button type="submit" class="btn btn-small btn-danger">Delete</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <p class="footer-note no-print">
        Keep the labels dry. If a code is ever copied by somebody else, delete the
        container and add it again to get a new code.
    </p>
</main>

<script src="assets/qrcode.js"></script>
<script>
    // Draw a QR code inside every .qr-code box. The library is bundled in
    // assets/qrcode.js, so this works without an internet connection.
    (function () {
        var boxes = document.querySelectorAll('.qr-code');

        for (var i = 0; i < boxes.length; i++) {
            var box = boxes[i];
            var url = box.getAttribute('data-qr');

            if (!url || typeof qrcode !== 'function') {
                continue;
            }

            var qr = qrcode(0, 'M');
            qr.addData(url);
            qr.make();

            box.innerHTML = qr.createSvgTag({
                cellSize: 4,
                margin: 8,
                scalable: true,
                alt: { text: 'QR code for ' + (box.getAttribute('data-label') || 'container') }
            });
        }

        var printButton = document.getElementById('print-labels');
        if (printButton) {
            printButton.addEventListener('click', function () {
                window.print();
            });
        }
    })();
</script>
<script src="script.js"></script>
</body>
</html>
