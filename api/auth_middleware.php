<?php
require_once 'jwt_helper.php';

function getAuthUser() {
    $headers = getallheaders();

    // Look for Authorization: Bearer <token>
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    if(empty($authHeader))
        sendError(401, 'Authorization header missing. Use: Authorization: Bearer <token>');

    if(!str_starts_with($authHeader, 'Bearer '))
        sendError(401, 'Invalid Authorization format. Use: Bearer <token>');

    $token = substr($authHeader, 7);
    $data  = JWT::decode($token);

    if(!$data)
        sendError(401, 'Invalid or expired token. Please login again.');

    return $data;
}