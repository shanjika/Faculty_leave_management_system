<?php
require_once 'config.php';
require_once 'auth_middleware.php';

$method = $_SERVER['REQUEST_METHOD'];

if($method === 'GET'){
    $user = getAuthUser();

    // Leave type breakdown
    $typeRes = $conn->query("SELECT LeaveType, COUNT(*) as total,
        SUM(CASE WHEN Status='Granted'  THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN Status='Rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN Status='Requested' THEN 1 ELSE 0 END) as pending
        FROM emp_leaves GROUP BY LeaveType");
    $types = [];
    while($r = $typeRes->fetch_assoc()) $types[] = $r;

    // Overall counts
    $totRes  = $conn->query("SELECT
        COUNT(*) as total,
        SUM(CASE WHEN Status='Granted'   THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN Status='Rejected'  THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN Status='Requested' THEN 1 ELSE 0 END) as pending
        FROM emp_leaves");
    $totals = $totRes->fetch_assoc();

    // Employee count
    $empRes  = $conn->query("SELECT COUNT(*) as total FROM employees");
    $empCount = $empRes->fetch_assoc()['total'];

    sendResponse('success', 'Stats fetched', [
        'totals'         => $totals,
        'by_leave_type'  => $types,
        'total_employees'=> $empCount
    ]);
}
else {
    sendError(405, 'Method not allowed');
}