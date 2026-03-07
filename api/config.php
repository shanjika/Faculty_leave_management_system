<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if($_SERVER['REQUEST_METHOD'] === 'OPTIONS'){
    http_response_code(200);
    exit;
}

// DB connection
$conn = new mysqli('127.0.0.1', 'root', '', 'leave_management', 3306);
if($conn->connect_error){
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Database connection failed']);
    exit;
}

function sendResponse($status, $message, $data = null){
    $response = ['status' => $status, 'message' => $message];
    if($data !== null) $response['data'] = $data;
    echo json_encode($response);
    exit;
}

function sendError($code, $message){
    http_response_code($code);
    echo json_encode(['status'=>'error','message'=>$message]);
    exit;
}