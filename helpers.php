<?php
// ============================================================
// helpers.php
// Small functions that are shared by more than one page.
// Every page that includes this file also gets auth.php and
// database.php, so it is safe to use db(), e() and $_SESSION.
// ============================================================

require_once 'auth.php';
require_once 'database.php';

/**
 * The address of the app, always ending with a slash.
 * Example: http://192.168.1.66/smart-water-tracker/
 */
function app_base_url(): string
{
    return rtrim(APP_BASE_URL, '/') . '/';
}

/** Remembers a one-time message that is shown after the next redirect. */
function set_flash(string $text, string $type = 'success'): void
{
    $_SESSION['flash'] = ['text' => $text, 'type' => $type];
}

/** Reads and clears the one-time message. */
function take_flash(): array
{
    if (empty($_SESSION['flash'])) {
        return ['text' => '', 'type' => 'success'];
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return [
        'text' => isset($flash['text']) ? $flash['text'] : '',
        'type' => isset($flash['type']) ? $flash['type'] : 'success',
    ];
}

/** Saves one water entry for a user. */
function add_water_entry(mysqli $conn, int $user_id, int $amount_ml): bool
{
    $now  = date('Y-m-d H:i:s');
    $stmt = $conn->prepare('INSERT INTO water_logs (user_id, amount_ml, logged_at) VALUES (?, ?, ?)');
    $stmt->bind_param('iis', $user_id, $amount_ml, $now);
    $saved = $stmt->execute();
    $stmt->close();

    return $saved;
}

/** Creates a random code that no container is using yet. */
function make_container_code(mysqli $conn): string
{
    do {
        $code = 'SWT-' . strtoupper(bin2hex(random_bytes(4)));

        $stmt = $conn->prepare('SELECT id FROM containers WHERE code = ? LIMIT 1');
        $stmt->bind_param('s', $code);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } while ($exists);

    return $code;
}

/** Adds a container for a user. Returns the new id, or 0 on failure. */
function create_container(mysqli $conn, int $user_id, string $name, int $volume_ml): int
{
    $code = make_container_code($conn);

    $stmt = $conn->prepare('INSERT INTO containers (user_id, name, volume_ml, code) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('isis', $user_id, $name, $volume_ml, $code);
    $saved = $stmt->execute();
    $id    = $saved ? (int) $conn->insert_id : 0;
    $stmt->close();

    return $id;
}

/**
 * Turns whatever a scanner returned into a container code.
 * It accepts a plain code (SWT-1A2B3C4D) or a full URL
 * (http://.../scan.php?code=SWT-1A2B3C4D).
 */
function normalize_scan_code(string $raw): string
{
    $raw = trim($raw);
    if ($raw === '') {
        return '';
    }

    if (stripos($raw, 'code=') !== false) {
        $query = parse_url($raw, PHP_URL_QUERY);
        if (is_string($query)) {
            parse_str($query, $params);
            if (!empty($params['code']) && is_string($params['code'])) {
                $raw = $params['code'];
            }
        }
    }

    return strtoupper(trim($raw));
}
