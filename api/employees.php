<?php
require_once 'config.php';
require_once 'auth_middleware.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET — list all employees
if($method === 'GET'){
    $user = getAuthUser();

    $dept   = $_GET['dept']   ?? '';
    $search = $_GET['search'] ?? '';
    $where  = "1=1";
    if($dept)   $where .= " AND Dept='".$conn->real_escape_string($dept)."'";
    if($search) $where .= " AND (EmpName LIKE '%".$conn->real_escape_string($search)."%' OR UserName LIKE '%".$conn->real_escape_string($search)."%')";

    $res  = $conn->query("SELECT id, EmpName, UserName, EmpEmail, Dept, Designation,
                                 EmpType, SickLeave, CasualLeave, EarnLeave,
                                 HodUsername, DateOfJoin, Gender
                          FROM employees WHERE $where ORDER BY Dept, EmpName");
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;

    sendResponse('success', 'Employees fetched', $rows);
}

// POST — add new employee (HR only)
elseif($method === 'POST'){
    $user = getAuthUser();
    if($user['role'] !== 'HR') sendError(403, 'Only HR can add employees');

    $body   = json_decode(file_get_contents('php://input'), true);
    $name   = $conn->real_escape_string($body['emp_name']    ?? '');
    $uname  = $conn->real_escape_string($body['username']    ?? '');
    $email  = $conn->real_escape_string($body['email']       ?? '');
    $dept   = $conn->real_escape_string($body['dept']        ?? '');
    $desig  = $conn->real_escape_string($body['designation'] ?? '');
    $pass   = $conn->real_escape_string($body['password']    ?? '');
    $gender = $conn->real_escape_string($body['gender']      ?? '');

    if(!$name || !$uname || !$email || !$dept || !$pass)
        sendError(400, 'Missing required fields');

    // Check username exists
    $check = $conn->query("SELECT id FROM employees WHERE UserName='$uname'");
    if($check && $check->num_rows > 0) sendError(409, 'Username already exists');

    $hashedPass = password_hash($body['password'], PASSWORD_BCRYPT);
    $sql = "INSERT INTO employees (EmpName, UserName, EmpEmail, Dept, Designation, EmpPass, Gender, SickLeave, CasualLeave, EarnLeave)
            VALUES ('$name','$uname','$email','$dept','$desig','".$conn->real_escape_string($hashedPass)."','$gender', 10, 10, 10)";

    if($conn->query($sql))
        sendResponse('success', 'Employee added', ['id' => $conn->insert_id]);
    else
        sendError(500, 'Failed to add employee');
}

// DELETE — remove employee (HR only)
elseif($method === 'DELETE'){
    $user = getAuthUser();
    if($user['role'] !== 'HR') sendError(403, 'Only HR can delete employees');

    $id = (int)($_GET['id'] ?? 0);
    if(!$id) sendError(400, 'Employee ID required');

    $check = $conn->query("SELECT id FROM employees WHERE id='$id'");
    if(!$check || $check->num_rows === 0) sendError(404, 'Employee not found');

    $conn->query("DELETE FROM employees WHERE id='$id'");
    sendResponse('success', 'Employee deleted', ['id'=>$id]);
}

else {
    sendError(405, 'Method not allowed');
}