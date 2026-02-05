<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'connect.php';

if (!isset($_SESSION['adminuser'])) {
    header('location:index.php?msg=' . urlencode('Please Login First To Access This Page!'));
    exit;
}

if (!isset($_POST['yearstart'])) {
    die("Invalid access");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>::Leave Management::</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" type="text/css" href="table.css">
</head>
<body>

<div class="textview">

<h1>Leave Management System</h1>
<?php include 'adminnavi.php'; ?>

<h2>Extracted Data</h2>

<?php
$startdate = $_POST['yearstart'] . "-" . $_POST['monthstart'] . "-" . $_POST['datestart'];
$enddate   = $_POST['yearend'] . "-" . $_POST['monthend'] . "-" . $_POST['dateend'];

$sql = "
SELECT EmpName, LeaveType, RequestDate, LeaveDays, Status, StartDate, EndDate, Dept
FROM emp_leaves
WHERE StartDate BETWEEN '$startdate' AND '$enddate'
AND Dept = '" . $_SESSION['dept'] . "'
ORDER BY RequestDate DESC
";


$result = $conn->query($sql);

if ($result->num_rows > 0) {

    echo "<table border='1' cellpadding='8' cellspacing='0'>
            <tr>
                <th>Employee Name</th>
                <th>Leave Type</th>
                <th>Request Date</th>
                <th>Leave Days</th>
                <th>Status</th>
                <th>Starting Date</th>
                <th>Ending Date</th>
                <th>Department</th>
            </tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['EmpName']}</td>
                <td>{$row['LeaveType']}</td>
                <td>{$row['RequestDate']}</td>
                <td>{$row['LeaveDays']}</td>
                <td>{$row['Status']}</td>
                <td>{$row['StartDate']}</td>
                <td>{$row['EndDate']}</td>
                <td>{$row['Dept']}</td>
              </tr>";
    }

    echo "</table>";

    

} else {
    echo "<p style='color:red'>No results found!</p>";
}
?>

</div>

</body>
</html>
