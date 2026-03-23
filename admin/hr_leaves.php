<?php
session_start();
include 'connect.php';

if(!isset($_SESSION['adminuser'])){
    header('location:index.php?err='.urlencode('Please Login First!'));
    exit;
}
if(($_SESSION['role'] ?? 'HOD') !== 'HR'){
    header('location:home.php?msg='.urlencode('Only HR can access this page.'));
    exit;
}

// Handle HR approve
if(isset($_GET['hr_approve'])){
    $lid    = (int)$_GET['hr_approve'];
    $leaveR = $conn->query("SELECT * FROM emp_leaves WHERE id='$lid'");
    $lv     = $leaveR->fetch_assoc();
    $newOverall = ($lv['HODStatus']==='Granted') ? 'Granted' : 'Requested';
    $conn->query("UPDATE emp_leaves SET HRStatus='Granted', Status='$newOverall' WHERE id='$lid'");
    header('location:hr_leaves.php?msg='.urlencode('Leave approved by HR'));
    exit;
}

// Handle HR reject
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['hr_reject_id'])){
    $lid    = (int)$_POST['hr_reject_id'];
    $reason = $conn->real_escape_string($_POST['rejection_reason'] ?? 'Rejected by HR');
    $conn->query("UPDATE emp_leaves SET HRStatus='Rejected', Status='Rejected', RejectionReason='$reason' WHERE id='$lid'");
    header('location:hr_leaves.php?msg='.urlencode('Leave rejected by HR'));
    exit;
}

// Pending — only HOD approved leaves
$sqlPending = "SELECT e.Id, e.EmpName, e.Dept, e.HodUsername,
    el.LeaveType, el.RequestDate, el.LeaveDays,
    el.StartDate, el.EndDate, el.id,
    el.HRStatus, el.HODStatus,
    IFNULL(el.FromSession,'')  as FromSession,
    IFNULL(el.ToSession,'')    as ToSession,
    IFNULL(el.ActivityID,'')   as ActivityID,
    IFNULL(el.Reason,'')       as Reason,
    IFNULL(el.ProofFile,'')    as ProofFile
    FROM employees e
    INNER JOIN emp_leaves el ON e.EmpName=el.EmpName AND e.Dept=el.Dept
    WHERE el.HODStatus='Granted' AND el.HRStatus='Pending'
    ORDER BY el.RequestDate DESC";

// History
$sqlHistory = "SELECT e.Id, e.EmpName, e.Dept, e.HodUsername,
    el.LeaveType, el.RequestDate, el.LeaveDays,
    el.StartDate, el.EndDate, el.id,
    el.Status, el.HRStatus, el.HODStatus,
    IFNULL(el.FromSession,'')       as FromSession,
    IFNULL(el.ToSession,'')         as ToSession,
    IFNULL(el.ActivityID,'')        as ActivityID,
    IFNULL(el.Reason,'')            as Reason,
    IFNULL(el.RejectionReason,'')   as RejectionReason,
    IFNULL(el.ProofFile,'')         as ProofFile
    FROM employees e
    INNER JOIN emp_leaves el ON e.EmpName=el.EmpName AND e.Dept=el.Dept
    WHERE el.HRStatus IN ('Granted','Rejected')
    ORDER BY el.RequestDate DESC LIMIT 60";

$resPending   = $conn->query($sqlPending);
$resHistory   = $conn->query($sqlHistory);
$pendingCount = $resPending ? $resPending->num_rows : 0;
$historyCount = $resHistory ? $resHistory->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HR Leave Approvals</title>
<link rel="stylesheet" href="style.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',sans-serif; background:#f0f4f8; min-height:100vh; }
.page-wrap { max-width:1200px; margin:0 auto; padding:30px 20px; }

