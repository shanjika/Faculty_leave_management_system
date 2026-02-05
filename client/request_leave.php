<?php
session_start();
include 'connect.php';

if(!isset($_SESSION['user'])){
    header('location:index.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Leave Management</title>

<style>
body{
    margin:0;
    font-family:Segoe UI,Arial;
    background:linear-gradient(120deg,#dff1ff,#ffffff);
}

.header{
    text-align:center;
    font-size:34px;
    padding:25px;
    background:white;
    box-shadow:0 2px 10px rgba(0,0,0,.15);
}

.container{
    max-width:900px;
    margin:60px auto;
    background:white;
    padding:50px;
    border-radius:15px;
    box-shadow:0 15px 35px rgba(0,0,0,.2);
}

.menu{
    background:#3b82f6;
    padding:15px;
    border-radius:10px;
    margin-bottom:30px;
	 background:white;
}

.menu a{
    color:white;
    margin-right:30px;
    font-weight:bold;
    text-decoration:none;
}

h2{
    text-align:center;
}

.leave-box{
    display:flex;
    justify-content:space-around;
    margin-top:40px;
}

.leave-btn{
    width:200px;
    height:90px;
    border:none;
    border-radius:12px;
    background:#2563eb;
    color:white;
    font-size:18px;
    cursor:pointer;
    box-shadow:0 8px 20px rgba(0,0,0,.2);
    transition:.3s;
}

.leave-btn:hover{
    transform:translateY(-5px);
}

.disabled{
    background:#ccc;
    color:#666;
}

.error{
    text-align:center;
    color:red;
    margin-top:15px;
    font-weight:bold;
}
</style>
</head>

<body>

<div class="header">Leave Management System</div>

<div class="container">

<div class="menu">
<?php include 'clientnavi.php'; ?>
</div>

<h2>Select Leave Type</h2>

<?php
if(isset($_GET['err'])){
    echo "<div class='error'>".$_GET['err']."</div>";
}
?>

<form action="leaverequest.php" method="post">

<div class="leave-box">

<?php
$sql="SELECT * FROM employees WHERE UserName='".$_SESSION['user']."'";
$res=$conn->query($sql);
$row=$res->fetch_assoc();

if($row['SickLeave']>0)
echo "<button class='leave-btn' name='type' value='Sick Leave'>🤒 Sick Leave</button>";
else
echo "<button class='leave-btn disabled' disabled>🤒 Sick Leave</button>";

if($row['EarnLeave']>0)
echo "<button class='leave-btn' name='type' value='Earn Leave'>💼 Earn Leave</button>";
else
echo "<button class='leave-btn disabled' disabled>💼 Earn Leave</button>";

if($row['CasualLeave']>0)
echo "<button class='leave-btn' name='type' value='Casual Leave'>🌴 Casual Leave</button>";
else
echo "<button class='leave-btn disabled' disabled>🌴 Casual Leave</button>";
?>

</div>
</form>

</div>

</body>
</html>
