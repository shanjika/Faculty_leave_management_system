<?php
session_start();
include 'connect.php';

if(!isset($_SESSION['user'])){
    header('location:index.php?err='.urlencode('Please Login First!'));
    exit;
}

$user      = $_SESSION['user'];
$leaveType = $conn->real_escape_string($_POST['leavetype']     ?? '');
$empname   = $conn->real_escape_string($_POST['empname']       ?? '');
$dept      = $conn->real_escape_string($_POST['dept']          ?? '');
$emptype   = $conn->real_escape_string($_POST['emptype']       ?? '');
$desig     = $conn->real_escape_string($_POST['designation']   ?? '');
$empfee    = $conn->real_escape_string($_POST['empfee']        ?? '');
$reason    = $conn->real_escape_string($_POST['leavereason']   ?? '');
$fromDate  = $conn->real_escape_string($_POST['from_date']     ?? '');
$toDate    = $conn->real_escape_string($_POST['to_date']       ?? '');
$fromSess  = $conn->real_escape_string($_POST['from_session']  ?? '');
$toSess    = $conn->real_escape_string($_POST['to_session']    ?? '');
$actId     = $conn->real_escape_string($_POST['activity_id']   ?? '');
$spReason  = $conn->real_escape_string($_POST['special_reason']?? '');

$isOnDuty  = ($leaveType === 'On Duty');
$isSpecial = ($leaveType === 'Special Leave');
$isLOP     = ($leaveType === 'Loss of Pay');

// Validate Activity ID for On Duty
if($isOnDuty && trim($_POST['activity_id'] ?? '') !== '12345'){
    header('location:leaverequest.php?type=On+Duty&err='.urlencode('Invalid Activity ID!'));
    exit;
}

// Validate dates
if(empty($fromDate) || empty($toDate)){
    header('location:leaverequest.php?type='.urlencode($leaveType).'&err='.urlencode('Please select both From and To dates!'));
    exit;
}
if(strtotime($toDate) < strtotime($fromDate)){
    header('location:leaverequest.php?type='.urlencode($leaveType).'&err='.urlencode('To Date cannot be before From Date!'));
    exit;
}

// Calculate leave days based on sessions
$d1       = new DateTime($fromDate);
$d2       = new DateTime($toDate);
$diffDays = $d1->diff($d2)->days + 1;
if($fromSess === 'AN') $diffDays -= 0.5;
if($toSess   === 'FN') $diffDays -= 0.5;
if($diffDays < 0.5)    $diffDays = 0.5;
$leaveDays = (int)ceil($diffDays);

// ── BALANCE CHECK ──
// Get employee record
$empRes = $conn->query("SELECT * FROM employees WHERE UserName='".$conn->real_escape_string($user)."'");
if(!$empRes || $empRes->num_rows === 0){
    header('location:request_leave.php?err='.urlencode('Employee record not found!'));
    exit;
}
$empData = $empRes->fetch_assoc();

$errMsg = '';

if($leaveType === 'Medical Leave'){
    if($leaveDays > (int)$empData['SickLeave']){
        $errMsg = "Insufficient Medical Leave balance! You have ".(int)$empData['SickLeave']." day(s) remaining but requested $leaveDays day(s).";
    }
} elseif($leaveType === 'Casual Leave'){
    if($leaveDays > (int)$empData['CasualLeave']){
        $errMsg = "Insufficient Casual Leave balance! You have ".(int)$empData['CasualLeave']." day(s) remaining but requested $leaveDays day(s).";
    }
} elseif($leaveType === 'Earn Leave'){
    if($leaveDays > (int)$empData['EarnLeave']){
        $errMsg = "Insufficient Earn Leave balance! You have ".(int)$empData['EarnLeave']." day(s) remaining but requested $leaveDays day(s).";
    }
}
// Loss of Pay, On Duty, Special Leave — no balance check needed

if(!empty($errMsg)){
    header('location:leaverequest.php?type='.urlencode($leaveType).'&err='.urlencode($errMsg));
    exit;
}

// Build final reason
$finalReason = $reason;
if($isSpecial && $spReason) $finalReason = $spReason.($reason ? ' – '.$reason : '');

// Insert into database
$sql = "INSERT INTO emp_leaves
        (EmpName, LeaveType, LeaveDays, StartDate, EndDate, Dept, FromSession, ToSession, ActivityID, Reason)
        VALUES
        ('$empname','$leaveType','$leaveDays','$fromDate','$toDate','$dept','$fromSess','$toSess','$actId','$finalReason')";

