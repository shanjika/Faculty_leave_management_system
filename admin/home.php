
<html>
<head>
<title>::Leave Management::</title>
<link rel="shortcut icon" type="image/png" href="favicon.png"/>
<link rel="stylesheet" type="text/css" href="style.css">
<style>
.admin-blocks {
	display: flex;
	flex-wrap: wrap;
	gap: 32px;
	justify-content: center;
	margin: 40px 0 0 0;
}
.admin-block {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	background: #fff;
	border-radius: 16px;
	box-shadow: 0 6px 24px rgba(0,0,0,0.10);
	padding: 36px 38px;
	min-width: 220px;
	min-height: 120px;
	font-size: 20px;
	font-weight: 600;
	color: #333;
	text-decoration: none;
	transition: box-shadow 0.2s, transform 0.2s;
}
.admin-block:hover {
	box-shadow: 0 12px 32px rgba(102,126,234,0.18);
	background: #f6f8ff;
	color: #2d3a6a;
	transform: translateY(-4px) scale(1.04);
}
</style>
</head>
<body>
<?php
session_start();
// Place Logout button at top right, outside the main content
include 'adminnavi.php';
echo "<center>";
echo "<div class = 'textview'>";
echo "<h1>Leave Management System</h1>";
if(isset($_SESSION['adminuser']))
        {
        if(isset($_GET['msg']))
            {
                echo "<div class = 'msg'><b><u>".htmlspecialchars($_GET['msg'])."</u></b></div>";
            }
        echo "<br/><h2>Welcome, " . $_SESSION["adminuser"] ."</h2>";
        // Admin tool blocks
        echo '<div class="admin-blocks">';
        echo '<a class="admin-block" href="register">📝 Register New Employee</a>';
        echo '<a class="admin-block" href="empdelete.php">🗑️ Delete Employee</a>';
        echo '<a class="admin-block" href="view_leaves.php">📋 Accept/Reject Leave</a>';
        echo '<a class="admin-block" href="set_leaves.php">⚙️ Set Default Leave</a>';
        echo '<a class="admin-block" href="extract_leaves.php">📤 Extract Leaves</a>';
        echo '<a class="admin-block" href="reports.php">📊 Reports & Statistics</a>';
        echo '</div>';
        }
else
    {
        header('location:index.php?err='.urlencode('Please Login first to access this page'));
    }
echo "</div>";
echo "</center>";
?>
