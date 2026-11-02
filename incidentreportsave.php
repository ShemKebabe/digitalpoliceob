<?php
require 'db.php';
session_start();

function newToken(): string
{
    return $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// GET ?token=1 -> give the form a CSRF token
if (isset($_GET['token'])) {
    respond(['token' => newToken()]);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['message' => 'Method not allowed.'], 405);
}

// Honeypot: bots fill the hidden field; pretend success and save nothing
if (!empty($_POST['website'])) {
    respond(['success' => true, 'reference' => 'OB/0000/00/000']);
}
if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf_token'] ?? '')) {
    respond(['message' => 'Your session expired. Please submit again.', 'token' => newToken()], 403);
}

// Rate limit: 5 reports per hour per session
$_SESSION['sent'] = array_filter($_SESSION['sent'] ?? [], fn($t) => $t > time() - 3600);
if (count($_SESSION['sent']) >= 5) {
    respond(['message' => 'Too many reports. Please try again later.'], 429);
}

// Trim every expected field and cap it at its column length
$limits = [
    'full_name' => 100, 'id_number' => 20, 'email' => 120, 'phone' => 16, 'address' => 150,
    'incident_datetime' => 16, 'incident_location' => 100, 'other_location' => 100,
    'incident_type' => 60, 'other_incident' => 60, 'incident_description' => 2000,
];
$in = [];
foreach ($limits as $field => $max) {
    $in[$field] = mb_substr(trim((string)($_POST[$field] ?? '')), 0, $max);
}
$in['phone'] = preg_replace('/[\s-]/', '', $in['phone']);
if ($in['incident_location'] === 'Other') $in['incident_location'] = $in['other_location'];
if ($in['incident_type'] === 'Other')     $in['incident_type']     = $in['other_incident'];

// Validate (keys match the form field ids so the page can show each message)
$when = DateTime::createFromFormat('Y-m-d\TH:i', $in['incident_datetime']);
$errors = array_filter([
    'fName'                => mb_strlen($in['full_name']) < 3 ? 'Please enter your full name.' : null,
    'idNumber'             => $in['id_number'] && !preg_match('/^[A-Za-z0-9]{5,20}$/', $in['id_number']) ? 'Enter a valid ID or passport number.' : null,
    'email'                => $in['email'] && !filter_var($in['email'], FILTER_VALIDATE_EMAIL) ? 'Enter a valid email address.' : null,
    'phone'                => !preg_match('/^(?:\+254|0)[17]\d{8}$/', $in['phone']) ? 'Enter a valid Kenyan phone number.' : null,
    'incident_datetime'    => !$when || $when > new DateTime('+5 minutes') ? 'Enter a valid date and time, not in the future.' : null,
    'incident_location'    => mb_strlen($in['incident_location']) < 3 ? 'Select or specify a location.' : null,
    'incident_type'        => mb_strlen($in['incident_type']) < 3 ? 'Select or specify the incident type.' : null,
    'incident_description' => mb_strlen($in['incident_description']) < 20 ? 'Describe the incident in at least 20 characters.' : null,
]);
if ($errors) {
    respond(['message' => 'Please correct the highlighted fields.', 'errors' => $errors], 422);
}

// Next reference number (OB/YYYY/MM/###). The UNIQUE key on reference_number makes a
// simultaneous duplicate fail with error 1062, so we simply retry with the next number.
$prefix = date('OB/Y/m/');
$stmt = $conn->prepare(
    'INSERT INTO public_reports (reference_number, full_name, id_number, email, phone, address,
                                 incident_datetime, location, incident_type, description, ip_address)
     VALUES (?,?,?,?,?,?,?,?,?,?,?)'
);
for ($try = 0; $try < 3; $try++) {
    $last = $conn->query("SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(reference_number, '/', -1) AS UNSIGNED)), 0)
                          FROM public_reports WHERE reference_number LIKE '$prefix%'")->fetch_row()[0];
    $ref = sprintf('%s%03d', $prefix, $last + 1);
    try {
        $stmt->execute([
            $ref, $in['full_name'], $in['id_number'] ?: null, $in['email'] ?: null, $in['phone'],
            $in['address'] ?: null, $when->format('Y-m-d H:i:s'), $in['incident_location'],
            $in['incident_type'], $in['incident_description'], $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
        $_SESSION['sent'][] = time();
        newToken();                                   // one token per submission
        respond(['success' => true, 'reference' => $ref]);
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() !== 1062) throw $e;        // only retry on duplicate reference
    }
}
respond(['message' => 'Could not save your report. Please try again.'], 500);