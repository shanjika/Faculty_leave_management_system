<?php
session_start();
?>
<title>View Faculty Leaves</title>
<link rel="stylesheet" type="text/css" href="style.css">
<link rel="shortcut icon" type="image/png" href="favicon.png"/>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',sans-serif; background:#f0f4f8; min-height:100vh; }
.page-wrap { max-width:1200px; margin:0 auto; padding:30px 20px; }
h1 { color:#1e3a8a; font-size:1.8rem; margin-bottom:6px; }
.sub { color:#64748b; font-size:.9rem; margin-bottom:24px; }

/* Tabs */
.tabs { display:flex; gap:0; margin-bottom:24px; border-bottom:3px solid #e2e8f0; }
.tab-btn {
    padding:12px 28px; background:none; border:none;
    font-size:.95rem; font-weight:600; color:#64748b;
    cursor:pointer; border-bottom:3px solid transparent;
    margin-bottom:-3px; transition:all .2s;
}
.tab-btn.active { color:#1e3a8a; border-bottom-color:#667eea; }
.tab-btn:hover  { color:#1e3a8a; background:#f8faff; }
.tab-badge {
    display:inline-block; background:#e53e3e; color:#fff;
    font-size:.7rem; font-weight:800; padding:1px 7px;
    border-radius:99px; margin-left:6px; vertical-align:middle;
}
.tab-badge.green { background:#38a169; }

/* Cards */
.leave-card {
    background:#fff; border-radius:14px;
    box-shadow:0 4px 18px rgba(0,0,0,.07);
    padding:20px 24px; margin-bottom:16px;
    display:flex; justify-content:space-between;
    align-items:center; gap:16px; flex-wrap:wrap;
    border-left:5px solid #667eea;
    transition:box-shadow .2s;
}
.leave-card:hover { box-shadow:0 8px 28px rgba(0,0,0,.12); }
.leave-card.granted  { border-left-color:#38a169; }
.leave-card.rejected { border-left-color:#e53e3e; }

.leave-info { flex:1; min-width:200px; }
.leave-info .name { font-size:1.05rem; font-weight:800; color:#1e293b; margin-bottom:4px; }
.leave-info .meta { font-size:.83rem; color:#64748b; display:flex; flex-wrap:wrap; gap:8px; }
.meta-item { background:#f1f5f9; padding:2px 10px; border-radius:99px; }

.badge-type { display:inline-block; padding:3px 12px; border-radius:99px; font-size:.75rem; font-weight:700; white-space:nowrap; }
.badge-od  { background:#d1fae5; color:#065f46; }
.badge-sp  { background:#fce7f3; color:#9d174d; }
.badge-std { background:#e0e7ff; color:#4338ca; }
.badge-lop { background:#fee2e2; color:#991b1b; }

.leave-actions { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
.btn-accept {
    padding:9px 22px; background:linear-gradient(135deg,#38a169,#276749);
    color:#fff; border:none; border-radius:8px;
    font-weight:700; font-size:.88rem; cursor:pointer; text-decoration:none;
    transition:opacity .2s;
}
.btn-reject {
    padding:9px 22px; background:linear-gradient(135deg,#e53e3e,#c53030);
    color:#fff; border:none; border-radius:8px;
    font-weight:700; font-size:.88rem; cursor:pointer; text-decoration:none;
    transition:opacity .2s;
}
.btn-accept:hover,.btn-reject:hover { opacity:.85; }

.status-pill { padding:5px 16px; border-radius:99px; font-size:.82rem; font-weight:700; }
.pill-granted  { background:#d1fae5; color:#065f46; }
.pill-rejected { background:#fee2e2; color:#991b1b; }

.empty-box {
    background:#fff; border-radius:14px; padding:50px;
    text-align:center; color:#94a3b8;
    box-shadow:0 4px 18px rgba(0,0,0,.06);
}
.empty-box .big { font-size:2.5rem; margin-bottom:12px; }
.empty-box p { font-size:1rem; }

.tab-content { display:none; }
.tab-content.active { display:block; }
</style>

<?php
include 'connect.php';
include 'adminnavi.php';

if(!isset($_SESSION['adminuser'])){
    header('location:index.php?err='.urlencode('Please login first!'));
    exit;
}

$role    = $_SESSION['role'] ?? 'HOD';
if($role === 'HR'){
    echo "<div style='background:#fee2e2;color:#991b1b;padding:20px;border-radius:10px;margin:20px;'>❌ HR does not have access to leave approvals.</div>";
    exit;
}

$hodUser = $conn->real_escape_string($_SESSION['adminuser']);

// Pending leaves
$sqlPending = "SELECT e.Id, e.EmpName, e.Dept, el.LeaveType, el.RequestDate, el.LeaveDays,
                      el.StartDate, el.EndDate, el.id,
                      IFNULL(el.FromSession,'') as FromSession,
                      IFNULL(el.ToSession,'') as ToSession,
                      IFNULL(el.ActivityID,'') as ActivityID,
                      IFNULL(el.Reason,'') as Reason
               FROM employees e
               INNER JOIN emp_leaves el ON e.EmpName = el.EmpName AND e.Dept = el.Dept
               WHERE e.HodUsername = '$hodUser' AND el.Status = 'Requested'
               ORDER BY el.RequestDate DESC";

// History leaves
$sqlHistory = "SELECT e.Id, e.EmpName, e.Dept, el.LeaveType, el.RequestDate, el.LeaveDays,
                      el.StartDate, el.EndDate, el.id, el.Status,
                      IFNULL(el.FromSession,'') as FromSession,
                      IFNULL(el.ToSession,'') as ToSession,
                      IFNULL(el.ActivityID,'') as ActivityID,
                      IFNULL(el.Reason,'') as Reason
               FROM employees e
               INNER JOIN emp_leaves el ON e.EmpName = el.EmpName AND e.Dept = el.Dept
               WHERE e.HodUsername = '$hodUser' AND el.Status IN ('Granted','Rejected')
               ORDER BY el.RequestDate DESC LIMIT 50";

$resPending = $conn->query($sqlPending);
$resHistory = $conn->query($sqlHistory);
$pendingCount = $resPending ? $resPending->num_rows : 0;
$historyCount = $resHistory ? $resHistory->num_rows : 0;

function leaveCard($row, $showActions = true){
    $lt = $row['LeaveType'];
    if($lt==='On Duty')      $bc='badge-od';
    elseif($lt==='Special Leave') $bc='badge-sp';
    elseif($lt==='Loss of Pay')   $bc='badge-lop';
    else $bc='badge-std';

    $statusClass = isset($row['Status']) ? strtolower($row['Status']) : '';
    $cardClass   = $statusClass === 'granted' ? 'granted' : ($statusClass === 'rejected' ? 'rejected' : '');

    echo "<div class='leave-card $cardClass'>";
    echo "  <div class='leave-info'>";
    echo "    <div class='name'>".htmlspecialchars($row['EmpName'])." <span style='font-size:.8rem;color:#94a3b8;font-weight:400;'>(".htmlspecialchars($row['Dept']).")</span></div>";
    echo "    <div class='meta'>";
    echo "      <span class='meta-item'><span class='badge-type $bc'>".htmlspecialchars($lt)."</span></span>";
    echo "      <span class='meta-item'>📅 ".htmlspecialchars($row['StartDate']);
    if($row['FromSession']) echo " (".htmlspecialchars($row['FromSession']).")";
    echo " → ".htmlspecialchars($row['EndDate']);
    if($row['ToSession']) echo " (".htmlspecialchars($row['ToSession']).")";
    echo "</span>";
    echo "      <span class='meta-item'>🗓 <strong>".htmlspecialchars($row['LeaveDays'])."</strong> day(s)</span>";
    if($row['ActivityID']) echo "<span class='meta-item'>🆔 ".htmlspecialchars($row['ActivityID'])."</span>";
    if($row['Reason'])     echo "<span class='meta-item'>📝 ".htmlspecialchars(substr($row['Reason'],0,50))."</span>";
    echo "      <span class='meta-item' style='color:#94a3b8;'>Requested: ".htmlspecialchars($row['RequestDate'])."</span>";
    echo "    </div>";
    echo "  </div>";
    echo "  <div class='leave-actions'>";
    if($showActions){
        echo "<a href='acceptleave.php?id=".$row['id']."&empid=".$row['Id']."' class='btn-accept' onclick=\"return confirm('Approve leave for ".htmlspecialchars($row['EmpName'])."?')\">✅ Approve</a>";
        echo "<a href='rejectleave.php?id=".$row['id']."&empid=".$row['Id']."' class='btn-reject' onclick=\"return confirm('Reject leave for ".htmlspecialchars($row['EmpName'])."?')\">❌ Reject</a>";
    } else {
        $pillClass = $row['Status']==='Granted' ? 'pill-granted' : 'pill-rejected';
        $icon      = $row['Status']==='Granted' ? '✅' : '❌';
        echo "<span class='status-pill $pillClass'>$icon ".htmlspecialchars($row['Status'])."</span>";
    }
    echo "  </div>";
    echo "</div>";
}
?>

<div class="page-wrap">
<h1 style="color:#ffffff;">📋 Faculty Leave Requests</h1>
<p class="sub">Manage leave requests for your assigned faculty</p>

<div class="tabs">
    <button class="tab-btn active" onclick="switchTab('pending',this)">
        ⏳ Pending
        <?php if($pendingCount > 0): ?>
        <span class="tab-badge"><?php echo $pendingCount; ?></span>
        <?php endif; ?>
    </button>
    <button class="tab-btn" onclick="switchTab('history',this)">
        📁 History
        <?php if($historyCount > 0): ?>
        <span class="tab-badge green"><?php echo $historyCount; ?></span>
        <?php endif; ?>
    </button>
</div>

<!-- Pending Tab -->
<div id="tab-pending" class="tab-content active">
<?php if($pendingCount > 0):
    while($row = $resPending->fetch_assoc()) leaveCard($row, true);
else: ?>
    <div class="empty-box">
        <div class="big">🎉</div>
        <p>No pending leave requests. All caught up!</p>
    </div>
<?php endif; ?>
</div>

<!-- History Tab -->
<div id="tab-history" class="tab-content">
<?php if($historyCount > 0):
    while($row = $resHistory->fetch_assoc()) leaveCard($row, false);
else: ?>
    <div class="empty-box">
        <div class="big">📭</div>
        <p>No approved or rejected leaves yet.</p>
    </div>
<?php endif; ?>
</div>

</div>

<script>
function switchTab(name, btn){
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-'+name).classList.add('active');
    btn.classList.add('active');
}
</script>