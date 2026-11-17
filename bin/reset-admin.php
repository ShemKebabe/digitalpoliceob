<?php

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../db.php';

$number = strtoupper($argv[1] ?? '');
if ($number === '') exit("Usage: php bin/reset-admin.php <service number>\n");

echo "New password for $number (10+ characters, upper and lower case, a digit): ";
$password = trim((string)fgets(STDIN));
if (!password_ok($password)) exit("Password does not meet the policy.\n");

$stmt = $conn->prepare("UPDATE officers SET password_hash = ?, must_change_password = 1, failed_logins = 0, locked_until = NULL, status = 'active'
                        WHERE service_number = ? AND role = 'admin'");
$stmt->execute([password_hash($password, PASSWORD_DEFAULT), $number]);
if ($stmt->affected_rows !== 1) exit("No administrator with that service number.\n");

audit(null, 'ADMIN_RESET_CLI', $number);
echo "Done. $number must choose a new password at the next sign-in.\n";