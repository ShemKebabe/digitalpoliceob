<?php
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['success' => false, 'message' => 'Invalid request method.'], 405);
}

$serviceNumber = trim($_POST['serviceNumber'] ?? '');
$password      = $_POST['password'] ?? '';
if ($serviceNumber === '' || $password === '') {
    respond(['success' => false, 'message' => 'Please enter both your Service Number and password.']);
}

$stmt = $conn->prepare('SELECT officer_id, password_hash FROM officers WHERE ap_number = ?');
$stmt->execute([$serviceNumber]);
$officer = $stmt->get_result()->fetch_assoc();

if (!$officer || !password_verify($password, $officer['password_hash'])) {
    respond(['success' => false, 'message' => 'Invalid Service Number or Password.']);
}

session_regenerate_id(true);
$_SESSION['officer_id']    = $officer['officer_id'];
$_SESSION['serviceNumber'] = $serviceNumber;   // kept so your existing pages keep working

$conn->prepare("INSERT INTO audit_log (officer_id, action, details) VALUES (?, 'LOGIN', 'Officer logged in')")
     ->execute([$officer['officer_id']]);

respond(['success' => true]);