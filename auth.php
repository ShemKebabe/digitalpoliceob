<?php
// Role gate shared by every protected endpoint.
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

/** The signed-in officer, read fresh from the database (never trust a role sent by the browser). */
function current_officer(): ?array
{
    global $conn;
    if (empty($_SESSION['officer_id'])) return null;
    if (time() - ($_SESSION['last_seen'] ?? 0) > 1800) { $_SESSION = []; return null; }   // 30-minute idle timeout
    $_SESSION['last_seen'] = time();

    $stmt = $conn->prepare('SELECT officer_id, service_number AS ap_number, full_name, `rank`, role FROM officers WHERE officer_id = ? AND status = \'active\'');
    $stmt->execute([$_SESSION['officer_id']]);
    return $stmt->get_result()->fetch_assoc() ?: null;
}

/** Stop with 401/403 unless an officer with one of $roles is signed in. No roles = any signed-in officer. */
function require_role(string ...$roles): array
{
    $officer = current_officer();
    if (!$officer) respond(['message' => 'Please sign in.'], 401);
    if ($roles && !in_array($officer['role'], $roles, true)) respond(['message' => 'You do not have permission.'], 403);
    return $officer;
}