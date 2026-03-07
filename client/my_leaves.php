<?php
session_start();
include 'connect.php';
if(!isset($_SESSION['user'])){
    header('location:index.php?err='.urlencode('Please Login First!'));
    exit;
}

$user = $_SESSION['user'];

// Get employee
$empRes = $conn->query("SELECT EmpName FROM employees WHERE UserName='".$conn->real_escape_string($user)."'");
if(!$empRes || $empRes->num_rows === 0){
    header('location:dashboard.php');
    exit;
}
$emp     = $empRes->fetch_assoc();
$empName = $conn->real_escape_string($emp['EmpName']);

// Filter values
$filterStatus = $conn->real_escape_string($_GET['status'] ?? 'all');
$filterType   = $conn->real_escape_string($_GET['type']   ?? 'all');

// Build WHERE clause
$where = "EmpName='$empName'";
if($filterStatus !== 'all') $where .= " AND Status='$filterStatus'";
if($filterType   !== 'all') $where .= " AND LeaveType='$filterType'";

// Get filtered leaves
$leavesRes = $conn->query("SELECT * FROM emp_leaves WHERE $where ORDER BY RequestDate DESC");
$totalRes  = $conn->query("SELECT COUNT(*) as cnt FROM emp_leaves WHERE EmpName='$empName'");
$total     = $totalRes ? (int)$totalRes->fetch_assoc()['cnt'] : 0;

// Count by status
function countStatus($conn,$empName,$status){
    $r = $conn->query("SELECT COUNT(*) as cnt FROM emp_leaves WHERE EmpName='$empName' AND Status='$status'");
    return $r ? (int)$r->fetch_assoc()['cnt'] : 0;
}
$cPending  = countStatus($conn,$empName,'Requested');
$cGranted  = countStatus($conn,$empName,'Granted');
$cRejected = countStatus($conn,$empName,'Rejected');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Leave History</title>
<link rel="stylesheet" href="style.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',sans-serif; background:#f0f4f8; min-height:100vh; }
.page-wrap { max-width:1100px; margin:0 auto; padding:30px 20px; }

/* Banner */
.banner {
    background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    color:#fff; padding:26px 30px; border-radius:14px;
    margin-bottom:24px;
    box-shadow:0 8px 24px rgba(102,126,234,.3);
}
.banner h1 { font-size:1.5rem; margin-bottom:4px; }
.banner p  { font-size:.88rem; opacity:.85; }

