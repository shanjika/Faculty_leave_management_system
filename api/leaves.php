<?php
require_once 'config.php';
require_once 'auth_middleware.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET — fetch leaves
if($method === 'GET'){
    $user = getAuthUser();

    // Optional filters
    $status = $_GET['status'] ?? '';
    $type   = $_GET['type']   ?? '';
    $dept   = $_GET['dept']   ?? '';

    $where = "1=1";
    if($status) $where .= " AND el.Status='".$conn->real_escape_string($status)."'";
    if($type)   $where .= " AND el.LeaveType='".$conn->real_escape_string($type)."'";
    if($dept)   $where .= " AND el.Dept='".$conn->real_escape_string($dept)."'";

    // If faculty role — only their own leaves
    if($user['role'] === 'faculty'){
        $empName = $conn->real_escape_string($user['name']);
        $where .= " AND el.EmpName='$empName'";
    }

    $sql = "SELECT el.id, el.EmpName, el.LeaveType, el.LeaveDays,
                   el.StartDate, el.EndDate, el.Status, el.RequestDate,
                   IFNULL(el.FromSession,'') as FromSession,
                   IFNULL(el.ToSession,'')   as ToSession,
                   IFNULL(el.ActivityID,'')  as ActivityID,
                   IFNULL(el.Reason,'')      as Reason,
                   el.Dept
            FROM emp_leaves el
            WHERE $where
            ORDER BY el.RequestDate DESC";

    $res  = $conn->query($sql);
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;

    sendResponse('success', 'Leaves fetched', $rows);
}

// POST — create new leave request
elseif($method === 'POST'){
    $user = getAuthUser();
    $body = json_decode(file_get_contents('php://input'), true);

    $empName   = $conn->real_escape_string($body['emp_name']      ?? '');
    $leaveType = $conn->real_escape_string($body['leave_type']     ?? '');
    $leaveDays = (int)($body['leave_days']                         ?? 0);
    $startDate = $conn->real_escape_string($body['start_date']     ?? '');
    $endDate   = $conn->real_escape_string($body['end_date']       ?? '');
    $dept      = $conn->real_escape_string($body['dept']           ?? '');
    $fromSess  = $conn->real_escape_string($body['from_session']   ?? '');
    $toSess    = $conn->real_escape_string($body['to_session']     ?? '');
    $actId     = $conn->real_escape_string($body['activity_id']    ?? '');
    $reason    = $conn->real_escape_string($body['reason']         ?? '');

    if(!$empName || !$leaveType || !$startDate || !$endDate)
        sendError(400, 'Missing required fields: emp_name, leave_type, start_date, end_date');

    if($leaveDays <= 0) sendError(400, 'leave_days must be greater than 0');

    // Balance check for standard leave types
    $empRes = $conn->query("SELECT * FROM employees WHERE EmpName='$empName'");
    if(!$empRes || $empRes->num_rows === 0) sendError(404, 'Employee not found');
    $emp = $empRes->fetch_assoc();

    if($leaveType === 'Medical Leave' && $leaveDays > (int)$emp['SickLeave'])
        sendError(400, 'Insufficient Medical Leave balance. Available: '.$emp['SickLeave']);
    if($leaveType === 'Casual Leave' && $leaveDays > (int)$emp['CasualLeave'])
        sendError(400, 'Insufficient Casual Leave balance. Available: '.$emp['CasualLeave']);
    if($leaveType === 'Earn Leave' && $leaveDays > (int)$emp['EarnLeave'])
        sendError(400, 'Insufficient Earn Leave balance. Available: '.$emp['EarnLeave']);

    $sql = "INSERT INTO emp_leaves
            (EmpName, LeaveType, LeaveDays, StartDate, EndDate, Dept, FromSession, ToSession, ActivityID, Reason)
            VALUES
            ('$empName','$leaveType','$leaveDays','$startDate','$endDate','$dept','$fromSess','$toSess','$actId','$reason')";

    if($conn->query($sql))
        sendResponse('success', 'Leave request created', ['id' => $conn->insert_id]);
    else
        sendError(500, 'Failed to create leave request');
}

// PUT — update leave status (HOD approve/reject)
elseif($method === 'PUT'){
    $user = getAuthUser();
    $body = json_decode(file_get_contents('php://input'), true);

    $id     = (int)($body['id']     ?? 0);
    $status = $conn->real_escape_string($body['status'] ?? '');

    if(!$id) sendError(400, 'Leave ID is required');
    if(!in_array($status, ['Granted','Rejected'])) sendError(400, 'Status must be Granted or Rejected');

    // Check leave exists
    $leaveRes = $conn->query("SELECT * FROM emp_leaves WHERE id='$id'");
    if(!$leaveRes || $leaveRes->num_rows === 0) sendError(404, 'Leave not found');
    $leave = $leaveRes->fetch_assoc();

    if($status === 'Granted'){
        // Deduct balance
        $empRes = $conn->query("SELECT * FROM employees WHERE EmpName='".$conn->real_escape_string($leave['EmpName'])."'");
        $emp    = $empRes->fetch_assoc();
        $days   = (int)$leave['LeaveDays'];

        if($leave['LeaveType'] === 'Medical Leave'){
            if($days > (int)$emp['SickLeave']) sendError(400, 'Insufficient Medical Leave balance');
            $conn->query("UPDATE employees SET SickLeave=SickLeave-$days WHERE EmpName='".$conn->real_escape_string($leave['EmpName'])."'");
        } elseif($leave['LeaveType'] === 'Casual Leave'){
            if($days > (int)$emp['CasualLeave']) sendError(400, 'Insufficient Casual Leave balance');
            $conn->query("UPDATE employees SET CasualLeave=CasualLeave-$days WHERE EmpName='".$conn->real_escape_string($leave['EmpName'])."'");
        } elseif($leave['LeaveType'] === 'Earn Leave'){
            if($days > (int)$emp['EarnLeave']) sendError(400, 'Insufficient Earn Leave balance');
            $conn->query("UPDATE employees SET EarnLeave=EarnLeave-$days WHERE EmpName='".$conn->real_escape_string($leave['EmpName'])."'");
        }
    }

    $conn->query("UPDATE emp_leaves SET Status='$status' WHERE id='$id'");
    sendResponse('success', 'Leave status updated to '.$status, ['id'=>$id,'status'=>$status]);
}

// DELETE — cancel a pending leave
elseif($method === 'DELETE'){
    $user = getAuthUser();
    $id   = (int)($_GET['id'] ?? 0);
    if(!$id) sendError(400, 'Leave ID is required');

    $leaveRes = $conn->query("SELECT * FROM emp_leaves WHERE id='$id'");
    if(!$leaveRes || $leaveRes->num_rows === 0) sendError(404, 'Leave not found');
    $leave = $leaveRes->fetch_assoc();

    if($leave['Status'] !== 'Requested') sendError(400, 'Only pending leaves can be cancelled');

    $conn->query("DELETE FROM emp_leaves WHERE id='$id' AND Status='Requested'");
    sendResponse('success', 'Leave request cancelled', ['id'=>$id]);
}

else {
    sendError(405, 'Method not allowed');
}