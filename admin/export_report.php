<?php
// admin/export_report.php
// Generates PDF (monthly) or CSV (department) reports. Uses dompdf for PDF.
session_start();
include '../admin/connect.php';
if(!isset($_SESSION['adminuser'])){ header('Location: index.php?err='.urlencode('Please Login First')); exit(); }

$type = isset($_GET['type']) ? $_GET['type'] : 'monthly';
$format = isset($_GET['format']) ? $_GET['format'] : 'pdf';

if($type === 'monthly'){
    // default: current month
    $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
    $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
    $start = "$year-".str_pad($month,2,'0',STR_PAD_LEFT)."-01";
    $end = date('Y-m-t', strtotime($start));
    $sql = "SELECT * FROM emp_leaves WHERE RequestDate BETWEEN '$start' AND '$end'";
    $res = $conn->query($sql);
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;

    if($format === 'csv'){
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="monthly_report_'.$year.'_'.$month.'.csv"');
        $out = fopen('php://output','w');
        fputcsv($out, array('EmpName','LeaveType','LeaveDays','StartDate','EndDate','Status','RequestDate','Dept'));
        foreach($rows as $rr) fputcsv($out, [$rr['EmpName'],$rr['LeaveType'],$rr['LeaveDays'],$rr['StartDate'],$rr['EndDate'],$rr['Status'],$rr['RequestDate'],$rr['Dept']]);
        fclose($out); exit();
    }

    // PDF via dompdf
    require_once '../admin/dompdf_config.inc.php';
    $html = '<h1>Monthly Leave Report - '.htmlspecialchars($month).'/'.htmlspecialchars($year).'</h1>';
    $html .= '<table border="1" cellpadding="6" cellspacing="0"><tr><th>Name</th><th>Type</th><th>Days</th><th>Start</th><th>End</th><th>Status</th><th>Dept</th></tr>';
    foreach($rows as $rr){
        $html .= '<tr><td>'.htmlspecialchars($rr['EmpName']).'</td><td>'.htmlspecialchars($rr['LeaveType']).'</td><td>'.htmlspecialchars($rr['LeaveDays']).'</td><td>'.htmlspecialchars($rr['StartDate']).'</td><td>'.htmlspecialchars($rr['EndDate']).'</td><td>'.htmlspecialchars($rr['Status']).'</td><td>'.htmlspecialchars($rr['Dept']).'</td></tr>';
    }
    $html .= '</table>';
    $pdf = createPDF($html, 'monthly_report_'.$year.'_'.$month.'.pdf');
    if($pdf) exit();
}

if($type === 'department'){
    $dept = isset($_GET['dept']) ? $conn->real_escape_string($_GET['dept']) : '';
    $sql = "SELECT * FROM emp_leaves" . ($dept ? " WHERE Dept='$dept'" : "");
    $res = $conn->query($sql);
    if($format === 'csv'){
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="department_report_'.($dept?$dept:'all').'.csv"');
        $out = fopen('php://output','w');
        fputcsv($out, array('EmpName','LeaveType','LeaveDays','StartDate','EndDate','Status','RequestDate','Dept'));
        while($r = $res->fetch_assoc()) fputcsv($out, [$r['EmpName'],$r['LeaveType'],$r['LeaveDays'],$r['StartDate'],$r['EndDate'],$r['Status'],$r['RequestDate'],$r['Dept']]);
        fclose($out); exit();
    }
    // PDF
    require_once '../admin/dompdf_config.inc.php';
    $html = '<h1>Department Report - '.htmlspecialchars($dept).'</h1>';
    $html .= '<table border="1" cellpadding="6" cellspacing="0"><tr><th>Name</th><th>Type</th><th>Days</th><th>Start</th><th>End</th><th>Status</th><th>Dept</th></tr>';
    while($r = $res->fetch_assoc()){
        $html .= '<tr><td>'.htmlspecialchars($r['EmpName']).'</td><td>'.htmlspecialchars($r['LeaveType']).'</td><td>'.htmlspecialchars($r['LeaveDays']).'</td><td>'.htmlspecialchars($r['StartDate']).'</td><td>'.htmlspecialchars($r['EndDate']).'</td><td>'.htmlspecialchars($r['Status']).'</td><td>'.htmlspecialchars($r['Dept']).'</td></tr>';
    }
    $html .= '</table>';
    $pdf = createPDF($html, 'department_report_'.($dept?$dept:'all').'.pdf');
    if($pdf) exit();
}

// fallback
header('Location: home.php');
?>
