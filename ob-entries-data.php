<?php
// Lists OB entries, optionally filtered by ?q= (SRS number, complainant name or ID number).
require 'auth.php';
require_role();

$q = '%' . addcslashes(trim($_GET['q'] ?? ''), '%_\\') . '%';
$stmt = $conn->prepare(
    "SELECT e.srs_number AS srs, DATE_FORMAT(e.incident_datetime, '%e %b, %H:%i') AS time,
            t.type_name AS type, l.location_name AS location, e.complainant_name AS complainant, e.status
     FROM ob_entries e
     LEFT JOIN incident_types t ON t.incident_type_id = e.incident_type_id
     LEFT JOIN locations l      ON l.location_id = e.location_id
     WHERE e.srs_number LIKE ? OR e.complainant_name LIKE ? OR e.complainant_id LIKE ?
     ORDER BY e.incident_datetime DESC LIMIT 100"
);
$stmt->execute([$q, $q, $q]);
respond(['entries' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);