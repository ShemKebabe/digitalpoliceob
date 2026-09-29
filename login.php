<?php
session_start();
header('Content-Type: application/json');

// Flip to true once your users table exists in phpMyAdmin
$USE_DATABASE = false;

// Only accept POST (opening login.php directly in the browser is a GET)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$serviceNumber = trim($_POST['serviceNumber'] ?? '');
$password      = $_POST['password'] ?? '';

if ($serviceNumber === '' || $password === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter both your Service Number and password.'
    ]);
    exit;
}

if (!$USE_DATABASE) {
    // Temporary simulation check - no database needed
    if ($serviceNumber === 'AP001' && $password === 'password123') {
        session_regenerate_id(true);
        $_SESSION['serviceNumber'] = $serviceNumber;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid Service Number or Password.']);
    }
    exit;
}

// ---- Real database login (only runs when $USE_DATABASE = true) ----
$host = '127.0.0.1';
$db   = 'digital_police_ob';
$user = 'root';
$pass = '';
$dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $stmt = $pdo->prepare('SELECT * FROM users WHERE service_number = ?');
    $stmt->execute([$serviceNumber]);
    $userRecord = $stmt->fetch();

    if ($userRecord && password_verify($password, $userRecord['password'])) {
        session_regenerate_id(true);
        $_SESSION['serviceNumber'] = $serviceNumber;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid Service Number or Password.']);
    }
} catch (PDOException $e) {
    error_log($e->getMessage()); // details go to the PHP error log, not the user
    echo json_encode(['success' => false, 'message' => 'Database connection failed. Please check XAMPP MySQL.']);
}