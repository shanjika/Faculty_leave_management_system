<?php
session_start();
include 'connect.php';
require 'update_leaves.php';

$username = trim($_POST['uname']);
$password = trim($_POST['pass']);

$sql = "SELECT UserName, EmpPass, Dept FROM employees";
$result = $conn->query($sql);

$found = false;

while($row = $result->fetch_assoc()){

    if($username == $row['UserName'] && $password == $row['EmpPass']){

        $_SESSION['user'] = $username;
        $dept = $row['Dept'];

        update_leaves($username,$dept);

        header("location:home.php");
        exit();
    }
}

header("location:index.php?err=Username Or Password Incorrect");
exit();
?>
