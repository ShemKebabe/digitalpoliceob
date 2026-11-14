<?php
// Shared bootstrap: security headers, session hardening, database connection, JSON + audit helpers.
function respond(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

// Log the real error server-side; the client only sees a generic message (A05, A09).
set_exception_handler(function (Throwable $e) {
    error_log($e);
    respond(['message' => 'Server error. Please try again.'], 500);
});

header_remove('X-Powered-By');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
header('Cache-Control: no-store');

ini_set('session.use_strict_mode', '1');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict', 'secure' => !empty($_SERVER['HTTPS'])]);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$cfg  = require __DIR__ . '/config.php';   // credentials live outside git
$conn = new mysqli($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['name']);
$conn->set_charset('utf8mb4');

/** Append one row to the audit trail. */
function audit(?int $officerId, string $action, string $details = ''): void
{
    global $conn;
    $conn->prepare('INSERT INTO audit_log (officer_id, action, details) VALUES (?, ?, ?)')
         ->execute([$officerId, $action, $details]);
}