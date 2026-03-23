<?php
session_start();
include 'connect.php';
if(!isset($_SESSION['user'])){
    header('Location: index.php?err='.urlencode('Please Login First To Access This Page !'));
    exit();
}
$user = $_SESSION['user'];
$sql  = "SELECT * FROM employees WHERE UserName='".$conn->real_escape_string($user)."'";
$res  = $conn->query($sql);
$emp  = $res && $res->num_rows ? $res->fetch_assoc() : null;

$total = 0; $used = 0;
$usedEarn = 0; $usedSick = 0; $usedCasual = 0;
$earn = 0; $sick = 0; $casual = 0;

if($emp){
    $earn    = (int)$emp['EarnLeave'];
    $sick    = (int)$emp['SickLeave'];
    $casual  = (int)$emp['CasualLeave'];
    $total   = $earn + $sick + $casual;
    $empName = $conn->real_escape_string($emp['EmpName']);

    $q   = "SELECT SUM(LeaveDays) as used FROM emp_leaves WHERE EmpName='$empName' AND Status IN ('Granted','Approved')";
    $r   = $conn->query($q);
    $row = $r && $r->num_rows ? $r->fetch_assoc() : null;
    $used = $row && $row['used'] ? (int)$row['used'] : 0;

    $qEarn   = "SELECT SUM(LeaveDays) as used FROM emp_leaves WHERE EmpName='$empName' AND LeaveType='Earn Leave'   AND Status IN ('Granted','Approved')";
    $qSick   = "SELECT SUM(LeaveDays) as used FROM emp_leaves WHERE EmpName='$empName' AND LeaveType='Sick Leave'   AND Status IN ('Granted','Approved')";
    $qCasual = "SELECT SUM(LeaveDays) as used FROM emp_leaves WHERE EmpName='$empName' AND LeaveType='Casual Leave' AND Status IN ('Granted','Approved')";

    $rE = $conn->query($qEarn);   $rowE = $rE && $rE->num_rows ? $rE->fetch_assoc() : null;
    $rS = $conn->query($qSick);   $rowS = $rS && $rS->num_rows ? $rS->fetch_assoc() : null;
    $rC = $conn->query($qCasual); $rowC = $rC && $rC->num_rows ? $rC->fetch_assoc() : null;

    $usedEarn   = $rowE && $rowE['used'] ? (int)$rowE['used'] : 0;
    $usedSick   = $rowS && $rowS['used'] ? (int)$rowS['used'] : 0;
    $usedCasual = $rowC && $rowC['used'] ? (int)$rowC['used'] : 0;
}
$remaining    = max(0, $total  - $used);
$remainEarn   = max(0, $earn   - $usedEarn);
$remainSick   = max(0, $sick   - $usedSick);
$remainCasual = max(0, $casual - $usedCasual);

// Reporting Authority (HOD)
$hodName = '';
$hodDept = '';
if($emp && !empty($emp['HodUsername'])){
    $hodUser = $conn->real_escape_string($emp['HodUsername']);
    $hodRes  = $conn->query("SELECT username, Dept FROM admins WHERE username='$hodUser'");
    if($hodRes && $hodRes->num_rows > 0){
        $hod     = $hodRes->fetch_assoc();
        $hodName = $hod['username'];
        $hodDept = $hod['Dept'];
    }
}

