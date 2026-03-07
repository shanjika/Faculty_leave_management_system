<?php
session_start();
?>
<title>View Faculty Leaves</title>
<link rel="stylesheet" type="text/css" href="style.css">
<link rel="shortcut icon" type="image/png" href="favicon.png"/>
<link rel="stylesheet" type="text/css" href="table.css">
<style>
.badge-type { display:inline-block; padding:3px 10px; border-radius:99px; font-size:11px; font-weight:700; white-space:nowrap; }
.badge-od  { background:#d1fae5; color:#065f46; }
.badge-sp  { background:#fce7f3; color:#9d174d; }
.badge-std { background:#e0e7ff; color:#4338ca; }
.badge-lop { background:#fee2e2; color:#991b1b; }
.session-info { font-size:11px; color:#64748b; margin-top:2px; }
.extra-info   { font-size:11px; color:#475569; margin-top:2px; font-style:italic; }
</style>
<div class="textview">
<center>
<?php
include 'connect.php';
echo "<h1>Faculty Leave Management System</h1>";
include 'adminnavi.php';

$role = $_SESSION['role'] ?? 'HOD';

// Block HR from this page
if($role === 'HR'){
    echo "<div style='background:#fee2e2;color:#991b1b;padding:20px;border-radius:10px;margin:20px;'>
          ❌ HR does not have access to leave approvals.</div>";
    echo "</center></div>";
    exit;
}

echo "<h2>View Faculty Leave Requests</h2>";
$count = 0;

if(isset($_SESSION['adminuser'])){
    $hodUser = $conn->real_escape_string($_SESSION['adminuser']);

    // HOD sees only leaves of faculty assigned to them
    $sql2 = "SELECT e.Id, e.EmpName, el.LeaveType, el.RequestDate, el.LeaveDays,
                    el.StartDate, el.EndDate, el.id,
                    IFNULL(el.FromSession,'') as FromSession,
                    IFNULL(el.ToSession,'') as ToSession,
                    IFNULL(el.ActivityID,'') as ActivityID,
                    IFNULL(el.ActivityName,'') as ActivityName,
                    IFNULL(el.Reason,'') as Reason
             FROM employees e
             INNER JOIN emp_leaves el ON e.EmpName = el.EmpName AND e.Dept = el.Dept
             WHERE e.HodUsername = '$hodUser'
             AND el.Status = 'Requested'";

    $result2 = $conn->query($sql2);

    if($result2 && $result2->num_rows > 0){
        echo "<table>";
        echo "<tr>
                <th>Faculty Name</th>
                <th>Leave Type</th>
                <th>Request Date</th>
                <th>Days</th>
                <th>From</th>
                <th>To</th>
                <th>Details</th>
                <th>Action</th>
              </tr>";
        while($row2 = $result2->fetch_assoc()){
            $lt      = $row2['LeaveType'];
            $isOD    = ($lt === 'On Duty');
            $isSp    = ($lt === 'Special Leave');
            $isLOP   = ($lt === 'Loss of Pay');
            if($isOD)       $bc = 'badge-od';
            elseif($isSp)   $bc = 'badge-sp';
            elseif($isLOP)  $bc = 'badge-lop';
            else             $bc = 'badge-std';

            echo "<tr>";
            echo "<td>".htmlspecialchars($row2['EmpName'])."</td>";
            echo "<td><span class='badge-type $bc'>".htmlspecialchars($lt)."</span></td>";
            echo "<td>".htmlspecialchars($row2['RequestDate'])."</td>";
            echo "<td>".htmlspecialchars($row2['LeaveDays'])."</td>";
            echo "<td>".htmlspecialchars($row2['StartDate']);
            if($row2['FromSession']) echo "<div class='session-info'>Session: <strong>".htmlspecialchars($row2['FromSession'])."</strong></div>";
            echo "</td>";
            echo "<td>".htmlspecialchars($row2['EndDate']);
            if($row2['ToSession']) echo "<div class='session-info'>Session: <strong>".htmlspecialchars($row2['ToSession'])."</strong></div>";
            echo "</td>";
            echo "<td>";
            if($row2['ActivityID'])   echo "<div class='extra-info'>🆔 ".htmlspecialchars($row2['ActivityID'])."</div>";
            if($row2['ActivityName']) echo "<div class='extra-info'>🎓 ".htmlspecialchars($row2['ActivityName'])."</div>";
            if($row2['Reason'])       echo "<div class='extra-info'>📝 ".htmlspecialchars(substr($row2['Reason'],0,60))."</div>";
            echo "</td>";
            echo "<td>
                <a href='acceptleave.php?id=".$row2['id']."&empid=".$row2['Id']."' style='color:#065f46;font-weight:700;'>✅ Accept</a>
                &nbsp;&nbsp;
                <a href='rejectleave.php?id=".$row2['id']."&empid=".$row2['Id']."' style='color:#991b1b;font-weight:700;'>❌ Reject</a>
              </td>";
            echo "</tr>";
            $count++;
        }
        echo "<tr><td colspan='8' style='text-align:center;padding:10px;background:#e8f4f8;'><strong>$count Pending Request(s)</strong></td></tr>";
        echo "</table>";
    } else {
        echo "<div style='background:#fff;padding:40px;text-align:center;border-radius:8px;margin:20px 0;'>";
        echo "<h3 style='color:#27ae60;'>✅ No Pending Requests</h3>";
        echo "<p style='color:#666;'>All leave requests have been processed, or no faculty is assigned to you yet.</p>";
        echo "</div>";
    }
} else {
    header("location:index.php?err=".urlencode('Please login first!'));
}
?>
</div>
</center>