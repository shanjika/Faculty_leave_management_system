<?php
session_start();
include 'connect.php';
?>
<html>
<head>
<title>Faculty Leave Management System</title>
<link rel="shortcut icon" type="image/png" href="favicon.png"/>
<link rel="stylesheet" type="text/css" href="style.css">
<style>
.admin-blocks {
    display: flex; flex-wrap: wrap;
    gap: 28px; justify-content: center;
    margin: 36px 0 0;
}
.admin-block {
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    background: #fff; border-radius: 16px;
    box-shadow: 0 6px 24px rgba(0,0,0,.10);
    padding: 34px 36px; min-width: 200px;
    font-size: 18px; font-weight: 600; color: #333;
    text-decoration: none;
    transition: box-shadow .2s, transform .2s;
    text-align: center; gap: 10px;
}
.admin-block:hover {
    box-shadow: 0 12px 32px rgba(102,126,234,.20);
    background: #f6f8ff; color: #2d3a6a;
    transform: translateY(-4px) scale(1.03);
}
.role-badge {
    display: inline-block; padding: 4px 18px;
    border-radius: 99px; font-size: .8rem;
    font-weight: 700; margin-bottom: 10px;
}
.badge-hod { background:#dbeafe; color:#1e40af; }
.badge-hr  { background:#d1fae5; color:#065f46; }
</style>
</head>
<body>
<?php
include 'adminnavi.php';
echo "<center>";
echo "<div class='textview'>";
echo "<h1>Faculty Leave Management System</h1>";

if(isset($_SESSION['adminuser'])){
    $role = $_SESSION['role'] ?? 'HOD';

    if(isset($_GET['msg'])){
        echo "<div class='msg'><b><u>".htmlspecialchars($_GET['msg'])."</u></b></div>";
    }

    
    echo "<br/><h2>Welcome, ".$_SESSION['adminuser']."</h2>";
    echo "<div class='admin-blocks'>";

    if($role === 'HR'){
        // HR: only add/delete employee + assign faculty to HOD
        echo '<a class="admin-block" href="register">📝 Add New Faculty</a>';
        echo '<a class="admin-block" href="empdelete.php">🗑️ Delete Faculty</a>';
        echo '<a class="admin-block" href="assign_hod.php">🔗 Assign Faculty to HOD</a>';
    } else {
        // HOD: view/approve leaves + reports only
        echo '<a class="admin-block" href="view_leaves.php">📋 View Leave Requests</a>';
        echo '<a class="admin-block" href="set_leaves.php">⚙️ Set Default Leaves</a>';
        echo '<a class="admin-block" href="extract_leaves.php">📤 Extract Leaves</a>';
        echo '<a class="admin-block" href="reports.php">📊 Reports & Statistics</a>';
    }

    echo "</div>";
} else {
    header('location:index.php?err='.urlencode('Please Login first'));
}

echo "</div></center>";
?>
</body>
</html>