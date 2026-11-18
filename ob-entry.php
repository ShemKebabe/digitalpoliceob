<?php
// New OB entry. GET returns the form lookups and the next SRS number; POST saves an entry for the signed-in officer.
require 'auth.php';
$me = require_role();

/** Next SRS number for the current month, e.g. OB/2026/11/004. */
function next_srs(): string
{
    global $conn;
    $prefix = date('Y/m');
    $stmt = $conn->prepare('SELECT MAX(CAST(SUBSTRING_INDEX(srs_number, "/", -1) AS UNSIGNED)) AS n FROM ob_entries WHERE srs_number LIKE ?');
    $stmt->execute(["OB/$prefix/%"]);
    return sprintf('OB/%s/%03d', $prefix, ((int)$stmt->get_result()->fetch_assoc()['n']) + 1);
}

function fail(string $message, int $status = 422): never { respond(['message' => $message], $status); }

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    respond([
        'srs'       => next_srs(),
        'locations' => $conn->query('SELECT location_id AS id, location_name AS name FROM locations ORDER BY location_name')->fetch_all(MYSQLI_ASSOC),
        'types'     => $conn->query('SELECT incident_type_id AS id, type_name AS name FROM incident_types ORDER BY type_name')->fetch_all(MYSQLI_ASSOC),
    ]);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Invalid request method.', 405);
require_csrf();

$when      = DateTime::createFromFormat('!Y-m-d\TH:i', $_POST['when'] ?? '');
$locationId = (int)($_POST['location_id'] ?? 0);
$typeId     = (int)($_POST['type_id'] ?? 0);
$name       = trim($_POST['complainant_name'] ?? '');
$idNo       = trim($_POST['complainant_id_no'] ?? '');
$phone      = trim($_POST['complainant_phone'] ?? '');
$narrative  = trim($_POST['narrative'] ?? '');

if (!$when || $when->getTimestamp() > time() + 300) fail('Enter a valid date and time that is not in the future.');
if (!$locationId || !$typeId)                      fail('Select a location and an incident type.');
if (mb_strlen($name) < 2 || mb_strlen($name) > 100) fail('Enter the complainant\'s full name.');
if ($idNo !== '' && !preg_match('/^[A-Za-z0-9]{5,20}$/', $idNo)) fail('ID number must be 5 to 20 letters or digits.');
if ($phone !== '' && !preg_match('/^\+?[0-9 ]{9,15}$/', $phone)) fail('Enter a valid phone number.');
if (mb_strlen($narrative) < 10 || mb_strlen($narrative) > 5000)   fail('The narrative must be between 10 and 5000 characters.');

// The UNIQUE key on srs_number settles a race between two officers saving at once: retry with the next number.
for ($try = 0; $try < 3; $try++) {
    $srs = next_srs();
    try {
        $conn->prepare('INSERT INTO ob_entries (srs_number, entry_datetime, incident_type_id, location_id, recorded_by, complainant_name, complainant_id_no, complainant_phone, narrative)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
             ->execute([$srs, $when->format('Y-m-d H:i:s'), $typeId, $locationId, $me['officer_id'], $name, $idNo ?: null, $phone ?: null, $narrative]);
        audit((int)$me['officer_id'], 'OB_CREATED', $srs);
        respond(['success' => true, 'srs' => $srs], 201);
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1452) fail('Unknown location or incident type.');
        if ($e->getCode() !== 1062) throw $e;
    }
}
fail('Could not allocate an OB number. Please try again.', 409);