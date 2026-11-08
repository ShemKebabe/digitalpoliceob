<?php
// Shared by every PHP endpoint: database connection + JSON helper.
function respond(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

// Any uncaught error (DB down, SQL error) becomes a clean JSON 500, never a stack trace.
set_exception_handler(fn() => respond(['message' => 'Server error. Please try again.'], 500));

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli('localhost', 'root', '', 'digital_police_ob');
$conn->set_charset('utf8mb4');