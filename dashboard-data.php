<?php
require 'auth.php';
date_default_timezone_set('Africa/Nairobi');

$officer = current_officer();
if (!$officer) respond(['authenticated' => false, 'message' => 'Not logged in.'], 401);

$stats = $conn->query(
    "SELECT COUNT(*) AS total,
            SUM(status = 'Open' AND DATE(created_at) = CURDATE()) AS open_today,
            SUM(status = 'Under Investigation') AS pending
     FROM ob_entries"
)->fetch_assoc();

$entries = $conn->query(
    "SELECT e.srs_number AS srs, DATE_FORMAT(e.created_at, '%H:%i') AS time,
            t.type_name AS type, l.location_name AS location, e.status
     FROM ob_entries e
     LEFT JOIN incident_types t ON t.incident_type_id = e.incident_type_id
     LEFT JOIN locations l      ON l.location_id = e.location_id
     ORDER BY e.created_at DESC LIMIT 10"
)->fetch_all(MYSQLI_ASSOC);

respond([
    'authenticated' => true,
    'officer' => ['name' => $officer['full_name'], 'rank' => $officer['rank'],
                  'serviceNumber' => $officer['ap_number'], 'role' => $officer['role']],
    'stats' => ['openToday' => (int)$stats['open_today'], 'totalFolios' => (int)$stats['total'],
                'pendingActions' => (int)$stats['pending'], 'mostRecentSrs' => $entries[0]['srs'] ?? '—'],
    'entries' => $entries,
    'today'   => date('l, j F Y'),
]);