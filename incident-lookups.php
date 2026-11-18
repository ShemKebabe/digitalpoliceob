<?php
// Public lookup lists for the report form: the same locations and incident types officers use.
require 'db.php';
$names = fn(string $sql): array => array_column($conn->query($sql)->fetch_all(MYSQLI_NUM), 0);
respond([
    'locations' => $names("SELECT location_name FROM locations ORDER BY location_name = 'Other', location_name"),
    'types'     => $names("SELECT type_name FROM incident_types ORDER BY type_name = 'Other', type_name"),
]);