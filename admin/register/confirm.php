<?php
session_start();
include '../connect.php';

if(!isset($_SESSION['adminuser'])){
    header('location:../index.php?err='.urlencode('Please Login First!'));
    exit;
}
if(($_SESSION['role'] ?? 'HOD') !== 'HR'){
    header('location:../home.php?msg='.urlencode('Only HR can register faculty.'));
    exit;
}

$errmsg  = '';
$empname = strip_tags(trim($_POST['empname']    ?? ''));
$uname   = strip_tags(trim($_POST['uname']      ?? ''));
$mailid  = strip_tags(trim($_POST['mailid']     ?? ''));
$desig   = strip_tags(trim($_POST['designation']?? ''));
$emptype = strip_tags(trim($_POST['factype']    ?? ''));
$empfee  = strip_tags(trim($_POST['facfee']     ?? ''));
$gender  = strip_tags(trim($_POST['gender']     ?? 'Male'));
$dept    = strip_tags(trim($_POST['dept']       ?? ''));

$doj  = trim($_POST['year-join']  ?? '').'-'.
        str_pad(trim($_POST['month-join'] ?? ''),2,'0',STR_PAD_LEFT).'-'.
        str_pad(trim($_POST['date-join']  ?? ''),2,'0',STR_PAD_LEFT);

$dob  = trim($_POST['year-birth'] ?? '').'-'.
        str_pad(trim($_POST['month-birth']?? ''),2,'0',STR_PAD_LEFT).'-'.
        str_pad(trim($_POST['date-birth'] ?? ''),2,'0',STR_PAD_LEFT);

$dob2 = str_pad(trim($_POST['date-birth'] ?? ''),2,'0',STR_PAD_LEFT).'-'.
        str_pad(trim($_POST['month-birth']?? ''),2,'0',STR_PAD_LEFT).'-'.
        trim($_POST['year-birth'] ?? '');

$pass = $uname;

// Validation
if(empty($empname)||empty($uname)||empty($mailid)||empty($doj)||empty($dob)||empty($dept)){
    header('location:index.php?err='.urlencode('One or more fields are empty.'));
    exit;
}
if(!filter_var($mailid, FILTER_VALIDATE_EMAIL)){
    header('location:index.php?err='.urlencode('Invalid email address.'));
    exit;
}
if(strtotime($doj) > time()){
    header('location:index.php?err='.urlencode('Date of Joining cannot be a future date.'));
    exit;
}

// Check duplicate username or email
$resCheck = $conn->query("SELECT UserName, EmpEmail FROM employees");
if($resCheck && $resCheck->num_rows > 0){
    while($rowC = $resCheck->fetch_assoc()){
        if($uname  === $rowC['UserName']){
            header('location:index.php?err='.urlencode("Username '$uname' is already taken."));
            exit;
        }
        if($mailid === $rowC['EmpEmail']){
            header('location:index.php?err='.urlencode("Email '$mailid' is already registered."));
            exit;
        }
    }
}

// Get leave defaults
$earnleave = 0; $sickleave = 0; $casualleave = 0;
$resLeave = $conn->query("SELECT SetEarnLeave, SetSickLeave, SetCasualLeave FROM admins LIMIT 1");
if($resLeave && $resLeave->num_rows > 0){
    $rowLeave    = $resLeave->fetch_assoc();
    $earnleave   = (int)$rowLeave['SetEarnLeave'];
    $sickleave   = (int)$rowLeave['SetSickLeave'];
    $casualleave = (int)$rowLeave['SetCasualLeave'];
}

// Insert
$sql = "INSERT INTO employees
        (UserName, EmpPass, EmpName, Dept, EarnLeave, SickLeave, CasualLeave,
         EmpEmail, DateOfJoin, Designation, EmpType, EmpFee, DateOfBirth, Gender)
        VALUES (
        '".$conn->real_escape_string($uname)."',
        '".$conn->real_escape_string($pass)."',
        '".$conn->real_escape_string($empname)."',
        '".$conn->real_escape_string($dept)."',
        $earnleave, $sickleave, $casualleave,
        '".$conn->real_escape_string($mailid)."',
        '".$conn->real_escape_string($doj)."',
        '".$conn->real_escape_string($desig)."',
        '".$conn->real_escape_string($emptype)."',
        '".$conn->real_escape_string($empfee)."',
        '".$conn->real_escape_string($dob)."',
        '".$conn->real_escape_string($gender)."')";

if($conn->query($sql) === TRUE){
    $conn->close();
    header('location:../home.php?msg='.urlencode('Faculty '.$empname.' added successfully!'));
    exit;
} else {
    $conn->close();
    header('location:index.php?err='.urlencode('Database error: '.$conn->error));
    exit;
}
?>