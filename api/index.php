<?php
require_once 'config.php';

sendResponse('success', 'Leave Management API', [
    'version' => '1.0',
    'endpoints' => [
        'POST   /api/auth.php?action=login'    => 'Login (faculty/HOD/HR)',
        'GET    /api/auth.php?action=me'        => 'Get current user (JWT required)',
        'POST   /api/auth.php?action=refresh'   => 'Refresh token (JWT required)',
        'GET    /api/leaves.php'                => 'Get all leaves (JWT required)',
        'POST   /api/leaves.php'                => 'Create leave request (JWT required)',
        'PUT    /api/leaves.php'                => 'Approve or Reject leave (JWT required)',
        'DELETE /api/leaves.php?id=1'           => 'Cancel pending leave (JWT required)',
        'GET    /api/employees.php'             => 'Get all employees (JWT required)',
        'POST   /api/employees.php'             => 'Add employee - HR only (JWT required)',
        'DELETE /api/employees.php?id=1'        => 'Delete employee - HR only (JWT required)',
        'GET    /api/stats.php'                 => 'Get leave statistics (JWT required)',
    ],
    'auth_instructions' => [
        'step_1' => 'POST /api/auth.php?action=login with {username, password, user_type}',
        'step_2' => 'Copy the token from response',
        'step_3' => 'Add header to all requests: Authorization: Bearer <token>'
    ]
]);
```

---

**How to test in Postman/Thunder Client:**

**Login:**
```
POST http://localhost/Leave-Management-System-Project-in-PHP-master/api/auth.php?action=login
Body (JSON):
{
  "username": "faculty_username",
  "password": "faculty_password",
  "user_type": "faculty"
}
```

**Use token on protected routes:**
```
GET http://localhost/Leave-Management-System-Project-in-PHP-master/api/leaves.php
Headers:
  Authorization: Bearer eyJ0eXAiOiJKV1Q...