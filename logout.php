<?php
require 'db.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(['message' => 'Invalid request method.'], 405);

audit($_SESSION['officer_id'] ?? null, 'LOGOUT');
$_SESSION = [];
setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Strict']);
session_destroy();
respond(['success' => true]);