<?php
require_once("auth.php");
require_once("db.php");

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="traffic_data.csv"');

$output = fopen("php://output", "w");
fputcsv($output, ['ID', 'Location', 'Date', 'Time', 'Severity', 'Description']);

$result = $conn->query("SELECT * FROM traffic_data ORDER BY date DESC, time DESC");
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}
fclose($output);
exit();
?>
