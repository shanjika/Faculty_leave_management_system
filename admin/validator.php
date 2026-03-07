<?php
session_start();
include 'connect.php';

$username = trim($_POST['uname']);
$password = trim($_POST['pass']);

// Check HR users first
$sqlHR = "SELECT * FROM hr_users WHERE username='".$conn->real_escape_string($username)."'";
$resHR = $conn->query($sqlHR);
if($resHR && $resHR->num_rows > 0){
    $rowHR = $resHR->fetch_assoc();
    // HR password stored as SHA1
    if($rowHR['password'] === sha1($password)){
        $_SESSION['adminuser'] = $username;
        $_SESSION['role']      = 'HR';
        $_SESSION['dept']      = 'ALL';
        header('location:home.php');
        exit;
    }
}

// Check HOD admins — original system stores password as plain text or its own hash
// Use same comparison as original: direct match
$sql    = "SELECT * FROM admins";
$result = $conn->query($sql);
while($row = $result->fetch_assoc()){
    if($username === $row['username'] && $password === $row['password']){
        $_SESSION['adminuser'] = $username;
        $_SESSION['role']      = !empty($row['Role']) ? $row['Role'] : 'HOD';
        $_SESSION['dept']      = $row['Dept'];
        header('location:home.php');
        exit;
    }
}

header('location:index.php?err='.urlencode('Username Or Password Incorrect'));
?>