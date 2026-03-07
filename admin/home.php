<?php
session_start();
include 'connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Faculty Leave Management System</title>
<link rel="shortcut icon" type="image/png" href="favicon.png"/>
<link rel="stylesheet" type="text/css" href="style.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',sans-serif; background:#f0f4f8; min-height:100vh; }
.page-wrap { max-width:1100px; margin:0 auto; padding:30px 20px; }

/* Banner */
.banner {
    background:linear-gradient(135deg,#1e3a8a 0%,#3b82f6 100%);
    color:#fff; padding:28px 32px; border-radius:16px;
    margin-bottom:28px;
    box-shadow:0 8px 28px rgba(30,58,138,.25);
    display:flex; justify-content:space-between;
    align-items:center; flex-wrap:wrap; gap:12px;
}
.banner h1 { font-size:1.7rem; margin-bottom:4px; }
.banner p  { font-size:.9rem; opacity:.85; }
.role-pill {
    background:rgba(255,255,255,.18); color:#fff;
    padding:6px 18px; border-radius:99px;
    font-weight:800; font-size:.85rem; letter-spacing:.5px;
}

/* Stat cards */
.stats-grid {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:18px; margin-bottom:28px;
}
.stat-card {
    background:#fff; border-radius:14px;
    box-shadow:0 4px 16px rgba(0,0,0,.07);
    padding:22px 24px;
    border-top:4px solid #667eea;
    transition:transform .2s,box-shadow .2s;
}
.stat-card:hover { transform:translateY(-4px); box-shadow:0 10px 28px rgba(0,0,0,.11); }
.stat-card.pending  { border-top-color:#f59e0b; }
.stat-card.approved { border-top-color:#10b981; }
.stat-card.rejected { border-top-color:#ef4444; }
.stat-card.faculty  { border-top-color:#8b5cf6; }
.stat-icon  { font-size:2rem; margin-bottom:10px; }
.stat-label { font-size:.78rem; text-transform:uppercase; font-weight:700; color:#94a3b8; letter-spacing:.6px; margin-bottom:6px; }
.stat-value { font-size:2.2rem; font-weight:800; color:#1e293b; }
.stat-sub   { font-size:.78rem; color:#94a3b8; margin-top:4px; }

/* Action blocks */
.section-title { font-size:1.1rem; font-weight:700; color:#1e293b; margin:24px 0 14px; }
.action-grid {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:18px; margin-bottom:28px;
}
.action-block {
    background:#fff; border-radius:14px;
    box-shadow:0 4px 16px rgba(0,0,0,.07);
    padding:28px 22px; text-align:center;
    text-decoration:none; color:#1e293b;
    font-size:1rem; font-weight:700;
    transition:transform .2s,box-shadow .2s,background .2s;
    display:flex; flex-direction:column;
    align-items:center; gap:10px;
}
.action-block:hover {
    transform:translateY(-4px);
    box-shadow:0 12px 28px rgba(102,126,234,.18);
    background:#f6f8ff; color:#1e3a8a;
}
.action-icon { font-size:2rem; }

/* Recent table */
.table-card {
    background:#fff; border-radius:14px;
    box-shadow:0 4px 16px rgba(0,0,0,.07);
    padding:22px 24px; overflow-x:auto;
}
.table-card h3 { font-size:1rem; font-weight:700; color:#1e293b; margin-bottom:16px; }
table { width:100%; border-collapse:collapse; }
th { padding:11px 12px; text-align:left; font-size:.78rem; font-weight:700; color:#64748b; text-transform:uppercase; background:#f8fafc; border-bottom:2px solid #e2e8f0; }
td { padding:13px 12px; font-size:.88rem; border-bottom:1px solid #f1f5f9; color:#1e293b; }
tr:last-child td { border-bottom:none; }
tr:hover td { background:#f8faff; }
.badge { display:inline-block; padding:3px 10px; border-radius:99px; font-size:.74rem; font-weight:700; }
.badge-pending  { background:#fef3c7; color:#92400e; }
.badge-granted  { background:#d1fae5; color:#065f46; }
.badge-rejected { background:#fee2e2; color:#991b1b; }
.badge-type-od  { background:#d1fae5; color:#065f46; }
.badge-type-sp  { background:#fce7f3; color:#9d174d; }
.badge-type-std { background:#e0e7ff; color:#4338ca; }
.badge-type-lop { background:#fee2e2; color:#991b1b; }

.msg-box { background:#d1fae5; color:#065f46; border-radius:10px; padding:12px 18px; margin-bottom:18px; font-weight:600; }
.empty { text-align:center; padding:30px; color:#94a3b8; font-size:.95rem; }

/* HR blocks */
.hr-banner {
    background:linear-gradient(135deg,#065f46 0%,#10b981 100%);
    color:#fff; padding:28px 32px; border-radius:16px;
    margin-bottom:28px;
    box-shadow:0 8px 28px rgba(6,95,70,.2);
    display:flex; justify-content:space-between;
    align-items:center; flex-wrap:wrap; gap:12px;
}
.hr-banner h1 { font-size:1.7rem; margin-bottom:4px; }
.hr-banner p  { font-size:.9rem; opacity:.85; }

@media(max-width:600px){
    .banner,.hr-banner { flex-direction:column; align-items:flex-start; }
    .stat-value { font-size:1.8rem; }
}
</style>
</head>
<body>
<?php include 'adminnavi.php'; ?>

<div class="page-wrap">
<?php
if(!isset($_SESSION['adminuser'])){
    header('location:index.php?err='.urlencode('Please Login first'));
    exit;
}

$role    = $_SESSION['role'] ?? 'HOD';
$hodUser = $conn->real_escape_string($_SESSION['adminuser']);

if(isset($_GET['msg'])){
    echo "<div class='msg-box'>".htmlspecialchars($_GET['msg'])."</div>";
}

// ══════════════════════════════════════
// HOD HOME
// ══════════════════════════════════════
if($role === 'HOD'):

// Count assigned faculty
$rFac     = $conn->query("SELECT COUNT(*) as cnt FROM employees WHERE HodUsername='$hodUser'");
$facCount = $rFac ? (int)$rFac->fetch_assoc()['cnt'] : 0;

// Count pending leaves
$rPend     = $conn->query("SELECT COUNT(*) as cnt FROM emp_leaves el
    INNER JOIN employees e ON e.EmpName=el.EmpName AND e.Dept=el.Dept
    WHERE e.HodUsername='$hodUser' AND el.Status='Requested'");
$pendCount = $rPend ? (int)$rPend->fetch_assoc()['cnt'] : 0;

// Count approved today
$rToday     = $conn->query("SELECT COUNT(*) as cnt FROM emp_leaves el
    INNER JOIN employees e ON e.EmpName=el.EmpName AND e.Dept=el.Dept
    WHERE e.HodUsername='$hodUser' AND el.Status='Granted'
    AND DATE(el.RequestDate)=CURDATE()");
$todayCount = $rToday ? (int)$rToday->fetch_assoc()['cnt'] : 0;

// Count total approved this month
$rMonth     = $conn->query("SELECT COUNT(*) as cnt FROM emp_leaves el
    INNER JOIN employees e ON e.EmpName=el.EmpName AND e.Dept=el.Dept
    WHERE e.HodUsername='$hodUser' AND el.Status='Granted'
    AND MONTH(el.RequestDate)=MONTH(CURDATE())
    AND YEAR(el.RequestDate)=YEAR(CURDATE())");
$monthCount = $rMonth ? (int)$rMonth->fetch_assoc()['cnt'] : 0;

// Count total rejected
$rRej     = $conn->query("SELECT COUNT(*) as cnt FROM emp_leaves el
    INNER JOIN employees e ON e.EmpName=el.EmpName AND e.Dept=el.Dept
    WHERE e.HodUsername='$hodUser' AND el.Status='Rejected'");
$rejCount = $rRej ? (int)$rRej->fetch_assoc()['cnt'] : 0;

// Recent 5 pending requests
$rRecent = $conn->query("SELECT e.EmpName, el.LeaveType, el.LeaveDays,
    el.StartDate, el.EndDate, el.Status, el.RequestDate,
    IFNULL(el.FromSession,'') as FromSession,
    IFNULL(el.ToSession,'') as ToSession
    FROM emp_leaves el
    INNER JOIN employees e ON e.EmpName=el.EmpName AND e.Dept=el.Dept
    WHERE e.HodUsername='$hodUser'
    ORDER BY el.RequestDate DESC LIMIT 5");
?>

<!-- HOD Banner -->
<div class="banner">
    <div>
        <h1>👋 Welcome, <?php echo htmlspecialchars($_SESSION['adminuser']); ?></h1>
        <p>Here's your faculty leave overview for today</p>
    </div>
    <span class="role-pill">HOD</span>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card faculty">
        <div class="stat-icon">👥</div>
        <div class="stat-label">Assigned Faculty</div>
        <div class="stat-value"><?php echo $facCount; ?></div>
        <div class="stat-sub">Under your department</div>
    </div>
    <div class="stat-card pending">
        <div class="stat-icon">⏳</div>
        <div class="stat-label">Pending Requests</div>
        <div class="stat-value"><?php echo $pendCount; ?></div>
        <div class="stat-sub">Awaiting your approval</div>
    </div>
    <div class="stat-card approved">
        <div class="stat-icon">✅</div>
        <div class="stat-label">Approved This Month</div>
        <div class="stat-value"><?php echo $monthCount; ?></div>
        <div class="stat-sub"><?php echo $todayCount; ?> approved today</div>
    </div>
    <div class="stat-card rejected">
        <div class="stat-icon">❌</div>
        <div class="stat-label">Total Rejected</div>
        <div class="stat-value"><?php echo $rejCount; ?></div>
        <div class="stat-sub">All time</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="section-title">⚡ Quick Actions</div>
<div class="action-grid">
    <a class="action-block" href="view_leaves.php">
        <span class="action-icon">📋</span>
        View Leave Requests
        <?php if($pendCount > 0): ?>
        <span style="background:#ef4444;color:#fff;padding:2px 10px;border-radius:99px;font-size:.75rem;"><?php echo $pendCount; ?> pending</span>
        <?php endif; ?>
    </a>
    <a class="action-block" href="set_leaves.php">
        <span class="action-icon">⚙️</span>
        Set Default Leaves
    </a>
    <a class="action-block" href="extract_leaves.php">
        <span class="action-icon">📤</span>
        Extract Leaves
    </a>
    <a class="action-block" href="reports.php">
        <span class="action-icon">📊</span>
        Reports & Statistics
    </a>
</div>

<!-- Recent Requests -->
<div class="section-title">🕐 Recent Leave Activity</div>
<div class="table-card">
    <h3>Last 5 Requests</h3>
    <?php if($rRecent && $rRecent->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Faculty</th>
                <th>Leave Type</th>
                <th>Days</th>
                <th>From</th>
                <th>To</th>
                <th>Status</th>
                <th>Requested</th>
            </tr>
        </thead>
        <tbody>
        <?php while($r = $rRecent->fetch_assoc()):
            $lt = $r['LeaveType'];
            if($lt==='On Duty')          $tc='badge-type-od';
            elseif($lt==='Special Leave') $tc='badge-type-sp';
            elseif($lt==='Loss of Pay')   $tc='badge-type-lop';
            else                          $tc='badge-type-std';
            $sc = 'badge-'.strtolower($r['Status']);
        ?>
        <tr>
            <td><strong><?php echo htmlspecialchars($r['EmpName']); ?></strong></td>
            <td><span class="badge <?php echo $tc; ?>"><?php echo htmlspecialchars($lt); ?></span></td>
            <td><?php echo $r['LeaveDays']; ?></td>
            <td><?php echo $r['StartDate']; ?><?php if($r['FromSession']) echo " (".$r['FromSession'].")"; ?></td>
            <td><?php echo $r['EndDate']; ?><?php if($r['ToSession']) echo " (".$r['ToSession'].")"; ?></td>
            <td><span class="badge <?php echo $sc; ?>"><?php echo $r['Status']; ?></span></td>
            <td style="color:#94a3b8;font-size:.8rem;"><?php echo date('d M Y', strtotime($r['RequestDate'])); ?></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty">No recent leave activity found.</div>
    <?php endif; ?>
</div>

<?php
// ══════════════════════════════════════
// HR HOME
// ══════════════════════════════════════
elseif($role === 'HR'):

// Count total faculty
$rTotal     = $conn->query("SELECT COUNT(*) as cnt FROM employees");
$totalFac   = $rTotal ? (int)$rTotal->fetch_assoc()['cnt'] : 0;

// Count unassigned faculty
$rUnassign     = $conn->query("SELECT COUNT(*) as cnt FROM employees WHERE HodUsername IS NULL OR HodUsername=''");
$unassignCount = $rUnassign ? (int)$rUnassign->fetch_assoc()['cnt'] : 0;

// Count total HODs
$rHods    = $conn->query("SELECT COUNT(*) as cnt FROM admins WHERE Role='HOD'");
$hodCount = $rHods ? (int)$rHods->fetch_assoc()['cnt'] : 0;
?>

<!-- HR Banner -->
<div class="hr-banner">
    <div>
        <h1>👋 Welcome, <?php echo htmlspecialchars($_SESSION['adminuser']); ?></h1>
        <p>Manage faculty members and HOD assignments</p>
    </div>
    <span class="role-pill" style="background:rgba(255,255,255,.2);">HR</span>
</div>

<!-- HR Stats -->
<div class="stats-grid">
    <div class="stat-card faculty">
        <div class="stat-icon">👥</div>
        <div class="stat-label">Total Faculty</div>
        <div class="stat-value"><?php echo $totalFac; ?></div>
        <div class="stat-sub">Registered in system</div>
    </div>
    <div class="stat-card pending">
        <div class="stat-icon">⚠️</div>
        <div class="stat-label">Unassigned Faculty</div>
        <div class="stat-value"><?php echo $unassignCount; ?></div>
        <div class="stat-sub">Not assigned to any HOD</div>
    </div>
    <div class="stat-card approved">
        <div class="stat-icon">🏫</div>
        <div class="stat-label">Total HODs</div>
        <div class="stat-value"><?php echo $hodCount; ?></div>
        <div class="stat-sub">Active in system</div>
    </div>
</div>

<!-- HR Actions -->
<div class="section-title">⚡ Quick Actions</div>
<div class="action-grid">
    <a class="action-block" href="register">
        <span class="action-icon">📝</span>
        Add New Faculty
    </a>
    <a class="action-block" href="empdelete.php">
        <span class="action-icon">🗑️</span>
        Delete Faculty
    </a>
    <a class="action-block" href="assign_hod.php">
        <span class="action-icon">🔗</span>
        Assign Faculty to HOD
        <?php if($unassignCount > 0): ?>
        <span style="background:#ef4444;color:#fff;padding:2px 10px;border-radius:99px;font-size:.75rem;"><?php echo $unassignCount; ?> unassigned</span>
        <?php endif; ?>
    </a>
    <a class="action-block" href="faculty_list.php">
        <span class="action-icon">📋</span>
        View All Faculty
    </a>
</div>

<?php endif; ?>
</div>
</body>
</html>