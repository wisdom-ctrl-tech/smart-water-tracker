<?php
// ============================================================
// scan.php  -  Log water by scanning a container's QR label
//
// A QR label contains a link like:
//   http://192.168.1.10/smart-water-tracker/scan.php?code=SWT-1A2B3C4D
// Opening that link (from a phone camera) logs the container's
// volume. The same page also offers an in-page camera scanner and
// a manual code box as fallbacks.
// ============================================================
require_once 'helpers.php';

$conn = db();

// The code can arrive from a QR link (?code=...), the manual form
// (POST code) or the "Log now" test buttons (POST code).
$raw_code = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw_code = isset($_POST['code']) ? (string) $_POST['code'] : '';
} elseif (isset($_GET['code'])) {
    $raw_code = (string) $_GET['code'];
}

$code = normalize_scan_code($raw_code);

// ------------------------------------------------------------
// Not logged in? Remember the scan, then come back after login.
// ------------------------------------------------------------
if (!is_logged_in()) {
    if ($code !== '') {
        $_SESSION['pending_scan_code'] = $code;
    }
    header('Location: login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// ------------------------------------------------------------
// A code was scanned or typed: log that container's volume.
// ------------------------------------------------------------
if ($code !== '') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_check()) {
        set_flash('Your session expired. Please scan again.', 'error');
        header('Location: scan.php');
        exit;
    }

    // "AND user_id = ?" makes sure a code only works for its owner.
    $stmt = $conn->prepare('SELECT id, name, volume_ml FROM containers
                            WHERE code = ? AND user_id = ? LIMIT 1');
    $stmt->bind_param('si', $code, $user_id);
    $stmt->execute();
    $container = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$container) {
        set_flash('That code does not belong to one of your containers. '
            . 'Check the code and try again.', 'error');
        header('Location: scan.php');
        exit;
    }

    $amount = (int) $container['volume_ml'];

    if (add_water_entry($conn, $user_id, $amount)) {
        set_flash('Logged ' . number_format($amount) . ' ml from your '
            . $container['name'] . '. Well done!');
        header('Location: index.php');
        exit;
    }

    set_flash('Sorry, that water entry could not be saved. Please try again.', 'error');
    header('Location: scan.php');
    exit;
}

// ------------------------------------------------------------
// Show the scan page
// ------------------------------------------------------------
$flash        = take_flash();
$message      = $flash['text'];
$message_type = $flash['type'];

