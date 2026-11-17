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

// PHP 8.0 lacks execute(array $params) (added in 8.1). These two small classes add it, so every
// $conn->prepare($sql)->execute([...]) call in the app works on PHP 8.0 and on newer versions.
class Stmt extends mysqli_stmt
{
    public function execute(?array $params = null): bool
    {
        if ($params) $this->bind_param(str_repeat('s', count($params)), ...$params);
        return parent::execute();
    }
}
class DB extends mysqli
{
    public function prepare(string $query): mysqli_stmt|false
    {
        return new Stmt($this, $query);
    }
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$cfg  = require __DIR__ . '/config.php';   // credentials live outside git
$conn = new DB($cfg['host'], $cfg['user'], $cfg['pass'], $cfg['name']);
$conn->set_charset('utf8mb4');

/** Append one row to the audit trail. */
function audit(?int $officerId, string $action, string $details = ''): void
{
    global $conn;
    $conn->prepare('INSERT INTO audit_log (officer_id, action, details) VALUES (?, ?, ?)')
         ->execute([$officerId, $action, $details]);
}

/** Password policy: at least 10 characters with an uppercase letter, a lowercase letter and a digit. */
function password_ok(string $p): bool
{
    return strlen($p) >= 10 && preg_match('/[A-Z]/', $p) && preg_match('/[a-z]/', $p) && preg_match('/\d/', $p);
}