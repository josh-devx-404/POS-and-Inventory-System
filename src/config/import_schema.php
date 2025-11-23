<?php
// Simple importer for database_schema.sql using mysqli->multi_query
// Usage (CLI):
//   php import_schema.php
// Or visit this file in browser (not recommended on production servers).

// Load DB credentials from db.php (will not create a PDO connection here)
require_once __DIR__ . '/db.php';

$sqlFile = __DIR__ . '/../../database_schema.sql';
if (!file_exists($sqlFile)) {
    echo "SQL file not found: {$sqlFile}\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    echo "Failed to read SQL file.\n";
    exit(1);
}

$host = $DB_HOST ?? 'localhost';
$user = $DB_USER ?? 'root';
$pass = $DB_PASS ?? '';

$mysqli = new mysqli($host, $user, $pass);
if ($mysqli->connect_errno) {
    echo "MySQL connection failed: ({$mysqli->connect_errno}) {$mysqli->connect_error}\n";
    exit(1);
}

// Enable multi statements
$mysqli->multi_query($sql);

$success = true;
while (true) {
    if ($result = $mysqli->store_result()) {
        // free result set
        $result->free();
    } else {
        if ($mysqli->errno) {
            echo "Error executing statements: ({$mysqli->errno}) {$mysqli->error}\n";
            $success = false;
            break;
        }
    }

    if (!$mysqli->more_results()) break;
    $mysqli->next_result();
}

if ($success) {
    echo "Schema import completed successfully.\n";
} else {
    echo "Schema import failed. Check errors above.\n";
}

$mysqli->close();

?>