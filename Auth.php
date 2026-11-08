<?php
// Role gate shared by every protected endpoint.
require_once 'db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

/** The signed-in officer, read fresh from the database (never trust a role sent by the browser). */
function current_officer(): ?array
{
    global $conn;
    if (empty($_SESSION['officer_id'])) return null;

    $stmt = $conn->prepare('SELECT officer_id, ap_number, full_name, `rank`, station, role FROM officers WHERE officer_id = ?');
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