<?php
require_once("auth.php");
require_once("db.php");
require_once("../plugins/tcpdf/tcpdf.php");

$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);
$pdf->Write(0, 'Traffic Incident Report', '', 0, 'C', true, 0, false, false, 0);
$pdf->Ln(5);

$html = '<table border="1" cellpadding="4">
    <tr>
        <th><b>ID</b></th>
        <th><b>Location</b></th>
        <th><b>Date</b></th>
        <th><b>Time</b></th>
        <th><b>Severity</b></th>
        <th><b>Description</b></th>
    </tr>';

$result = $conn->query("SELECT * FROM traffic_data ORDER BY date DESC");
while ($row = $result->fetch_assoc()) {
    $html .= "<tr>
        <td>{$row['id']}</td>
        <td>{$row['location']}</td>
        <td>{$row['date']}</td>
        <td>{$row['time']}</td>
        <td>{$row['severity']}</td>
        <td>{$row['description']}</td>
    </tr>";
}
$html .= "</table>";

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('traffic_report.pdf', 'D');
exit();
?>