$success = $conn->query($sql);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Leave Request – Faculty Portal</title>
<link rel="stylesheet" href="style.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',sans-serif; background:#eef2f7; min-height:100vh; }
.wrap { max-width:560px; margin:60px auto; padding:20px; }
.card {
    background:#fff; border-radius:18px;
    box-shadow:0 8px 32px rgba(0,0,0,.10);
    padding:40px 36px; text-align:center;
}
.big-icon { font-size:4rem; }
.card h2  { font-size:1.6rem; margin:16px 0 8px; }
.card p   { color:#374151; font-size:.95rem; margin-bottom:20px; line-height:1.6; }
.card h2.success { color:#065f46; }
.card h2.error   { color:#991b1b; }
.detail-block { margin:20px 0; text-align:left; }
.detail-row {
    display:flex; justify-content:space-between;
    border-bottom:1px solid #f1f5f9; padding:10px 0; font-size:.9rem;
}
.detail-row .lbl { color:#64748b; font-weight:600; }
.detail-row .val { color:#1e293b; font-weight:700; text-align:right; }
.status-pending  { color:#d97706; font-weight:800; }
.btn-row { display:flex; gap:12px; margin-top:24px; }
.btn {
    flex:1; padding:13px; border-radius:10px;
    font-weight:700; font-size:.95rem;
    text-decoration:none; text-align:center;
    transition:opacity .2s; display:block;
}
.btn-primary   { background:linear-gradient(135deg,#667eea,#764ba2); color:#fff; }
.btn-secondary { background:#f1f5f9; color:#374151; }
.btn:hover { opacity:.88; }
.balance-box {
    background:#fef3c7; border:1px solid #fcd34d;
    border-radius:10px; padding:14px 16px;
    font-size:.88rem; color:#92400e;
    margin-bottom:16px; text-align:left;
}
.balance-row { display:flex; justify-content:space-between; padding:4px 0; font-size:.85rem; }
.balance-lbl { color:#64748b; }
.balance-val { font-weight:700; color:#1e293b; }
</style>
</head>
<body>
<?php include 'clientnavi.php'; ?>
</div>

<div class="wrap">
<div class="card">

<?php if($success): ?>
    <div class="big-icon">✅</div>
    <h2 class="success">Leave Request Submitted!</h2>
    <p>Your <strong><?php echo htmlspecialchars($leaveType); ?></strong> request has been submitted and is pending HOD approval.</p>

    <div class="detail-block">
        <div class="detail-row">
            <span class="lbl">Leave Type</span>
            <span class="val"><?php echo htmlspecialchars($leaveType); ?></span>
        </div>
        <?php if($isSpecial && $spReason): ?>
        <div class="detail-row">
            <span class="lbl">Special Type</span>
            <span class="val"><?php echo htmlspecialchars($spReason); ?></span>
        </div>
        <?php endif; ?>
        <div class="detail-row">
            <span class="lbl">From</span>
            <span class="val"><?php echo htmlspecialchars($fromDate); ?> (<?php echo htmlspecialchars($fromSess); ?>)</span>
        </div>
        <div class="detail-row">
            <span class="lbl">To</span>
            <span class="val"><?php echo htmlspecialchars($toDate); ?> (<?php echo htmlspecialchars($toSess); ?>)</span>
        </div>
        <div class="detail-row">
            <span class="lbl">Total Days</span>
            <span class="val"><?php echo $leaveDays; ?> day(s)</span>
        </div>
        <?php if($isOnDuty && $actId): ?>
        <div class="detail-row">
            <span class="lbl">Activity ID</span>
            <span class="val"><?php echo htmlspecialchars($actId); ?></span>
        </div>
        <?php endif; ?>
        <?php if($reason): ?>
        <div class="detail-row">
            <span class="lbl">Reason</span>
            <span class="val"><?php echo htmlspecialchars($reason); ?></span>
        </div>
        <?php endif; ?>
        <div class="detail-row">
            <span class="lbl">Status</span>
            <span class="val status-pending">🕐 Pending Approval</span>
        </div>
    </div>

    <div class="btn-row">
        <a href="request_leave.php" class="btn btn-secondary">← New Request</a>
        <a href="dashboard.php"     class="btn btn-primary">📊 Dashboard</a>
    </div>

<?php else: ?>
    <div class="big-icon">❌</div>
    <h2 class="error">Submission Failed</h2>
    <p>Something went wrong while submitting your leave request. Please try again.</p>
    <div class="btn-row">
        <a href="request_leave.php" class="btn btn-primary">← Go Back</a>
    </div>
<?php endif; ?>

</div>
</div>
</body>
</html>