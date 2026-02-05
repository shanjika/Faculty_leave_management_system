<?php
include 'connect.php';

$key = trim($_POST['key']);
$newpass = trim($_POST['newpass']);
$cnf = trim($_POST['cnfnewpass']);

if($newpass != $cnf){
    header("location:resetpass.php?err=Passwords do not match");
    exit;
}

$sql = "SELECT id,UserName,Random FROM employees";
$result = $conn->query($sql);

while($row = $result->fetch_assoc()){

    if($row['Random'] == $key){

        $id = $row['id'];

        // SAVE PASSWORD AS PLAIN TEXT
        $conn->query("UPDATE employees SET EmpPass='$newpass', Random='' WHERE id='$id'");

        header("location:index.php?err=Password Changed Successfully");
        exit;
    }
}

echo "Invalid reset key";

$conn->close();
?>
