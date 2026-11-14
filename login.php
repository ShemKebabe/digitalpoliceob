<?php
require 'db.php';
session_start();

const MAX_FAILS = 5, LOCK_MINUTES = 15;
const DUMMY_HASH = '$2y$10$I74jPNJKMoxe/raCBxUGuOHA0r4bwNXTttdTi/5W1HUxcA292S1eu';   // verified when the account is unknown, so response time does not reveal valid numbers

if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['success' => false, 'message' => 'Invalid request method.'], 405);

$number   = substr(trim($_POST['serviceNumber'] ?? ''), 0, 20);
$password = $_POST['password'] ?? '';
if ($number === '' || $password === '') respond(['success' => false, 'message' => 'Please enter both your Service Number and password.'], 422);

$stmt = $conn->prepare("SELECT officer_id, password_hash, locked_until FROM officers WHERE service_number = ? AND status = 'active'");
$stmt->execute([$number]);
$officer = $stmt->get_result()->fetch_assoc();

$locked = $officer && $officer['locked_until'] && strtotime($officer['locked_until']) > time();
$valid  = password_verify($password, $officer['password_hash'] ?? DUMMY_HASH) && $officer && !$locked;

if (!$valid) {
    if ($officer && !$locked) {
        $conn->prepare('UPDATE officers
                        SET locked_until  = IF(failed_logins >= ? - 1, NOW() + INTERVAL ? MINUTE, locked_until),
                            failed_logins = IF(failed_logins >= ? - 1, 0, failed_logins + 1)
                        WHERE officer_id = ?')
             ->execute([MAX_FAILS, LOCK_MINUTES, MAX_FAILS, $officer['officer_id']]);
    }
    audit($officer['officer_id'] ?? null, 'LOGIN_FAILED', $number);
    // One message for every failure: unknown number, wrong password and locked account look identical.
    respond(['success' => false, 'message' => 'Invalid Service Number or Password, or account temporarily locked.'], 401);
}

$conn->prepare('UPDATE officers SET failed_logins = 0, locked_until = NULL WHERE officer_id = ?')->execute([$officer['officer_id']]);
session_regenerate_id(true);                       // new session ID after login (session fixation)
$_SESSION['officer_id']    = $officer['officer_id'];
$_SESSION['serviceNumber'] = $number;
$_SESSION['last_seen']     = time();
audit($officer['officer_id'], 'LOGIN', 'Officer logged in');
respond(['success' => true]);