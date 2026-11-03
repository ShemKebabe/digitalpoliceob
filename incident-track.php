<?php
require 'db.php';
session_start();

// Rate limit: 20 lookups per 10 minutes per session (stops reference guessing)
$_SESSION['lookups'] = array_filter($_SESSION['lookups'] ?? [], fn($t) => $t > time() - 600);
if (count($_SESSION['lookups']) >= 20) {
    respond(['found' => false, 'message' => 'Too many searches. Please wait a few minutes.'], 429);
}
$_SESSION['lookups'][] = time();

$ref = strtoupper(trim($_GET['ref'] ?? ''));
if (!preg_match('#^OB/\d{4}/\d{2}/\d{3,5}$#', $ref)) {
    respond(['found' => false, 'message' => 'Invalid reference format.'], 400);
}

$stmt = $conn->prepare(
    'SELECT reference_number, status, incident_type, location, incident_datetime, created_at, remarks
     FROM public_reports WHERE reference_number = ?'
);
$stmt->execute([$ref]);
$r = $stmt->get_result()->fetch_assoc();

if (!$r) {
    respond(['found' => false, 'message' => 'No report found with that reference number.']);
}

// Public result: no names, ID numbers, phone numbers or descriptions
respond([
    'found'             => true,
    'reference'         => $r['reference_number'],
    'status'            => $r['status'],
    'incident_type'     => $r['incident_type'],
    'location'          => $r['location'],
    'incident_datetime' => date('d M Y, H:i', strtotime($r['incident_datetime'])),
    'submitted_at'      => date('d M Y, H:i', strtotime($r['created_at'])),
    'remarks'           => $r['remarks'] ?? '',
]);