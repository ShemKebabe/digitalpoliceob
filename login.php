<?php
session_start();
header('Content-Type: application/json');

$host = '127.0.0.1';
$db   = 'digital_police_ob';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    // Establish database connection using PDO
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Check if data was submitted via POST from the JavaScript Fetch API
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $serviceNumber = trim($_POST['serviceNumber'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validate that fields are not empty
        if (empty($serviceNumber) || empty($password)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Please enter both your Service Number and password.'
            ]);
            exit;
        }

        /* 
          ==================================================
          FUTURE SQL INTEGRATION PROVISION:
          When your users table is ready in phpMyAdmin, 
          uncomment the block below to query your database securely:
          ==================================================
        
          $stmt = $pdo->prepare("SELECT * FROM users WHERE service_number = ?");
          $stmt->execute([$serviceNumber]);
          $userRecord = $stmt->fetch();

          if ($userRecord && password_verify($password, $userRecord['password'])) {
              $_SESSION['serviceNumber'] = $serviceNumber;
              echo json_encode(['success' => true]);
              exit;
          } else {
              echo json_encode([
                  'success' => false, 
                  'message' => 'Invalid Service Number or Password.'
              ]);
              exit;
          }
        */

        // Temporary simulation check until your SQL table is populated:
        if ($serviceNumber === "AP001" && $password === "password123") {
            $_SESSION['serviceNumber'] = $serviceNumber;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Invalid Service Number or Password.'
            ]);
        }
    }
} catch (\PDOException $e) {
    // Catch database connection or query errors and return them as JSON
    echo json_encode([
        'success' => false, 
        'message' => 'Database connection failed. Please check XAMPP MySQL.'
    ]);
}
?>