.banner {
    background:linear-gradient(135deg,#065f46 0%,#10b981 100%);
    color:#fff; padding:26px 30px; border-radius:14px;
    margin-bottom:24px;
    box-shadow:0 8px 24px rgba(6,95,70,.2);
}
.banner h1 { font-size:1.6rem; margin-bottom:4px; }
.banner p  { font-size:.88rem; opacity:.85; }

.tabs { display:flex; gap:0; margin-bottom:24px; border-bottom:3px solid #e2e8f0; }
.tab-btn {
    padding:12px 28px; background:none; border:none;
    font-size:.95rem; font-weight:600; color:#64748b;
    cursor:pointer; border-bottom:3px solid transparent;
    margin-bottom:-3px; transition:all .2s;
}
.tab-btn.active { color:#065f46; border-bottom-color:#10b981; }
.tab-btn:hover  { color:#065f46; background:#f0fdf4; }
.tab-badge {
    display:inline-block; background:#e53e3e; color:#fff;
    font-size:.7rem; font-weight:800; padding:1px 7px;
    border-radius:99px; margin-left:6px; vertical-align:middle;
}
.tab-badge.green { background:#38a169; }
.tab-content { display:none; }
.tab-content.active { display:block; }

.leave-card {
    background:#fff; border-radius:14px;
    box-shadow:0 4px 18px rgba(0,0,0,.07);
    padding:20px 24px; margin-bottom:16px;
    border-left:5px solid #10b981; transition:box-shadow .2s;
}
.leave-card:hover  { box-shadow:0 8px 28px rgba(0,0,0,.12); }
.leave-card.granted  { border-left-color:#38a169; }
.leave-card.rejected { border-left-color:#e53e3e; }

.leave-top { display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px; }
.leave-info .name { font-size:1.05rem; font-weight:800; color:#1e293b; margin-bottom:6px; }
.leave-info .meta { font-size:.83rem; color:#64748b; display:flex; flex-wrap:wrap; gap:8px; }
.meta-item { background:#f1f5f9; padding:2px 10px; border-radius:99px; }

.badge-type { display:inline-block; padding:3px 12px; border-radius:99px; font-size:.75rem; font-weight:700; }
.badge-od  { background:#d1fae5; color:#065f46; }
.badge-sp  { background:#fce7f3; color:#9d174d; }
.badge-std { background:#e0e7ff; color:#4338ca; }
.badge-lop { background:#fee2e2; color:#991b1b; }

.proof-link {
    display:inline-flex; align-items:center; gap:6px;
    background:#e0e7ff; color:#4338ca;
    padding:4px 12px; border-radius:99px;
    font-size:.78rem; font-weight:700;
    text-decoration:none; transition:background .2s;
}
.proof-link:hover { background:#c7d2fe; }

.status-row {
    display:flex; gap:10px; margin-top:14px;
    padding-top:14px; border-top:1px solid #f1f5f9;
    flex-wrap:wrap; align-items:center; justify-content:space-between;
}
.status-pills { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
.spill { padding:4px 14px; border-radius:99px; font-size:.78rem; font-weight:700; }
.spill-pending  { background:#fef3c7; color:#92400e; }
.spill-granted  { background:#d1fae5; color:#065f46; }
.spill-rejected { background:#fee2e2; color:#991b1b; }

.hod-badge {
    background:#e0e7ff; color:#4338ca;
    padding:3px 12px; border-radius:99px;
    font-size:.75rem; font-weight:700;
}

.leave-actions { display:flex; gap:10px; flex-wrap:wrap; }
.btn-accept {
    padding:9px 22px; background:linear-gradient(135deg,#10b981,#065f46);
    color:#fff; border:none; border-radius:8px;
    font-weight:700; font-size:.88rem; cursor:pointer;
    text-decoration:none; transition:opacity .2s;
}
.btn-reject {
    padding:9px 22px; background:linear-gradient(135deg,#e53e3e,#c53030);
    color:#fff; border:none; border-radius:8px;
    font-weight:700; font-size:.88rem; cursor:pointer; transition:opacity .2s;
}
.btn-accept:hover,.btn-reject:hover { opacity:.85; }

.rejection-box {
    margin-top:10px; background:#fee2e2;
    border-radius:8px; padding:10px 14px;
    font-size:.83rem; color:#991b1b;
}

.empty-box {
    background:#fff; border-radius:14px; padding:50px;
    text-align:center; color:#94a3b8;
    box-shadow:0 4px 18px rgba(0,0,0,.06);
}
.empty-box .big { font-size:2.5rem; margin-bottom:12px; }

.alert-success { background:#d1fae5; color:#065f46; border-radius:10px; padding:12px 18px; margin-bottom:18px; font-weight:600; }
.alert-error   { background:#fee2e2; color:#991b1b; border-radius:10px; padding:12px 18px; margin-bottom:18px; font-weight:600; }

/* Reject Modal */
.modal-overlay {
    display:none; position:fixed; inset:0;
    background:rgba(0,0,0,.5); z-index:1000;
    align-items:center; justify-content:center;
}
.modal-overlay.open { display:flex; }
.modal {
    background:#fff; border-radius:16px; padding:30px;
    max-width:480px; width:90%;
    box-shadow:0 20px 60px rgba(0,0,0,.2);
}
.modal h3 { font-size:1.2rem; color:#1e293b; margin-bottom:6px; }
.modal p  { color:#64748b; font-size:.88rem; margin-bottom:16px; }
.modal textarea {
    width:100%; padding:12px; border:2px solid #e2e8f0;
    border-radius:10px; font-size:.92rem; font-family:inherit;
    resize:vertical; min-height:100px; transition:border .2s;
}
.modal textarea:focus { border-color:#e53e3e; outline:none; }
.modal-btns { display:flex; gap:10px; margin-top:16px; }
.btn-confirm-reject {
    flex:1; padding:12px;
    background:linear-gradient(135deg,#e53e3e,#c53030);
    color:#fff; border:none; border-radius:8px;
    font-weight:700; cursor:pointer; font-size:.92rem;
}
.btn-cancel-modal {
    padding:12px 20px; background:#f1f5f9; color:#374151;
    border:none; border-radius:8px; font-weight:700; cursor:pointer;
}
</style>
</head>
<body>
<?php include 'adminnavi.php'; ?>

<div class="page-wrap">

    <div class="banner">
        <h1>📋 HR Leave Approvals</h1>
        <p>Final approval for HOD-approved leave requests — all departments</p>
    </div>

    <?php if(isset($_GET['msg'])): ?>
    <div class="alert-success">✅ <?php echo htmlspecialchars($_GET['msg']); ?></div>
    <?php endif; ?>
    <?php if(isset($_GET['err'])): ?>
    <div class="alert-error">❌ <?php echo htmlspecialchars($_GET['err']); ?></div>
    <?php endif; ?>

    <?php if($pendingCount === 0 && $historyCount === 0): ?>
    <div class="empty-box">
        <div class="big">🎉</div>
        <p>No leave requests waiting for HR approval.<br>
        <span style="font-size:.85rem;color:#94a3b8;">Requests appear here only after HOD approval.</span></p>
    </div>
    <?php else: ?>

    <div class="tabs">
        <button class="tab-btn active" onclick="switchTab('pending',this)">
            ⏳ Pending HOD-Approved
            <?php if($pendingCount>0): ?>
            <span class="tab-badge"><?php echo $pendingCount; ?></span>
            <?php endif; ?>
        </button>
        <button class="tab-btn" onclick="switchTab('history',this)">
            📁 HR History
            <?php if($historyCount>0): ?>
            <span class="tab-badge green"><?php echo $historyCount; ?></span>
            <?php endif; ?>
        </button>
    </div>

    <!-- Pending Tab -->
    <div id="tab-pending" class="tab-content active">
    <?php if($pendingCount > 0):
        while($row = $resPending->fetch_assoc()):
            $lt = $row['LeaveType'];
            $bc = $lt==='On Duty'?'badge-od':($lt==='Special Leave'?'badge-sp':($lt==='Loss of Pay'?'badge-lop':'badge-std'));
    ?>
    <div class="leave-card">
        <div class="leave-top">
            <div class="leave-info">
                <div class="name">
                    <?php echo htmlspecialchars($row['EmpName']); ?>
                    <span style="font-size:.8rem;color:#94a3b8;font-weight:400;">
                        (<?php echo htmlspecialchars($row['Dept']); ?>)
                    </span>
                </div>
                <div class="meta">
                    <span class="meta-item">
                        <span class="badge-type <?php echo $bc; ?>"><?php echo htmlspecialchars($lt); ?></span>
                    </span>
                    <span class="meta-item">
                        📅 <?php echo $row['StartDate']; ?>
                        <?php if($row['FromSession']) echo " (".$row['FromSession'].")"; ?>
                        →
                        <?php echo $row['EndDate']; ?>
                        <?php if($row['ToSession']) echo " (".$row['ToSession'].")"; ?>
                    </span>
                    <span class="meta-item">🗓 <strong><?php echo $row['LeaveDays']; ?></strong> day(s)</span>
                    <?php if($row['ActivityID']): ?>
                    <span class="meta-item">🆔 <?php echo htmlspecialchars($row['ActivityID']); ?></span>
                    <?php endif; ?>
                    <?php if($row['Reason']): ?>
                    <span class="meta-item">📝 <?php echo htmlspecialchars(substr($row['Reason'],0,50)); ?></span>
                    <?php endif; ?>
                    <?php if(!empty($row['ProofFile'])): ?>
                    <span class="meta-item" style="background:#e0e7ff;padding:0;">
                        <a href="../client/leave_proofs/<?php echo urlencode($row['ProofFile']); ?>"
                           target="_blank" class="proof-link">
                            📎 View Proof
                        </a>
                    </span>
                    <?php endif; ?>
                    <span class="meta-item" style="color:#94a3b8;">
                        <?php echo date('d M Y',strtotime($row['RequestDate'])); ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="status-row">
            <div class="status-pills">
                <span class="spill spill-pending">HR: Pending</span>
                <span class="spill spill-granted">HOD: Granted ✅</span>
                <?php if(!empty($row['HodUsername'])): ?>
                <span class="hod-badge">👤 <?php echo htmlspecialchars($row['HodUsername']); ?></span>
                <?php endif; ?>
            </div>
            <div class="leave-actions">
                <a href="hr_leaves.php?hr_approve=<?php echo $row['id']; ?>"
                   class="btn-accept"
                   onclick="return confirm('Finally approve leave for <?php echo htmlspecialchars($row['EmpName']); ?>?')">
                   ✅ Final Approve
                </a>
                <button class="btn-reject"
                    onclick="openReject(<?php echo $row['id']; ?>,'<?php echo htmlspecialchars($row['EmpName']); ?>')">
                    ❌ Reject
                </button>
            </div>
        </div>
    </div>
    <?php endwhile;
    else: ?>
    <div class="empty-box">
        <div class="big">🎉</div>
        <p>No pending requests.<br>
        <span style="font-size:.85rem;color:#94a3b8;">Leaves appear here after HOD approves them.</span></p>
    </div>
    <?php endif; ?>
    </div>

    <!-- History Tab -->
    <div id="tab-history" class="tab-content">
    <?php if($historyCount > 0):
        while($row = $resHistory->fetch_assoc()):
            $lt      = $row['LeaveType'];
            $bc      = $lt==='On Duty'?'badge-od':($lt==='Special Leave'?'badge-sp':($lt==='Loss of Pay'?'badge-lop':'badge-std'));
            $hrSt    = $row['HRStatus'];
            $hodSt   = $row['HODStatus'];
            $cardCl  = $hrSt==='Granted'?'granted':($hrSt==='Rejected'?'rejected':'');
            $hrPill  = 'spill-'.strtolower($hrSt);
            $hodPill = 'spill-'.strtolower($hodSt);
    ?>
    <div class="leave-card <?php echo $cardCl; ?>">
        <div class="leave-top">
            <div class="leave-info">
                <div class="name">
                    <?php echo htmlspecialchars($row['EmpName']); ?>
                    <span style="font-size:.8rem;color:#94a3b8;font-weight:400;">
                        (<?php echo htmlspecialchars($row['Dept']); ?>)
                    </span>
                </div>
                <div class="meta">
                    <span class="meta-item">
                        <span class="badge-type <?php echo $bc; ?>"><?php echo htmlspecialchars($lt); ?></span>
                    </span>
                    <span class="meta-item">
                        📅 <?php echo $row['StartDate']; ?>
                        <?php if($row['FromSession']) echo " (".$row['FromSession'].")"; ?>
                        →
                        <?php echo $row['EndDate']; ?>
                        <?php if($row['ToSession']) echo " (".$row['ToSession'].")"; ?>
                    </span>
                    <span class="meta-item">🗓 <strong><?php echo $row['LeaveDays']; ?></strong> day(s)</span>
                    <?php if(!empty($row['ProofFile'])): ?>
                    <span class="meta-item" style="background:#e0e7ff;padding:0;">
                        <a href="../client/leave_proofs/<?php echo urlencode($row['ProofFile']); ?>"
                           target="_blank" class="proof-link">
                            📎 View Proof
                        </a>
                    </span>
                    <?php endif; ?>
                    <span class="meta-item" style="color:#94a3b8;">
                        <?php echo date('d M Y',strtotime($row['RequestDate'])); ?>
                    </span>
                </div>
                <?php if(!empty($row['RejectionReason'])): ?>
                <div class="rejection-box">
                    ❌ Rejection Reason: <strong><?php echo htmlspecialchars($row['RejectionReason']); ?></strong>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="status-row">
            <div class="status-pills">
                <span class="spill <?php echo $hrPill; ?>">HR: <?php echo $hrSt; ?></span>
                <span class="spill <?php echo $hodPill; ?>">HOD: <?php echo $hodSt; ?></span>
            </div>
        </div>
    </div>
    <?php endwhile;
    else: ?>
    <div class="empty-box">
        <div class="big">📭</div>
        <p>No HR history yet.</p>
    </div>
    <?php endif; ?>
    </div>

    <?php endif; ?>
</div>

<!-- Reject Modal -->
<div class="modal-overlay" id="rejectModal">
    <div class="modal">
        <h3>❌ Reject Leave Request</h3>
        <p id="rejectEmpName" style="color:#1e293b;font-weight:700;margin-bottom:6px;"></p>
        <p>Please provide a reason. This will be visible to the faculty member.</p>
        <form method="POST" action="">
            <input type="hidden" name="hr_reject_id" id="hr_reject_id">
            <textarea name="rejection_reason" placeholder="Enter reason for rejection..." required></textarea>
            <div class="modal-btns">
                <button type="button" class="btn-cancel-modal" onclick="closeReject()">Cancel</button>
                <button type="submit" class="btn-confirm-reject">❌ Confirm Reject</button>
            </div>
        </form>
    </div>
</div>

<script>
function switchTab(name,btn){
    document.querySelectorAll('.tab-content').forEach(t=>t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
    document.getElementById('tab-'+name).classList.add('active');
    btn.classList.add('active');
}
function openReject(id,name){
    document.getElementById('hr_reject_id').value = id;
    document.getElementById('rejectEmpName').textContent = '👤 '+name;
    document.getElementById('rejectModal').classList.add('open');
}
function closeReject(){
    document.getElementById('rejectModal').classList.remove('open');
}
</script>
</body>
</html>