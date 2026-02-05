<?php
session_start();
include 'connect.php';

require_once '../admin/dompdf/autoload.inc.php';
use Dompdf\Dompdf;

$id = $_GET['id'];

$sql = "SELECT * FROM emp_leaves WHERE id='$id'";
$res = $conn->query($sql);
$row = $res->fetch_assoc();

$html = "
<h2 style='text-align:center'>Leave Details</h2>
<table border='1' cellpadding='10' width='100%'>
<tr><td><b>Name</b></td><td>{$row['EmpName']}</td></tr>
<tr><td><b>Leave Type</b></td><td>{$row['LeaveType']}</td></tr>
<tr><td><b>Request Date</b></td><td>{$row['RequestDate']}</td></tr>
<tr><td><b>Days</b></td><td>{$row['LeaveDays']}</td></tr>
<tr><td><b>Start</b></td><td>{$row['StartDate']}</td></tr>
<tr><td><b>End</b></td><td>{$row['EndDate']}</td></tr>
<tr><td><b>Status</b></td><td>{$row['Status']}</td></tr>
</table>
";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->render();

$filename = "leave_".$id.".pdf";

header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=$filename");
echo $dompdf->output();
exit;
