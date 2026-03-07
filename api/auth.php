<?php
require_once 'config.php';
require_once 'jwt_helper.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$action = basename($uri, '.php');

// Get sub-action from query string: ?action=login / ?action=refresh / ?action=me
$action = $_GET['action'] ?? 'login';

// ── LOGIN ──
if($method === 'POST' && $action === 'login'){
    $body = json_decode(file_get_contents('php://input'), true);

    $username = trim($body['username'] ?? '');
    $password = trim($body['password'] ?? '');
    $userType = trim($body['user_type'] ?? 'faculty'); // faculty | HOD | HR

    if(!$username || !$password)
        sendError(400, 'Username and password are required');

    // ── Faculty login
    if($userType === 'faculty'){
        $uname = $conn->real_escape_string($username);
        $res   = $conn->query("SELECT id, EmpName, UserName, EmpEmail, Dept, EmpPass, Gender,
                                      SickLeave, CasualLeave, EarnLeave, HodUsername
                               FROM employees WHERE UserName='$uname'");

        if(!$res || $res->num_rows === 0)
            sendError(401, 'Invalid username or password');

        $emp = $res->fetch_assoc();

        // Support both plain text (old) and bcrypt (new) passwords
        $valid = ($password === $emp['EmpPass']) || password_verify($password, $emp['EmpPass']);
        if(!$valid) sendError(401, 'Invalid username or password');

        $token = JWT::encode([
            'id'       => $emp['id'],
            'username' => $emp['UserName'],
            'name'     => $emp['EmpName'],
            'email'    => $emp['EmpEmail'],
            'dept'     => $emp['Dept'],
            'role'     => 'faculty'
        ]);

        sendResponse('success', 'Login successful', [
            'token'     => $token,
            'user_type' => 'faculty',
            'user'      => [
                'id'          => $emp['id'],
                'name'        => $emp['EmpName'],
                'username'    => $emp['UserName'],
                'email'       => $emp['EmpEmail'],
                'dept'        => $emp['Dept'],
                'gender'      => $emp['Gender'],
                'sick_leave'  => $emp['SickLeave'],
                'casual_leave'=> $emp['CasualLeave'],
                'earn_leave'  => $emp['EarnLeave'],
                'hod'         => $emp['HodUsername']
            ]
        ]);
    }

    // ── HOD login
    elseif($userType === 'HOD'){
        $uname = $conn->real_escape_string($username);
        $res   = $conn->query("SELECT id, username, Dept, password, Role
                               FROM admins WHERE username='$uname' AND Role='HOD'");

        if(!$res || $res->num_rows === 0)
            sendError(401, 'Invalid HOD credentials');

        $hod   = $res->fetch_assoc();
        $valid = ($password === $hod['password']);
        if(!$valid) sendError(401, 'Invalid HOD credentials');

        $token = JWT::encode([
            'id'       => $hod['id'],
            'username' => $hod['username'],
            'name'     => $hod['username'],
            'dept'     => $hod['Dept'],
            'role'     => 'HOD'
        ]);

        sendResponse('success', 'HOD login successful', [
            'token'     => $token,
            'user_type' => 'HOD',
            'user'      => [
                'id'      => $hod['id'],
                'username'=> $hod['username'],
                'dept'    => $hod['Dept'],
                'role'    => 'HOD'
            ]
        ]);
    }

    // ── HR login
    elseif($userType === 'HR'){
        $uname = $conn->real_escape_string($username);
        $res   = $conn->query("SELECT id, username FROM hr_users WHERE username='$uname'");

        if(!$res || $res->num_rows === 0)
            sendError(401, 'Invalid HR credentials');

        $hr    = $res->fetch_assoc();
        $res2  = $conn->query("SELECT password FROM hr_users WHERE username='$uname'");
        $hrPass= $res2->fetch_assoc();

        // HR password stored as SHA1
        if(sha1($password) !== $hrPass['password'])
            sendError(401, 'Invalid HR credentials');

        $token = JWT::encode([
            'id'       => $hr['id'],
            'username' => $hr['username'],
            'name'     => $hr['username'],
            'role'     => 'HR'
        ]);

        sendResponse('success', 'HR login successful', [
            'token'     => $token,
            'user_type' => 'HR',
            'user'      => [
                'id'      => $hr['id'],
                'username'=> $hr['username'],
                'role'    => 'HR'
            ]
        ]);
    }

    else {
        sendError(400, 'user_type must be: faculty, HOD, or HR');
    }
}

// ── GET CURRENT USER (me) ──
elseif($method === 'GET' && $action === 'me'){
    require_once 'auth_middleware.php';
    $user = getAuthUser();
    sendResponse('success', 'Authenticated user', $user);
}

// ── REFRESH TOKEN ──
elseif($method === 'POST' && $action === 'refresh'){
    require_once 'auth_middleware.php';
    $user  = getAuthUser();
    $token = JWT::encode([
        'id'       => $user['id'],
        'username' => $user['username'],
        'name'     => $user['name'],
        'role'     => $user['role'],
        'dept'     => $user['dept'] ?? ''
    ]);
    sendResponse('success', 'Token refreshed', ['token' => $token]);
}

else {
    sendError(405, 'Method not allowed. Use ?action=login, ?action=me, ?action=refresh');
}