/* Summary pills */
.summary-row {
    display:flex; gap:14px; flex-wrap:wrap;
    margin-bottom:24px;
}
.summary-pill {
    background:#fff; border-radius:10px;
    padding:14px 20px; text-align:center;
    box-shadow:0 3px 12px rgba(0,0,0,.07);
    min-width:120px; flex:1;
    border-top:3px solid #667eea;
}
.summary-pill.pending  { border-top-color:#f59e0b; }
.summary-pill.approved { border-top-color:#10b981; }
.summary-pill.rejected { border-top-color:#ef4444; }
.pill-val   { font-size:1.8rem; font-weight:800; color:#1e293b; }
.pill-label { font-size:.75rem; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-top:3px; }

/* Filter bar */
.filter-bar {
    background:#fff; border-radius:12px;
    box-shadow:0 3px 12px rgba(0,0,0,.07);
    padding:18px 20px; margin-bottom:20px;
    display:flex; gap:14px; flex-wrap:wrap;
    align-items:flex-end;
}
.filter-group { display:flex; flex-direction:column; gap:5px; flex:1; min-width:160px; }
.filter-group label { font-size:.78rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.5px; }
.filter-group select {
    padding:10px 12px; border:2px solid #e2e8f0;
    border-radius:8px; font-size:.9rem;
    background:#fafafa; color:#1e293b;
    cursor:pointer; transition:border .2s;
}
.filter-group select:focus { border-color:#667eea; outline:none; }
.filter-btn {
    padding:10px 24px; background:linear-gradient(135deg,#667eea,#764ba2);
    color:#fff; border:none; border-radius:8px;
    font-weight:700; font-size:.9rem; cursor:pointer;
    transition:opacity .2s; align-self:flex-end;
}
.filter-btn:hover { opacity:.88; }
.reset-btn {
    padding:10px 18px; background:#f1f5f9;
    color:#374151; border:none; border-radius:8px;
    font-weight:700; font-size:.9rem; cursor:pointer;
    align-self:flex-end; text-decoration:none;
    display:inline-block; text-align:center;
}
.reset-btn:hover { background:#e2e8f0; }
.result-count {
    font-size:.82rem; color:#64748b;
    align-self:flex-end; padding-bottom:10px;
    white-space:nowrap;
}

/* Table */
.table-card {
    background:#fff; border-radius:14px;
    box-shadow:0 4px 16px rgba(0,0,0,.07);
    padding:22px 24px; overflow-x:auto;
}
table { width:100%; border-collapse:collapse; }
th {
    padding:12px; text-align:left;
    font-size:.78rem; font-weight:700;
    color:#64748b; text-transform:uppercase;
    background:#f8fafc; border-bottom:2px solid #e2e8f0;
    white-space:nowrap;
}
td {
    padding:14px 12px; font-size:.88rem;
    border-bottom:1px solid #f1f5f9; color:#1e293b;
}
tr:last-child td { border-bottom:none; }
tr:hover td { background:#f8faff; }

.badge { display:inline-block; padding:3px 10px; border-radius:99px; font-size:.74rem; font-weight:700; white-space:nowrap; }
.badge-requested { background:#fef3c7; color:#92400e; }
.badge-granted   { background:#d1fae5; color:#065f46; }
.badge-approved  { background:#d1fae5; color:#065f46; }
.badge-rejected  { background:#fee2e2; color:#991b1b; }
.badge-type-od   { background:#d1fae5; color:#065f46; }
.badge-type-sp   { background:#fce7f3; color:#9d174d; }
.badge-type-std  { background:#e0e7ff; color:#4338ca; }
.badge-type-lop  { background:#fee2e2; color:#991b1b; }

.action-link  { color:#667eea; text-decoration:none; font-weight:600; font-size:.85rem; }
.action-link:hover { text-decoration:underline; }
.cancel-link  { color:#ef4444; text-decoration:none; font-weight:600; font-size:.85rem; }
.cancel-link:hover { text-decoration:underline; }

.empty-state {
    text-align:center; padding:50px 20px; color:#94a3b8;
}
.empty-state .icon { font-size:3rem; margin-bottom:12px; }
.empty-state p { font-size:1rem; }

.active-filter-tag {
    display:inline-block; background:#e0e7ff; color:#4338ca;
    padding:3px 12px; border-radius:99px; font-size:.78rem;
    font-weight:700; margin-left:8px;
}

@media(max-width:600px){
    .filter-bar { flex-direction:column; }
    .summary-row { gap:8px; }
}
</style>
</head>
<body>
<?php include 'clientnavi.php'; ?>
</div>

<div class="page-wrap">

    <!-- Banner -->
    <div class="banner">
        <h1>📋 My Leave History</h1>
        <p>View and manage all your leave requests — <?php echo htmlspecialchars($emp['EmpName']); ?></p>
    </div>

    <!-- Summary pills -->
    <div class="summary-row">
        <div class="summary-pill">
            <div class="pill-val"><?php echo $total; ?></div>
            <div class="pill-label">Total</div>
        </div>
        <div class="summary-pill pending">
            <div class="pill-val"><?php echo $cPending; ?></div>
            <div class="pill-label">Pending</div>
        </div>
        <div class="summary-pill approved">
            <div class="pill-val"><?php echo $cGranted; ?></div>
            <div class="pill-label">Approved</div>
        </div>
        <div class="summary-pill rejected">
            <div class="pill-val"><?php echo $cRejected; ?></div>
            <div class="pill-label">Rejected</div>
        </div>
    </div>

    <!-- Filter bar -->
    <form method="GET" action="">
    <div class="filter-bar">
        <div class="filter-group">
            <label>Filter by Status</label>
            <select name="status">
                <option value="all"       <?php echo $filterStatus==='all'       ?'selected':''; ?>>All Statuses</option>
                <option value="Requested" <?php echo $filterStatus==='Requested' ?'selected':''; ?>>⏳ Pending</option>
                <option value="Granted"   <?php echo $filterStatus==='Granted'   ?'selected':''; ?>>✅ Approved</option>
                <option value="Rejected"  <?php echo $filterStatus==='Rejected'  ?'selected':''; ?>>❌ Rejected</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Filter by Leave Type</label>
            <select name="type">
                <option value="all"          <?php echo $filterType==='all'          ?'selected':''; ?>>All Types</option>
                <option value="Medical Leave" <?php echo $filterType==='Medical Leave'?'selected':''; ?>>🏥 Medical Leave</option>
                <option value="Casual Leave"  <?php echo $filterType==='Casual Leave' ?'selected':''; ?>>🌴 Casual Leave</option>
                <option value="Earn Leave"    <?php echo $filterType==='Earn Leave'   ?'selected':''; ?>>📅 Earn Leave</option>
                <option value="Loss of Pay"   <?php echo $filterType==='Loss of Pay'  ?'selected':''; ?>>💸 Loss of Pay</option>
                <option value="On Duty"       <?php echo $filterType==='On Duty'      ?'selected':''; ?>>🏛️ On Duty</option>
                <option value="Special Leave" <?php echo $filterType==='Special Leave'?'selected':''; ?>>⭐ Special Leave</option>
            </select>
        </div>
        <button type="submit" class="filter-btn">🔍 Apply Filter</button>
        <a href="my_leaves.php" class="reset-btn">↺ Reset</a>
        <?php
        $shown = $leavesRes ? $leavesRes->num_rows : 0;
        echo "<span class='result-count'>Showing <strong>$shown</strong> of <strong>$total</strong> records</span>";
        ?>
    </div>
    </form>

    <!-- Active filter tags -->
    <?php if($filterStatus !== 'all' || $filterType !== 'all'): ?>
    <div style="margin-bottom:14px;font-size:.85rem;color:#64748b;">
        Active filters:
        <?php if($filterStatus !== 'all'): ?>
        <span class="active-filter-tag">Status: <?php echo htmlspecialchars($filterStatus); ?></span>
        <?php endif; ?>
        <?php if($filterType !== 'all'): ?>
        <span class="active-filter-tag">Type: <?php echo htmlspecialchars($filterType); ?></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="table-card">
    <?php if($leavesRes && $leavesRes->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Leave Type</th>
                <th>From</th>
                <th>To</th>
                <th>Days</th>
                <th>Reason</th>
                <th>Requested On</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $i = 1;
        while($row = $leavesRes->fetch_assoc()):
            $lt = $row['LeaveType'];
            if($lt==='On Duty')           $tc='badge-type-od';
            elseif($lt==='Special Leave') $tc='badge-type-sp';
            elseif($lt==='Loss of Pay')   $tc='badge-type-lop';
            else                          $tc='badge-type-std';
            $sc = 'badge-'.strtolower($row['Status']);
        ?>
        <tr>
            <td style="color:#94a3b8;"><?php echo $i++; ?></td>
            <td><span class="badge <?php echo $tc; ?>"><?php echo htmlspecialchars($lt); ?></span></td>
            <td>
                <?php echo htmlspecialchars($row['StartDate']); ?>
                <?php if(!empty($row['FromSession'])): ?>
                <br><span style="font-size:.75rem;color:#94a3b8;"><?php echo htmlspecialchars($row['FromSession']); ?></span>
                <?php endif; ?>
            </td>
            <td>
                <?php echo htmlspecialchars($row['EndDate']); ?>
                <?php if(!empty($row['ToSession'])): ?>
                <br><span style="font-size:.75rem;color:#94a3b8;"><?php echo htmlspecialchars($row['ToSession']); ?></span>
                <?php endif; ?>
            </td>
            <td><strong><?php echo htmlspecialchars($row['LeaveDays']); ?></strong></td>
            <td style="max-width:160px;color:#64748b;font-size:.82rem;">
                <?php echo htmlspecialchars(substr($row['Reason'] ?? '',0,60)); ?>
                <?php if(strlen($row['Reason'] ?? '') > 60) echo '...'; ?>
            </td>
            <td style="color:#94a3b8;font-size:.8rem;white-space:nowrap;">
                <?php echo date('d M Y', strtotime($row['RequestDate'])); ?>
            </td>
            <td><span class="badge <?php echo $sc; ?>"><?php echo htmlspecialchars($row['Status']); ?></span></td>
            <td style="white-space:nowrap;">
                <a href="download.php?id=<?php echo urlencode($row['id']); ?>" class="action-link">📄 Download</a>
                <?php if($row['Status'] === 'Requested'): ?>
                &nbsp;
                <a href="cancel_leave.php?id=<?php echo urlencode($row['id']); ?>"
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
        <div class="icon">📭</div>
        <p>
        <?php if($filterStatus !== 'all' || $filterType !== 'all'): ?>
            No records match your filter. <a href="my_leaves.php" style="color:#667eea;font-weight:600;">Clear filters</a>
        <?php else: ?>
            No leave requests yet. <a href="request_leave.php" style="color:#667eea;font-weight:600;">Apply for leave →</a>
        <?php endif; ?>
        </p>
    </div>
    <?php endif; ?>
    </div>

</div>
</body>
</html>