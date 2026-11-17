<?php
// A signed-in officer changes their own password (also the way out of a temporary password).
require 'auth.php';
$me = require_role();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['message' => 'Invalid request method.'], 405);
require_csrf();

$current = $_POST['current'] ?? '';
$new     = $_POST['new'] ?? '';

$stmt = $conn->prepare('SELECT password_hash FROM officers WHERE officer_id = ?');
$stmt->execute([$me['officer_id']]);
$hash = $stmt->get_result()->fetch_assoc()['password_hash'];

if (!password_verify($current, $hash)) {
    audit((int)$me['officer_id'], 'PASSWORD_CHANGE_FAILED');
    respond(['message' => 'Current password is incorrect.'], 422);
}
if (!password_ok($new)) respond(['message' => 'New password needs 10+ characters with a capital letter, a lowercase letter and a number.'], 422);
if (password_verify($new, $hash)) respond(['message' => 'Choose a password different from the current one.'], 422);

$conn->prepare('UPDATE officers SET password_hash = ?, must_change_password = 0 WHERE officer_id = ?')
     ->execute([password_hash($new, PASSWORD_DEFAULT), $me['officer_id']]);
session_regenerate_id(true);
audit((int)$me['officer_id'], 'PASSWORD_CHANGED');
respond(['success' => true]);