<?php
session_start();
header('Content-Type: application/json');

// Guard: refuse if nobody is logged in
if (empty($_SESSION['serviceNumber'])) {
    http_response_code(401);
    echo json_encode(['authenticated' => false, 'message' => 'Not logged in.']);
    exit;
}

$serviceNumber = $_SESSION['serviceNumber'];

// TODO: replace with a real query once the officers/entries tables exist:
//   $stmt = $pdo->prepare("SELECT full_name, rank FROM officers WHERE service_number = ?");
$officerName = 'Sgt. James Mwangi';
$officerRank = 'Sergeant';

// TODO: replace with SELECT ... FROM ob_entries ORDER BY created_at DESC
$recentEntries = [
    ['srs' => 'OB/2026/03/001', 'time' => '08:30', 'type' => 'Theft',     'location' => 'Central',  'status' => 'Open'],
    ['srs' => 'OB/2026/03/002', 'time' => '10:15', 'type' => 'Lost Item', 'location' => 'Kilimani', 'status' => 'Open'],
    ['srs' => 'OB/2026/03/003', 'time' => '22:45', 'type' => 'Assault',   'location' => 'Kasarani', 'status' => 'Under Investigation'],
];

$openToday      = count(array_filter($recentEntries, fn($e) => $e['status'] === 'Open'));
$totalFolios    = count($recentEntries);
$pendingActions = count(array_filter($recentEntries, fn($e) => $e['status'] === 'Under Investigation'));
$mostRecentSrs  = $recentEntries[0]['srs'] ?? '—';

echo json_encode([
    'authenticated' => true,
    'officer' => [
        'name'          => $officerName,
        'rank'          => $officerRank,
        'serviceNumber' => $serviceNumber,
    ],
    'stats' => [
        'openToday'      => $openToday,
        'totalFolios'    => $totalFolios,
        'pendingActions' => $pendingActions,
        'mostRecentSrs'  => $mostRecentSrs,
    ],
    'entries' => $recentEntries,
    'today'   => date('l, j F Y'),
]);