<?php
session_start();
include 'connect.php';

if(!isset($_SESSION['user'])){
    header('location:index.php');
    exit;
}
?>

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
