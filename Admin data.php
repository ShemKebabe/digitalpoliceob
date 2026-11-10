<?php
// Data for admin.html. Administrators only.
require 'auth.php';
require_role('admin');

// One failing query must not blank the whole page: log it and send null for that widget.
function section(callable $query)
{
    try {
        return $query();
    } catch (mysqli_sql_exception $e) {
        error_log('admin-data: ' . $e->getMessage());
        return null;
    }
}
function count_of(string $sql): int
{
    global $conn;
    return (int)$conn->query($sql)->fetch_row()[0];
}
function rows_of(string $sql): array
{
    global $conn;
    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

respond([
    'stats' => [
        'records'  => section(fn() => count_of('SELECT COUNT(*) FROM ob_entries')),
        'officers' => section(fn() => count_of('SELECT COUNT(*) FROM officers')),
        'public'   => section(fn() => count_of('SELECT COUNT(*) FROM public_reports')),
        'audit'    => section(fn() => count_of('SELECT COUNT(*) FROM audit_log')),
    ],
    'status' => section(fn() => rows_of('SELECT status AS label, COUNT(*) AS total FROM ob_entries GROUP BY status ORDER BY total DESC')),
    'types'  => section(fn() => rows_of(
        'SELECT t.type_name AS label, COUNT(*) AS total
         FROM ob_entries e JOIN incident_types t ON t.incident_type_id = e.incident_type_id
         GROUP BY t.type_name ORDER BY total DESC LIMIT 10')),
    'officers' => section(fn() => rows_of('SELECT ap_number, full_name, `rank`, station, role FROM officers ORDER BY ap_number')),
    'audit'    => section(fn() => rows_of(
        'SELECT a.action, a.details, a.created_at, o.full_name
         FROM audit_log a LEFT JOIN officers o ON o.officer_id = a.officer_id
         ORDER BY a.created_at DESC LIMIT 10')),
]);