$stmt = $conn->prepare('SELECT id, name, volume_ml, code FROM containers
                        WHERE user_id = ? ORDER BY id');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$containers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$user_name   = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'there';
$active_page = 'scan';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scan - Smart Water Intake Tracker</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'topbar.php'; ?>

<main class="container">

    <section class="welcome-hero">
        <h1 class="welcome">Scan to log water</h1>
        <p class="welcome-sub">
            Point your phone camera at the QR label on your glass or bottle.
            The amount is logged for you.
        </p>
    </section>

    <?php if ($message !== ''): ?>
        <div id="flash-message"
             class="message <?php echo $message_type === 'error' ? 'message-error' : 'message-success'; ?>">
            <?php echo e($message); ?>
        </div>
    <?php endif; ?>

    <div class="grid">

        <!-- ===== Phone camera instructions ===== -->
        <section class="card">
            <h2>Use your phone camera</h2>
            <ol class="steps">
                <li>Open the camera app on your phone.</li>
                <li>Point it at the QR label on the container.</li>
                <li>Tap the link that appears on the screen.</li>
            </ol>
            <p class="card-note">
                Your phone and this computer must be on the same Wi-Fi network.
                If the label does not open, print it again from the
                <a href="containers.php">Containers</a> page using the computer's
                Wi-Fi address instead of <code>localhost</code>.
            </p>
        </section>

        <!-- ===== Camera + manual code ===== -->
        <section class="card">
            <h2>Scan on this device</h2>
            <p class="card-hint">
                This works on <code>localhost</code> or HTTPS. On a phone over
                plain Wi-Fi, use the phone's own camera app instead.
            </p>

            <div class="scanner">
                <video id="scanner-video" class="scanner-video" playsinline muted hidden></video>
                <p id="scanner-status" class="scanner-status"></p>
                <div class="scanner-actions">
                    <button type="button" class="btn btn-primary" id="start-camera">Start camera</button>
                    <button type="button" class="btn btn-water" id="stop-camera" hidden>Stop camera</button>
                </div>
            </div>

            <p class="or-separator"><span>or type the code</span></p>

            <form method="post" action="scan.php" class="custom-form">
                <?php echo csrf_field(); ?>
                <label for="code">Container code</label>
                <div class="inline-fields">
                    <input type="text" id="code" name="code" maxlength="200"
                           placeholder="SWT-1A2B3C4D" autocomplete="off" required>
                    <button type="submit" class="btn btn-primary">Log it</button>
                </div>
                <p class="card-note">The code is printed under the QR square.</p>
            </form>
        </section>

        <!-- ===== The user's containers ===== -->
        <section class="card span-2">
            <h2>Your containers</h2>

            <?php if (empty($containers)): ?>
                <p class="empty">You have no containers yet.</p>
                <p class="card-note">
                    <a class="btn btn-primary" href="containers.php">Add your first container</a>
                </p>
            <?php else: ?>
                <p class="card-hint">
                    Tap a container to log it now. This is handy for testing
                    without a phone.
                </p>

                <div class="container-quick">
                    <?php foreach ($containers as $container): ?>
                        <form method="post" action="scan.php" class="quick-container-form">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="code"
                                   value="<?php echo e($container['code']); ?>">
                            <button type="submit" class="btn btn-water btn-block">
                                <span class="quick-container-name"><?php echo e($container['name']); ?></span>
                                <span class="quick-container-volume"><?php echo number_format((int) $container['volume_ml']); ?> ml</span>
                            </button>
                        </form>
                    <?php endforeach; ?>
                </div>

                <p class="card-note">
                    <a href="containers.php">Manage containers &amp; print QR labels</a>
                </p>
            <?php endif; ?>
        </section>

    </div>
</main>

<script>
    // Optional in-page scanner. It uses the BarcodeDetector API, which is
    // available in Chrome/Edge on localhost or HTTPS. When it is not
    // available the manual code box above still works.
    (function () {
        var startButton = document.getElementById('start-camera');
        var stopButton  = document.getElementById('stop-camera');
        var video       = document.getElementById('scanner-video');
        var status      = document.getElementById('scanner-status');

        if (!startButton || !stopButton || !video || !status) {
            return;
        }

        var stream   = null;
        var timer    = null;
        var detector = null;

        function setStatus(text) {
            status.textContent = text;
        }

        // Hide the button when this browser cannot use the camera directly,
        // for example on a phone opening the app over plain HTTP.
        var canScanHere = ('BarcodeDetector' in window)
            && navigator.mediaDevices
            && typeof navigator.mediaDevices.getUserMedia === 'function';

        if (!canScanHere) {
            startButton.hidden = true;
            setStatus('This browser cannot use the camera directly. Use your phone camera or the code box below.');
        }

        function stopCamera() {
            if (timer !== null) {
                window.clearInterval(timer);
                timer = null;
            }
            if (stream !== null) {
                var tracks = stream.getTracks();
                for (var i = 0; i < tracks.length; i++) {
                    tracks[i].stop();
                }
                stream = null;
            }
            video.srcObject = null;
            video.hidden = true;
            startButton.hidden = false;
            stopButton.hidden = true;
            setStatus('');
        }

        startButton.addEventListener('click', function () {
            if (!('BarcodeDetector' in window)) {
                setStatus('This browser cannot scan QR codes directly. Use the code box below.');
                return;
            }
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                setStatus('The camera is not available. Use the code box below.');
                return;
            }

            try {
                detector = new BarcodeDetector({ formats: ['qr_code'] });
            } catch (error) {
                setStatus('This browser cannot scan QR codes directly. Use the code box below.');
                return;
            }

            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                .then(function (mediaStream) {
                    stream = mediaStream;
                    video.srcObject = stream;
                    video.hidden = false;
                    return video.play();
                })
                .then(function () {
                    startButton.hidden = true;
                    stopButton.hidden = false;
                    setStatus('Point the camera at a container QR code.');

                    timer = window.setInterval(function () {
                        detector.detect(video).then(function (codes) {
                            if (codes.length > 0 && codes[0].rawValue) {
                                var value = codes[0].rawValue;
                                stopCamera();
                                window.location.href = 'scan.php?code=' + encodeURIComponent(value);
                            }
                        }).catch(function () {
                            // Ignore a bad frame and try the next one.
                        });
                    }, 500);
                })
                .catch(function () {
                    setStatus('The camera could not be started. Use the code box below.');
                });
        });

        stopButton.addEventListener('click', stopCamera);
    })();
</script>
<script src="script.js"></script>
</body>
</html>
