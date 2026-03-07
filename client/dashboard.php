<?php
session_start();
include 'connect.php';
if(!isset($_SESSION['user'])){
    header('Location: index.php?err='.urlencode('Please Login First!'));
    exit();
}
$user = $_SESSION['user'];
$sql  = "SELECT * FROM employees WHERE UserName='".$conn->real_escape_string($user)."'";
$res  = $conn->query($sql);
$emp  = $res && $res->num_rows ? $res->fetch_assoc() : null;

function pct($num,$den){ return $den>0 ? round(($num/$den)*100) : 0; }

if($emp){
    $empName = $conn->real_escape_string($emp['EmpName']);

    $earn   = (int)$emp['EarnLeave'];
    $sick   = (int)$emp['SickLeave'];
    $casual = (int)$emp['CasualLeave'];
    $total  = $earn + $sick + $casual;

    function usedDays($conn,$empName,$type){
        $r   = $conn->query("SELECT SUM(LeaveDays) as u FROM emp_leaves WHERE EmpName='$empName' AND LeaveType='$type' AND Status IN ('Granted','Approved')");
        $row = $r && $r->num_rows ? $r->fetch_assoc() : null;
        return $row && $row['u'] ? (int)$row['u'] : 0;
    }

    $usedMedical = usedDays($conn,$empName,'Medical Leave');
    $usedCasual  = usedDays($conn,$empName,'Casual Leave');
    $usedEarn    = usedDays($conn,$empName,'Earn Leave');
    $usedLOP     = usedDays($conn,$empName,'Loss of Pay');
    $usedTotal   = $usedMedical + $usedCasual + $usedEarn + $usedLOP;

    $remainMedical = max(0, $sick   - $usedMedical);
    $remainCasual  = max(0, $casual - $usedCasual);
    $remainEarn    = max(0, $earn   - $usedEarn);
    $remaining     = max(0, $total  - $usedTotal);

    // On Duty
    $rOD      = $conn->query("SELECT COUNT(*) as cnt, SUM(LeaveDays) as days FROM emp_leaves WHERE EmpName='$empName' AND LeaveType='On Duty'");
    $rowOD    = $rOD && $rOD->num_rows ? $rOD->fetch_assoc() : null;
    $odCount  = $rowOD ? (int)$rowOD['cnt']  : 0;
    $odDays   = $rowOD ? (int)$rowOD['days'] : 0;
    $odApproved = usedDays($conn,$empName,'On Duty');
    $rODP     = $conn->query("SELECT SUM(LeaveDays) as d FROM emp_leaves WHERE EmpName='$empName' AND LeaveType='On Duty' AND Status='Requested'");
    $odPending  = $rODP && $rODP->num_rows ? (int)($rODP->fetch_assoc()['d'] ?? 0) : 0;

    // Special Leave
    $rSp      = $conn->query("SELECT COUNT(*) as cnt, SUM(LeaveDays) as days FROM emp_leaves WHERE EmpName='$empName' AND LeaveType='Special Leave'");
    $rowSp    = $rSp && $rSp->num_rows ? $rSp->fetch_assoc() : null;
    $spCount  = $rowSp ? (int)$rowSp['cnt']  : 0;
    $spDays   = $rowSp ? (int)$rowSp['days'] : 0;
    $spApproved = usedDays($conn,$empName,'Special Leave');
    $rSPP     = $conn->query("SELECT SUM(LeaveDays) as d FROM emp_leaves WHERE EmpName='$empName' AND LeaveType='Special Leave' AND Status='Requested'");
    $spPending  = $rSPP && $rSPP->num_rows ? (int)($rSPP->fetch_assoc()['d'] ?? 0) : 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Leave Dashboard</title>
<link rel="stylesheet" href="style.css">
<style>
    .cancel-link { color:#e53e3e; text-decoration:none; font-weight:600; font-size:13px; }
.cancel-link:hover { text-decoration:underline; }
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; background:#f5f7fa; color:#333; }

.page-content { padding:30px 20px; }
.container    { max-width:1200px; margin:0 auto; }

.banner {
    background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    color:#fff; padding:30px; border-radius:12px;
    margin-bottom:30px;
    box-shadow:0 10px 30px rgba(102,126,234,.4);
}
.banner h1 { font-size:30px; margin-bottom:6px; }
.banner p  { font-size:14px; opacity:.9; }

.section-title { font-size:18px; font-weight:700; color:#1a202c; margin:30px 0 16px; }

.stats-grid {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:22px; margin-bottom:14px;
}
.stats-grid-2 {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
    gap:22px; margin-bottom:30px;
}

.stat-card {
    background:#fff; padding:24px; border-radius:12px;
    box-shadow:0 4px 15px rgba(0,0,0,.08);
    border-left:5px solid #667eea;
    transition:transform .2s,box-shadow .2s;
}
.stat-card:hover { transform:translateY(-4px); box-shadow:0 10px 28px rgba(0,0,0,.12); }
.stat-card.used      { border-left-color:#f6ad55; }
.stat-card.remaining { border-left-color:#48bb78; }
.stat-card.onduty    { border-left-color:#4299e1; }
.stat-card.special   { border-left-color:#9f7aea; }

.stat-label { font-size:11px; text-transform:uppercase; color:#718096; font-weight:700; letter-spacing:.8px; }
.stat-value { font-size:40px; font-weight:800; color:#2d3748; margin:12px 0 16px; }
.stat-detail{ font-size:12px; color:#718096; margin-top:10px; }

.type-row { margin-bottom:12px; }
.type-header { display:flex; justify-content:space-between; font-size:12px; color:#4a5568; margin-bottom:4px; }
.type-name  { font-weight:700; }
.type-count { color:#718096; }
.progress-bar { background:#e2e8f0; height:7px; border-radius:4px; overflow:hidden; }
.progress-fill { height:100%; border-radius:4px; transition:width .5s ease; }

/* Total card colours */
.stat-card           .progress-fill.medical { background:linear-gradient(90deg,#667eea,#764ba2); }
.stat-card           .progress-fill.casual  { background:linear-gradient(90deg,#b794f4,#9f7aea); }
.stat-card           .progress-fill.earn    { background:linear-gradient(90deg,#63b3ed,#4299e1); }
.stat-card           .progress-fill.lop     { background:linear-gradient(90deg,#fc8181,#e53e3e); }

/* Used card colours */
.stat-card.used      .progress-fill.medical { background:linear-gradient(90deg,#f6ad55,#ed8936); }
.stat-card.used      .progress-fill.casual  { background:linear-gradient(90deg,#fbd38d,#f6ad55); }
.stat-card.used      .progress-fill.earn    { background:linear-gradient(90deg,#76e4f7,#0bc5ea); }
.stat-card.used      .progress-fill.lop     { background:linear-gradient(90deg,#fc8181,#e53e3e); }

/* Remaining card colours */
.stat-card.remaining .progress-fill.medical { background:linear-gradient(90deg,#48bb78,#38a169); }
.stat-card.remaining .progress-fill.casual  { background:linear-gradient(90deg,#4fd1c5,#38b2ac); }
.stat-card.remaining .progress-fill.earn    { background:linear-gradient(90deg,#68d391,#48bb78); }
.stat-card.remaining .progress-fill.lop     { background:linear-gradient(90deg,#a0aec0,#718096); }

/* Info rows for OD / Special */
.info-row {
    display:flex; justify-content:space-between;
    align-items:center; padding:9px 0;
    border-bottom:1px solid #f1f5f9; font-size:13px;
}
.info-row:last-child { border-bottom:none; }
.info-label { color:#4a5568; font-weight:600; }
.info-val   { font-weight:700; color:#2d3748; }
.badge { display:inline-block; padding:3px 10px; border-radius:99px; font-size:11px; font-weight:700; }
.badge-pending  { background:#fef3c7; color:#92400e; }
.badge-approved { background:#d1fae5; color:#065f46; }

/* Table */
.table-card {
    background:#fff; padding:24px; border-radius:12px;
    box-shadow:0 4px 15px rgba(0,0,0,.08); overflow-x:auto;
}
.table-card h3 { margin-bottom:18px; color:#2d3748; font-size:17px; }
table { width:100%; border-collapse:collapse; }
thead { background:#f7fafc; border-bottom:2px solid #e2e8f0; }
th { padding:12px; text-align:left; font-weight:700; color:#4a5568; font-size:12px; text-transform:uppercase; }
td { padding:14px 12px; border-bottom:1px solid #e2e8f0; font-size:13px; }
tbody tr:hover { background:#f7fafc; }

.status-badge { display:inline-block; padding:3px 10px; border-radius:4px; font-size:11px; font-weight:700; }
.status-requested { background:#bee3f8; color:#2c5282; }
.status-granted   { background:#c6f6d5; color:#22543d; }
.status-approved  { background:#c6f6d5; color:#22543d; }
.status-rejected  { background:#fed7d7; color:#742a2a; }

.action-link { color:#667eea; text-decoration:none; font-weight:600; }
.action-link:hover { text-decoration:underline; }
.empty-state { text-align:center; padding:40px; color:#718096; font-size:15px; }

@media(max-width:768px){
    .banner h1 { font-size:22px; }
    .stat-value { font-size:32px; }
    .stats-grid,.stats-grid-2 { grid-template-columns:1fr; }
}
</style>
</head>
<body>
<?php include 'clientnavi.php'; ?>
</div>

<div class="page-content">
<div class="container">

<?php if(!$emp): ?>
    <div class="banner">
        <h1>Dashboard</h1>
        <p>Employee record not found. Please contact HR.</p>
    </div>
<?php else: ?>

<div class="banner">
    <h1>Welcome, <?php echo htmlspecialchars($emp['EmpName']); ?></h1>
    <p>Your Leave Balance Overview</p>
</div>

<!-- ── SECTION 1: Standard Leaves ── -->
<div class="section-title">📊 Standard Leave Balance</div>
<div class="stats-grid">

    <!-- Total -->
    <div class="stat-card">
        <div class="stat-label">📊 Total Leaves</div>
        <div class="stat-value"><?php echo $total; ?></div>
        <div class="type-row">
            <div class="type-header">
                <span class="type-name">Medical Leave</span>
                <span class="type-count"><?php echo $sick; ?> days</span>
            </div>
            <div class="progress-bar"><div class="progress-fill medical" style="width:100%"></div></div>
        </div>
        <div class="type-row">
            <div class="type-header">
                <span class="type-name">Casual Leave</span>
                <span class="type-count"><?php echo $casual; ?> days</span>
            </div>
            <div class="progress-bar"><div class="progress-fill casual" style="width:100%"></div></div>
        </div>
        <div class="type-row">
            <div class="type-header">
                <span class="type-name">Earn Leave</span>
                <span class="type-count"><?php echo $earn; ?> days</span>
            </div>
            <div class="progress-bar"><div class="progress-fill earn" style="width:100%"></div></div>
        </div>
        <div class="stat-detail">Total allocated leave days</div>
    </div>

    <!-- Used -->
    <div class="stat-card used">
        <div class="stat-label">✓ Used Leaves</div>
        <div class="stat-value"><?php echo $usedTotal; ?></div>
        <div class="type-row">
            <div class="type-header">
                <span class="type-name">Medical Leave</span>
                <span class="type-count"><?php echo $usedMedical; ?> / <?php echo $sick; ?> (<?php echo pct($usedMedical,$sick); ?>%)</span>
            </div>
            <div class="progress-bar"><div class="progress-fill medical" style="width:<?php echo pct($usedMedical,$sick); ?>%"></div></div>
        </div>
        <div class="type-row">
            <div class="type-header">
                <span class="type-name">Casual Leave</span>
                <span class="type-count"><?php echo $usedCasual; ?> / <?php echo $casual; ?> (<?php echo pct($usedCasual,$casual); ?>%)</span>
            </div>
            <div class="progress-bar"><div class="progress-fill casual" style="width:<?php echo pct($usedCasual,$casual); ?>%"></div></div>
        </div>
        <div class="type-row">
            <div class="type-header">
                <span class="type-name">Earn Leave</span>
                <span class="type-count"><?php echo $usedEarn; ?> / <?php echo $earn; ?> (<?php echo pct($usedEarn,$earn); ?>%)</span>
            </div>
            <div class="progress-bar"><div class="progress-fill earn" style="width:<?php echo pct($usedEarn,$earn); ?>%"></div></div>
        </div>
        <div class="type-row">
            <div class="type-header">
                <span class="type-name">Loss of Pay</span>
                <span class="type-count"><?php echo $usedLOP; ?> days taken</span>
            </div>
            <div class="progress-bar"><div class="progress-fill lop" style="width:<?php echo min(100,$usedLOP*10); ?>%"></div></div>
        </div>
        <div class="stat-detail">Approved &amp; granted requests</div>
    </div>

    <!-- Remaining -->
    <div class="stat-card remaining">
        <div class="stat-label">🎯 Remaining Leaves</div>
        <div class="stat-value"><?php echo $remaining; ?></div>
        <div class="type-row">
            <div class="type-header">
                <span class="type-name">Medical Leave</span>
                <span class="type-count"><?php echo $remainMedical; ?> / <?php echo $sick; ?> (<?php echo pct($remainMedical,$sick); ?>%)</span>
            </div>
            <div class="progress-bar"><div class="progress-fill medical" style="width:<?php echo pct($remainMedical,$sick); ?>%"></div></div>
        </div>
        <div class="type-row">
            <div class="type-header">
                <span class="type-name">Casual Leave</span>
                <span class="type-count"><?php echo $remainCasual; ?> / <?php echo $casual; ?> (<?php echo pct($remainCasual,$casual); ?>%)</span>
            </div>
            <div class="progress-bar"><div class="progress-fill casual" style="width:<?php echo pct($remainCasual,$casual); ?>%"></div></div>
        </div>
        <div class="type-row">
            <div class="type-header">
                <span class="type-name">Earn Leave</span>
                <span class="type-count"><?php echo $remainEarn; ?> / <?php echo $earn; ?> (<?php echo pct($remainEarn,$earn); ?>%)</span>
            </div>
            <div class="progress-bar"><div class="progress-fill earn" style="width:<?php echo pct($remainEarn,$earn); ?>%"></div></div>
        </div>
        <div class="type-row">
            <div class="type-header">
                <span class="type-name">Loss of Pay</span>
                <span class="type-count">No quota limit</span>
            </div>
            <div class="progress-bar"><div class="progress-fill lop" style="width:0%"></div></div>
        </div>
        <div class="stat-detail">Available quota remaining</div>
    </div>

</div>

<!-- ── SECTION 2: On Duty & Special ── -->
<div class="section-title">📋 On Duty &amp; Special Leave Summary</div>
<div class="stats-grid-2">

    <!-- On Duty -->
    <div class="stat-card onduty">
        <div class="stat-label">🏛️ On Duty Leave</div>
        <div class="stat-value"><?php echo $odDays; ?> <span style="font-size:16px;color:#718096;">days</span></div>
        <div class="info-row">
            <span class="info-label">Total Applications</span>
            <span class="info-val"><?php echo $odCount; ?> requests</span>
        </div>
        <div class="info-row">
            <span class="info-label">Approved Days</span>
            <span class="info-val"><span class="badge badge-approved">✅ <?php echo $odApproved; ?> days</span></span>
        </div>
        <div class="info-row">
            <span class="info-label">Pending Days</span>
            <span class="info-val"><span class="badge badge-pending">⏳ <?php echo $odPending; ?> days</span></span>
        </div>
        <div class="stat-detail">On Duty has no fixed quota — tracked separately</div>
    </div>

    <!-- Special Leave -->
    <div class="stat-card special">
        <div class="stat-label">⭐ Special Leave</div>
        <div class="stat-value"><?php echo $spDays; ?> <span style="font-size:16px;color:#718096;">days</span></div>
        <div class="info-row">
            <span class="info-label">Total Applications</span>
            <span class="info-val"><?php echo $spCount; ?> requests</span>
        </div>
        <div class="info-row">
            <span class="info-label">Approved Days</span>
            <span class="info-val"><span class="badge badge-approved">✅ <?php echo $spApproved; ?> days</span></span>
        </div>
        <div class="info-row">
            <span class="info-label">Pending Days</span>
            <span class="info-val"><span class="badge badge-pending">⏳ <?php echo $spPending; ?> days</span></span>
        </div>
        <div class="stat-detail">Special Leave has no fixed quota — tracked separately</div>
    </div>

</div>

<!-- ── Recent Requests ── -->
<div class="section-title">📋 Recent Leave Requests</div>
<div class="table-card">
<?php
$sql2 = "SELECT * FROM emp_leaves WHERE EmpName='".$conn->real_escape_string($emp['EmpName'])."' ORDER BY RequestDate DESC LIMIT 10";
$res2 = $conn->query($sql2);
if($res2 && $res2->num_rows){
    echo "<h3>Last 10 Requests</h3>";
    echo "<table><thead><tr>
            <th>Request Date</th><th>Leave Type</th><th>Days</th>
            <th>Start</th><th>End</th><th>Status</th><th>Download</th>
          </tr></thead><tbody>";
    while($rr = $res2->fetch_assoc()){
        $sc = 'status-'.strtolower(str_replace(' ','',$rr['Status']));
        echo "<tr>";
        echo "<td>".htmlspecialchars($rr['RequestDate'])."</td>";
        echo "<td>".htmlspecialchars($rr['LeaveType'])."</td>";
        echo "<td><strong>".htmlspecialchars($rr['LeaveDays'])."</strong></td>";
        echo "<td>".htmlspecialchars($rr['StartDate'])."</td>";
        echo "<td>".htmlspecialchars($rr['EndDate'])."</td>";
        echo "<td><span class='status-badge $sc'>".htmlspecialchars($rr['Status'])."</span></td>";
echo "<td style='display:flex;gap:10px;align-items:center;'>";
echo "<a href='download.php?id=".urlencode($rr['id'])."' class='action-link'>📄 Download</a>";
if($rr['Status'] === 'Requested'){
    echo " &nbsp;<a href='cancel_leave.php?id=".urlencode($rr['id'])."' class='cancel-link' onclick=\"return confirm('Are you sure you want to cancel this leave request?')\">🗑 Cancel</a>";
}
echo "</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
} else {
    echo "<div class='empty-state'>No leave requests yet. <a href='request_leave.php' class='action-link'>Submit your first request →</a></div>";
}
?>
</div>

<?php endif; ?>
</div>
</div>
</body>
</html>