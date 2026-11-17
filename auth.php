<?php
// Role gate shared by every protected endpoint.
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

/** The signed-in officer, read fresh from the database (never trust a role sent by the browser). */
function current_officer(): ?array
{
    global $conn;
    if (empty($_SESSION['officer_id'])) return null;
    if (time() - ($_SESSION['last_seen'] ?? 0) > 1800) { $_SESSION = []; return null; }   // 30-minute idle timeout
    $_SESSION['last_seen'] = time();

    $stmt = $conn->prepare('SELECT officer_id, service_number AS ap_number, full_name, `rank`, role, must_change_password FROM officers WHERE officer_id = ? AND status = \'active\'');
    $stmt->execute([$_SESSION['officer_id']]);
    return $stmt->get_result()->fetch_assoc() ?: null;
}

/** Stop with 401/403 unless an officer with one of $roles is signed in. No roles = any signed-in officer. */
function require_role(string ...$roles): array
{
    $officer = current_officer();
    if (!$officer) respond(['message' => 'Please sign in.'], 401);
    if ($roles && !in_array($officer['role'], $roles, true)) respond(['message' => 'You do not have permission.'], 403);
    // A temporary password must be replaced before anything else works.
    if ($officer['must_change_password'] && !in_array(basename($_SERVER['SCRIPT_NAME']), ['password-change.php', 'me.php'], true))
        respond(['message' => 'Change your password first.', 'mustChange' => true], 403);
    return $officer;
}

/** CSRF token for the signed-in session; browsers send it back in the X-CSRF-Token header. */
function csrf_token(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
}

function require_csrf(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) respond(['message' => 'Invalid request token. Reload the page.'], 403);
}