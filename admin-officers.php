<?php
// Officer management, administrators only. GET lists officers; POST creates, updates or resets a password.
require 'auth.php';
$me = require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    respond(['officers' => $conn->query(
        'SELECT officer_id AS id, service_number, full_name, `rank`, role, status, must_change_password AS pending
         FROM officers ORDER BY service_number'
    )->fetch_all(MYSQLI_ASSOC)]);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['message' => 'Invalid request method.'], 405);
require_csrf();

function fail(string $message, int $status = 422): never { respond(['message' => $message], $status); }

function temp_password(): string
{
    do { $p = substr(strtr(base64_encode(random_bytes(12)), '+/=', 'xyz'), 0, 12); } while (!password_ok($p));
    return $p;
}

function find_officer(int $id): array
{
    global $conn;
    $stmt = $conn->prepare('SELECT officer_id, service_number FROM officers WHERE officer_id = ?');
    $stmt->execute([$id]);
    return $stmt->get_result()->fetch_assoc() ?: fail('Officer not found.', 404);
}

$action = $_POST['action'] ?? '';
$id     = (int)($_POST['id'] ?? 0);
$name   = trim($_POST['full_name'] ?? '');
$rank   = trim($_POST['rank'] ?? '');
$role   = $_POST['role'] ?? '';
$status = $_POST['status'] ?? 'active';

if (in_array($action, ['create', 'update'], true)) {
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100 || mb_strlen($rank) < 2 || mb_strlen($rank) > 40) fail('Enter a name and a rank.');
    if (!in_array($role, ['officer', 'admin'], true) || !in_array($status, ['active', 'inactive'], true)) fail('Invalid role or status.');
}

if ($action === 'create') {
    $number = strtoupper(trim($_POST['service_number'] ?? ''));
    if (!preg_match('/^[A-Z0-9]{3,20}$/', $number)) fail('Service number must be 3 to 20 letters or digits.');
    $temp = temp_password();
    try {
        $conn->prepare('INSERT INTO officers (service_number, full_name, `rank`, role, password_hash, must_change_password) VALUES (?, ?, ?, ?, ?, 1)')
             ->execute([$number, $name, $rank, $role, password_hash($temp, PASSWORD_DEFAULT)]);
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1062) fail('That service number already exists.', 409);
        throw $e;
    }
    audit((int)$me['officer_id'], 'OFFICER_CREATED', $number);
    respond(['success' => true, 'service_number' => $number, 'temp' => $temp], 201);
}

if ($action === 'update') {
    $target = find_officer($id);
    if ($id === (int)$me['officer_id'] && ($role !== 'admin' || $status !== 'active')) fail('You cannot demote or deactivate your own account.');
    $conn->prepare('UPDATE officers SET full_name = ?, `rank` = ?, role = ?, status = ? WHERE officer_id = ?')
         ->execute([$name, $rank, $role, $status, $id]);
    audit((int)$me['officer_id'], 'OFFICER_UPDATED', "{$target['service_number']} role=$role status=$status");
    respond(['success' => true]);
}

if ($action === 'reset') {
    $target = find_officer($id);
    if ($id === (int)$me['officer_id']) fail('Use "Change password" for your own account.');
    $temp = temp_password();
    $conn->prepare('UPDATE officers SET password_hash = ?, must_change_password = 1, failed_logins = 0, locked_until = NULL WHERE officer_id = ?')
         ->execute([password_hash($temp, PASSWORD_DEFAULT), $id]);
    audit((int)$me['officer_id'], 'PASSWORD_RESET', $target['service_number']);
    respond(['success' => true, 'service_number' => $target['service_number'], 'temp' => $temp]);
}

fail('Unknown action.');