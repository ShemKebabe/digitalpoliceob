<?php
// Shared by every PHP endpoint: session settings, database connection, JSON helper.
function respond(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

// Log the real error server-side; the client only sees a generic message.
set_exception_handler(function (Throwable $e) {
    error_log($e);
    respond(['message' => 'Server error. Please try again.'], 500);
});

session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict']);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli(getenv('DB_HOST') ?: 'localhost', getenv('DB_USER') ?: 'root', getenv('DB_PASS') ?: '', 'digital_police_ob');
$conn->set_charset('utf8mb4');