function pct($num, $den){ return $den > 0 ? round(($num/$den)*100) : 0; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Balance Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="table.css">
    <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; background:#f5f7fa; color:#333; }
    .page-wrapper { display:flex; flex-direction:column; min-height:100vh; }
    .page-content { flex:1; padding:30px 20px; }
    .container { max-width:1200px; margin:0 auto; }

    /* Banner */
    .banner {
        background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
        color:#fff; padding:30px; border-radius:14px;
        margin-bottom:30px;
        box-shadow:0 10px 30px rgba(102,126,234,0.4);
        display:flex; justify-content:space-between;
        align-items:center; flex-wrap:wrap; gap:20px;
    }
    .banner h1 { font-size:2rem; margin-bottom:6px; }
    .banner p  { font-size:.9rem; opacity:.88; }

    /* Reporting authority */
    .ra-card {
        background:rgba(255,255,255,.15);
        border:2px solid rgba(255,255,255,.3);
        border-radius:14px; padding:18px 26px;
        min-width:220px; text-align:center;
        backdrop-filter:blur(6px); flex-shrink:0;
    }
    .ra-label { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.8px; opacity:.75; margin-bottom:10px; }
    .ra-name  { font-size:1.15rem; font-weight:800; margin-bottom:4px; }
    .ra-dept  { font-size:.8rem; opacity:.8; }
    .ra-unassigned { font-size:.92rem; font-weight:700; opacity:.75; }
    .ra-hint  { font-size:.75rem; opacity:.65; margin-top:4px; }
.pill-na { background:#f1f5f9; color:#94a3b8; }
    h2 { font-size:22px; margin:30px 0 20px; color:#1a202c; font-weight:600; }

    /* Stats */
    .stats-grid {
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
        gap:25px; margin-bottom:40px;
    }
    .stat-card {
        background:#fff; padding:25px; border-radius:10px;
        box-shadow:0 4px 15px rgba(0,0,0,.08);
        transition:transform .3s,box-shadow .3s;
        border-left:5px solid #667eea;
    }
    .stat-card:hover { transform:translateY(-5px); box-shadow:0 10px 30px rgba(0,0,0,.12); }
    .stat-card.used      { border-left-color:#f6ad55; }
    .stat-card.remaining { border-left-color:#48bb78; }

    .stat-label { font-size:12px; text-transform:uppercase; color:#718096; font-weight:600; letter-spacing:.5px; }
    .stat-value { font-size:42px; font-weight:700; color:#2d3748; margin:15px 0 18px; }
    .stat-detail{ font-size:12px; color:#718096; margin-top:10px; }

    .type-breakdown { margin-top:4px; }
    .type-row { margin-bottom:10px; }
    .type-header { display:flex; justify-content:space-between; align-items:center; font-size:12px; color:#4a5568; margin-bottom:4px; }
    .type-name  { font-weight:600; }
    .type-count { color:#718096; }
    .progress-bar  { background:#e2e8f0; height:7px; border-radius:4px; overflow:hidden; }
    .progress-fill { height:100%; border-radius:4px; transition:width .5s ease; }

    .stat-card .progress-fill.earn   { background:linear-gradient(90deg,#667eea,#764ba2); }
    .stat-card .progress-fill.sick   { background:linear-gradient(90deg,#63b3ed,#4299e1); }
    .stat-card .progress-fill.casual { background:linear-gradient(90deg,#b794f4,#9f7aea); }
    .stat-card.used .progress-fill.earn   { background:linear-gradient(90deg,#f6ad55,#ed8936); }
    .stat-card.used .progress-fill.sick   { background:linear-gradient(90deg,#fc8181,#e53e3e); }
    .stat-card.used .progress-fill.casual { background:linear-gradient(90deg,#fbd38d,#f6ad55); }
    .stat-card.remaining .progress-fill.earn   { background:linear-gradient(90deg,#48bb78,#38a169); }
    .stat-card.remaining .progress-fill.sick   { background:linear-gradient(90deg,#4fd1c5,#38b2ac); }
    .stat-card.remaining .progress-fill.casual { background:linear-gradient(90deg,#68d391,#48bb78); }

    /* Table */
    .table-card {
        background:#fff; padding:25px; border-radius:10px;
        box-shadow:0 4px 15px rgba(0,0,0,.08); overflow-x:auto;
    }
    .table-card h3 { margin-bottom:20px; color:#2d3748; font-size:18px; }
    .table-card table { width:100%; border-collapse:collapse; min-width:900px; }
    .table-card thead { background:#f7fafc; border-bottom:2px solid #e2e8f0; }
    .table-card th { padding:12px; text-align:left; font-weight:600; color:#4a5568; font-size:12px; text-transform:uppercase; white-space:nowrap; }
    .table-card td { padding:14px 12px; border-bottom:1px solid #e2e8f0; font-size:14px; vertical-align:middle; }
    .table-card tbody tr:hover { background:#f7fafc; }

    .pill {
        display:inline-block; padding:3px 10px;
        border-radius:99px; font-size:.74rem; font-weight:700;
    }
    .pill-pending  { background:#fef3c7; color:#92400e; }
    .pill-granted  { background:#d1fae5; color:#065f46; }
    .pill-rejected { background:#fee2e2; color:#991b1b; }
    .pill-approved { background:#d1fae5; color:#065f46; }

    .overall-pill {
        display:inline-block; padding:4px 12px;
        border-radius:99px; font-size:.78rem; font-weight:700;
    }
    .ov-pending  { background:#bee3f8; color:#2c5282; }
    .ov-granted  { background:#c6f6d5; color:#22543d; }
    .ov-rejected { background:#fed7d7; color:#742a2a; }

    .rejection-box {
        margin-top:6px; background:#fee2e2; border-radius:6px;
        padding:5px 10px; font-size:.74rem; color:#991b1b;
        max-width:200px; line-height:1.4;
    }

    .action-link  { color:#667eea; text-decoration:none; font-weight:600; transition:color .3s; }
    .action-link:hover { color:#764ba2; text-decoration:underline; }
    .cancel-link  { color:#e53e3e; text-decoration:none; font-weight:600; }
    .cancel-link:hover { text-decoration:underline; }

    .empty-state { text-align:center; padding:40px; color:#718096; }
    .empty-state p { font-size:16px; }

    @media(max-width:768px){
        .banner { padding:20px; flex-direction:column; align-items:flex-start; }
        .banner h1 { font-size:1.5rem; }
        .ra-card { width:100%; }
        .page-content { padding:20px 15px; }
        .stat-value { font-size:32px; }
        .stats-grid { grid-template-columns:1fr; gap:15px; }
    }
    </style>
</head>
<body>
<div class="page-wrapper">
    <?php include 'clientnavi.php'; ?>

    <div class="page-content">
    <div class="container">

    <?php if(!$emp): ?>
        <div class="banner">
            <div>
                <h1>Dashboard</h1>
                <p>Employee record not found. Please contact HR.</p>
            </div>
        </div>
    <?php else: ?>

        <!-- Banner + Reporting Authority -->
        <div class="banner">
            <div>
                <h1>👋 Welcome, <?php echo htmlspecialchars($emp['EmpName']); ?></h1>
                <p>
                    <?php echo htmlspecialchars($emp['Designation'] ?? ''); ?>
                    &nbsp;•&nbsp;
                    <?php echo htmlspecialchars($emp['Dept'] ?? ''); ?>
                </p>
            </div>
            <div class="ra-card">
                <div class="ra-label">📋 Reporting Authority</div>
                <?php if(!empty($hodName)): ?>
                    <div class="ra-name">👤 <?php echo htmlspecialchars($hodName); ?></div>
                    <div class="ra-dept">HOD &nbsp;•&nbsp; <?php echo htmlspecialchars($hodDept); ?></div>
                <?php else: ?>
                    <div class="ra-unassigned">⚠️ Not yet assigned</div>
                    <div class="ra-hint">Contact HR to assign your HOD</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">

            <!-- Total -->
            <div class="stat-card">
                <div class="stat-label">📊 Total Leaves</div>
                <div class="stat-value"><?php echo $total; ?></div>
                <div class="stat-detail">
                    Earn: <strong><?php echo $earn; ?></strong> |
                    Medical: <strong><?php echo $sick; ?></strong> |
                    Casual: <strong><?php echo $casual; ?></strong>
                </div>
            </div>

            <!-- Used -->
            <div class="stat-card used">
                <div class="stat-label">✓ Used Leaves</div>
                <div class="stat-value"><?php echo $used; ?></div>
                <div class="type-breakdown">
                    <div class="type-row">
                        <div class="type-header">
                            <span class="type-name">Earn Leave</span>
                            <span class="type-count"><?php echo $usedEarn; ?> / <?php echo $earn; ?> (<?php echo pct($usedEarn,$earn); ?>%)</span>
                        </div>
                        <div class="progress-bar"><div class="progress-fill earn" style="width:<?php echo pct($usedEarn,$earn); ?>%"></div></div>
                    </div>
                    <div class="type-row">
                        <div class="type-header">
                            <span class="type-name">Medical Leave</span>
                            <span class="type-count"><?php echo $usedSick; ?> / <?php echo $sick; ?> (<?php echo pct($usedSick,$sick); ?>%)</span>
                        </div>
                        <div class="progress-bar"><div class="progress-fill sick" style="width:<?php echo pct($usedSick,$sick); ?>%"></div></div>
                    </div>
                    <div class="type-row">
                        <div class="type-header">
                            <span class="type-name">Casual Leave</span>
                            <span class="type-count"><?php echo $usedCasual; ?> / <?php echo $casual; ?> (<?php echo pct($usedCasual,$casual); ?>%)</span>
                        </div>
                        <div class="progress-bar"><div class="progress-fill casual" style="width:<?php echo pct($usedCasual,$casual); ?>%"></div></div>
                    </div>
                </div>
                <div class="stat-detail">Approved &amp; granted requests</div>
            </div>

            <!-- Remaining -->
            <div class="stat-card remaining">
                <div class="stat-label">🎯 Remaining Leaves</div>
                <div class="stat-value"><?php echo $remaining; ?></div>
                <div class="type-breakdown">
                    <div class="type-row">
                        <div class="type-header">
                            <span class="type-name">Earn Leave</span>
                            <span class="type-count"><?php echo $remainEarn; ?> / <?php echo $earn; ?> (<?php echo pct($remainEarn,$earn); ?>%)</span>
                        </div>
                        <div class="progress-bar"><div class="progress-fill earn" style="width:<?php echo pct($remainEarn,$earn); ?>%"></div></div>
                    </div>
                    <div class="type-row">
                        <div class="type-header">
                            <span class="type-name">Medical Leave</span>
                            <span class="type-count"><?php echo $remainSick; ?> / <?php echo $sick; ?> (<?php echo pct($remainSick,$sick); ?>%)</span>
                        </div>
                        <div class="progress-bar"><div class="progress-fill sick" style="width:<?php echo pct($remainSick,$sick); ?>%"></div></div>
                    </div>
                    <div class="type-row">
                        <div class="type-header">
                            <span class="type-name">Casual Leave</span>
                            <span class="type-count"><?php echo $remainCasual; ?> / <?php echo $casual; ?> (<?php echo pct($remainCasual,$casual); ?>%)</span>
                        </div>
                        <div class="progress-bar"><div class="progress-fill casual" style="width:<?php echo pct($remainCasual,$casual); ?>%"></div></div>
                    </div>
                </div>
                <div class="stat-detail">Available quota remaining</div>
            </div>

        </div><!-- /stats-grid -->

        <!-- Recent Leave Requests -->
        <h2>📋 Recent Leave Requests</h2>
        <div class="table-card">
        <?php
        $sql2 = "SELECT * FROM emp_leaves
                 WHERE EmpName='".$conn->real_escape_string($emp['EmpName'])."'
                 ORDER BY RequestDate DESC LIMIT 10";
        $res2 = $conn->query($sql2);
        if($res2 && $res2->num_rows):
        ?>
            <h3>Last 10 Requests</h3>
            <table>
                <thead>
                    <tr>
                        <th>Request Date</th>
                        <th>Leave Type</th>
                        <th>Days</th>
                        <th>From</th>
                        <th>To</th>
                        <th>HR Status</th>
                        <th>HOD Status</th>
                        <th>Overall</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($rr = $res2->fetch_assoc()):
                    $hrSt  = $rr['HRStatus']  ?? 'Pending';
                    $hodSt = $rr['HODStatus'] ?? 'Pending';
                    $ovSt  = $rr['Status']    ?? 'Requested';
                    $rejR  = $rr['RejectionReason'] ?? '';
// If HOD rejected, HR is not required
if($hodSt==='Rejected'){
    $hrSt = 'Not Required';
    $hrCl = 'pill-na';
} elseif($hrSt==='Granted'){
    $hrCl = 'pill-granted';
} elseif($hrSt==='Rejected'){
    $hrCl = 'pill-rejected';
} else {
    $hrCl = 'pill-pending';
}
                    $hodCl = $hodSt==='Granted' ? 'pill-granted'  : ($hodSt==='Rejected' ? 'pill-rejected'  : 'pill-pending');
                    $ovCl  = $ovSt==='Granted'  ? 'ov-granted'   : ($ovSt==='Rejected'  ? 'ov-rejected'   : 'ov-pending');
                    $ovTxt = $ovSt==='Granted'  ? '✅ Approved'  : ($ovSt==='Rejected'  ? '❌ Rejected'   : '🕐 Pending');
                ?>
                <tr>
                    <td style="font-size:.82rem;color:#94a3b8;white-space:nowrap;">
                        <?php echo date('d M Y',strtotime($rr['RequestDate'])); ?>
                    </td>
                    <td><?php echo htmlspecialchars($rr['LeaveType']); ?></td>
                    <td><strong><?php echo $rr['LeaveDays']; ?></strong></td>
                    <td style="font-size:.85rem;">
                        <?php echo $rr['StartDate']; ?>
                        <?php if(!empty($rr['FromSession'])): ?>
                        <br><span style="font-size:.74rem;color:#94a3b8;"><?php echo $rr['FromSession']; ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:.85rem;">
                        <?php echo $rr['EndDate']; ?>
                        <?php if(!empty($rr['ToSession'])): ?>
                        <br><span style="font-size:.74rem;color:#94a3b8;"><?php echo $rr['ToSession']; ?></span>
                        <?php endif; ?>
                    </td>
                    <td><span class="pill <?php echo $hrCl; ?>"><?php echo $hrSt; ?></span></td>
                    <td><span class="pill <?php echo $hodCl; ?>"><?php echo $hodSt; ?></span></td>
                    <td>
                        <span class="overall-pill <?php echo $ovCl; ?>"><?php echo $ovTxt; ?></span>
                        <?php if(!empty($rejR)): ?>
                        <div class="rejection-box">
                            ❌ <strong>Reason:</strong> <?php echo htmlspecialchars($rejR); ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td style="white-space:nowrap;">
                        <a href="download.php?id=<?php echo urlencode($rr['id']); ?>" class="action-link">📄 Download</a>
                        <?php if($rr['Status']==='Requested'): ?>
                        &nbsp;
                        <a href="cancel_leave.php?id=<?php echo urlencode($rr['id']); ?>"
                           class="cancel-link"
                           onclick="return confirm('Cancel this leave request?')">🗑 Cancel</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <p>No leave requests yet.
                    <a href="request_leave.php" class="action-link">Submit your first request →</a>
                </p>
            </div>
        <?php endif; ?>
        </div>

    <?php endif; ?>
    </div>
    </div>
</div>
</body>
</html>