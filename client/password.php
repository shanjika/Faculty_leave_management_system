<link rel="stylesheet" type="text/css" href="style.css">

<?php
session_start();
include 'connect.php';

if(!isset($_SESSION['user'])){
    header("location:index.php");
    exit;
}

$oldpass = $_POST['oldpass'];
$newpass = $_POST['newpass'];
$cnfnewpass = $_POST['cnfnewpass'];
$uname = $_SESSION['user'];

if($newpass != $cnfnewpass){
    header("location:changepass.php?err=Passwords do not match");
    exit;
}

if(strlen($newpass) < 6){
    header("location:changepass.php?err=Password must be minimum 6 characters");
    exit;
}

$sql = "SELECT id, EmpPass FROM employees WHERE UserName='$uname'";
$res = $conn->query($sql);

$row = $res->fetch_assoc();

if($oldpass == $row['EmpPass']){

    $conn->query("UPDATE employees SET EmpPass='$newpass' WHERE id='".$row['id']."'");

    header("location:home.php?msg=Password Changed Successfully");

}else{

    header("location:changepass.php?err=Incorrect Old Password");
}
?>
