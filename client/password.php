<?php
session_start();
include 'connect.php';

if(!isset($_SESSION['user'])){
    header('location:index.php');
    exit;
}

$user       = $conn->real_escape_string($_SESSION['user']);
$oldpass    = $_POST['oldpass']    ?? '';
$newpass    = $_POST['newpass']    ?? '';
$cnfnewpass = $_POST['cnfnewpass'] ?? '';

// Validation
if(empty($oldpass) || empty($newpass) || empty($cnfnewpass)){
    header('location:changepass.php?err='.urlencode('All fields are required!'));
    exit;
}
if($newpass !== $cnfnewpass){
    header('location:changepass.php?err='.urlencode('New passwords do not match!'));
    exit;
}
if(strlen($newpass) < 6){
    header('location:changepass.php?err='.urlencode('Password must be at least 6 characters!'));
    exit;
}

// Get current password
$res = $conn->query("SELECT id, EmpPass FROM employees WHERE UserName='$user'");
if(!$res || $res->num_rows === 0){
    header('location:changepass.php?err='.urlencode('User not found!'));
    exit;
}
$row = $res->fetch_assoc();

// Check old password matches
if($oldpass !== $row['EmpPass']){
    header('location:changepass.php?err='.urlencode('Current password is incorrect!'));
    exit;
}

// Check new password is not same as old
if($newpass === $oldpass){
    header('location:changepass.php?err='.urlencode('New password cannot be same as current password!'));
    exit;
}

// Update password
$conn->query("UPDATE employees SET EmpPass='".  $conn->real_escape_string($newpass)."' WHERE id='".$row['id']."'");
$conn->close();

header('location:changepass.php?msg='.urlencode('Password changed successfully!'));
exit;
?>