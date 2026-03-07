<?php
session_start();
include 'connect.php';

if(!isset($_SESSION['adminuser'])){
    header('location:index.php?err='.urlencode('Please Login First!'));
    exit;
}

$id    = filter_var($_GET['id']    ?? 0, FILTER_VALIDATE_INT);
$empid = filter_var($_GET['empid'] ?? 0, FILTER_VALIDATE_INT);

if(!$id || !$empid){
    header('location:view_leaves.php');
    exit;
}

// Get leave record
$resLeave = $conn->query("SELECT * FROM emp_leaves WHERE id='$id'");
if(!$resLeave || $resLeave->num_rows === 0){
    header('location:view_leaves.php?err='.urlencode('Leave record not found!'));
    exit;
}
$leave     = $resLeave->fetch_assoc();
$leaveDays = (int)$leave['LeaveDays'];
$leaveType = $leave['LeaveType'];

// Get employee record
$resEmp = $conn->query("SELECT * FROM employees WHERE id='$empid'");
if(!$resEmp || $resEmp->num_rows === 0){
    header('location:view_leaves.php?err='.urlencode('Employee record not found!'));
    exit;
}
$emp = $resEmp->fetch_assoc();

// ── BALANCE CHECK ON ADMIN SIDE TOO ──
$balanceErr = '';
if($leaveType === 'Medical Leave' || $leaveType === 'Sick Leave'){
    if($leaveDays > (int)$emp['SickLeave']){
        $balanceErr = "Cannot approve — faculty only has ".(int)$emp['SickLeave']." Medical Leave day(s) remaining but requested $leaveDays day(s).";
    }
} elseif($leaveType === 'Casual Leave'){
    if($leaveDays > (int)$emp['CasualLeave']){
        $balanceErr = "Cannot approve — faculty only has ".(int)$emp['CasualLeave']." Casual Leave day(s) remaining but requested $leaveDays day(s).";
    }
} elseif($leaveType === 'Earn Leave'){
    if($leaveDays > (int)$emp['EarnLeave']){
        $balanceErr = "Cannot approve — faculty only has ".(int)$emp['EarnLeave']." Earn Leave day(s) remaining but requested $leaveDays day(s).";
    }
}

// If balance error — show error page, do NOT approve
if(!empty($balanceErr)){
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Cannot Approve</title>
<link rel="stylesheet" href="style.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',sans-serif; background:#f0f4f8; min-height:100vh; }
.wrap { max-width:520px; margin:60px auto; padding:20px; }
.card { background:#fff; border-radius:18px; box-shadow:0 8px 32px rgba(0,0,0,.10); padding:40px 36px; text-align:center; }
.big-icon { font-size:4rem; }
.card h2 { color:#991b1b; font-size:1.4rem; margin:16px 0 10px; }
.card p  { color:#374151; font-size:.95rem; margin-bottom:20px; line-height:1.6; }
.err-box { background:#fee2e2; border:1px solid #fca5a5; border-radius:10px; padding:14px 16px; color:#991b1b; font-weight:600; font-size:.9rem; margin-bottom:20px; }
.btn { display:block; padding:13px; border-radius:10px; font-weight:700; font-size:.95rem; text-decoration:none; text-align:center; background:linear-gradient(135deg,#667eea,#764ba2); color:#fff; margin-top:16px; }
.btn:hover { opacity:.88; }
</style>
</head>
<body>
<?php include 'adminnavi.php'; ?>
<div class="wrap"><div class="card">
    <div class="big-icon">⚠️</div>
    <h2>Cannot Approve Leave</h2>
    <div class="err-box">❌ <?php echo htmlspecialchars($balanceErr); ?></div>
    <p>Please reject this request or ask the faculty to apply for Loss of Pay instead.</p>
    <a href="view_leaves.php" class="btn">← Back to Leave Requests</a>
</div></div>
</body></html>
<?php
    $conn->close();
    exit;
}

// Deduct balance for standard leave types
$updateSql = '';
if($leaveType === 'Medical Leave' || $leaveType === 'Sick Leave'){
    $newBal    = (int)$emp['SickLeave'] - $leaveDays;
    $updateSql = "UPDATE employees SET SickLeave='$newBal' WHERE id='$empid'";
} elseif($leaveType === 'Casual Leave'){
    $newBal    = (int)$emp['CasualLeave'] - $leaveDays;
    $updateSql = "UPDATE employees SET CasualLeave='$newBal' WHERE id='$empid'";
} elseif($leaveType === 'Earn Leave'){
    $newBal    = (int)$emp['EarnLeave'] - $leaveDays;
    $updateSql = "UPDATE employees SET EarnLeave='$newBal' WHERE id='$empid'";
}
// Loss of Pay, On Duty, Special Leave — no deduction

if($updateSql) $conn->query($updateSql);

// Mark as Granted
$conn->query("UPDATE emp_leaves SET Status='Granted' WHERE id='$id'");
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Leave Approved</title>
<link rel="stylesheet" href="style.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',sans-serif; background:#f0f4f8; min-height:100vh; }
.wrap { max-width:560px; margin:60px auto; padding:20px; }
.card { background:#fff; border-radius:18px; box-shadow:0 8px 32px rgba(0,0,0,.10); padding:40px 36px; text-align:center; }
.big-icon { font-size:4rem; }
.card h2  { color:#065f46; font-size:1.6rem; margin:16px 0 8px; }
.card p   { color:#374151; font-size:.95rem; margin-bottom:20px; }
.detail-block { margin:20px 0; text-align:left; }
.detail-row { display:flex; justify-content:space-between; border-bottom:1px solid #f1f5f9; padding:10px 0; font-size:.9rem; }
.detail-row .lbl { color:#64748b; font-weight:600; }
.detail-row .val { color:#1e293b; font-weight:700; text-align:right; }
.status-granted { color:#065f46; font-weight:800; }
.btn-row { display:flex; gap:12px; margin-top:24px; }
.btn { flex:1; padding:13px; border-radius:10px; font-weight:700; font-size:.95rem; text-decoration:none; text-align:center; transition:opacity .2s; display:block; }
.btn-primary   { background:linear-gradient(135deg,#667eea,#764ba2); color:#fff; }
.btn-secondary { background:#f1f5f9; color:#374151; }
.btn:hover { opacity:.88; }
.balance-note { background:#d1fae5; border:1px solid #6ee7b7; border-radius:10px; padding:12px 16px; font-size:.85rem; color:#065f46; margin-bottom:16px; text-align:left; }
</style>
</head>
<body>
<?php include 'adminnavi.php'; ?>

<div class="wrap">
<div class="card">
    <div class="big-icon">✅</div>
    <h2>Leave Approved!</h2>
    <p>The leave request for <strong><?php echo htmlspecialchars($leave['EmpName']); ?></strong> has been successfully approved.</p>

    <?php if($updateSql): ?>
    <div class="balance-note">
        💰 Leave balance updated — <strong><?php echo $leaveDays; ?></strong> day(s) deducted from <?php echo htmlspecialchars($leaveType); ?> quota.
    </div>
    <?php else: ?>
    <div class="balance-note" style="background:#e0f2fe;border-color:#7dd3fc;color:#0c4a6e;">
        ℹ️ <?php echo htmlspecialchars($leaveType); ?> — no balance deduction applied.
    </div>
    <?php endif; ?>

    <div class="detail-block">
        <div class="detail-row">
            <span class="lbl">Faculty Name</span>
            <span class="val"><?php echo htmlspecialchars($leave['EmpName']); ?></span>
        </div>
        <div class="detail-row">
            <span class="lbl">Leave Type</span>
            <span class="val"><?php echo htmlspecialchars($leaveType); ?></span>
        </div>
        <div class="detail-row">
            <span class="lbl">Days</span>
            <span class="val"><?php echo $leaveDays; ?> day(s)</span>
        </div>
        <div class="detail-row">
            <span class="lbl">From</span>
            <span class="val"><?php echo htmlspecialchars($leave['StartDate']); ?></span>
        </div>
        <div class="detail-row">
            <span class="lbl">To</span>
            <span class="val"><?php echo htmlspecialchars($leave['EndDate']); ?></span>
        </div>
        <div class="detail-row">
            <span class="lbl">Status</span>
            <span class="val status-granted">✅ Granted</span>
        </div>
        <?php if(isset($newBal)): ?>
        <div class="detail-row">
            <span class="lbl">Remaining Balance</span>
            <span class="val"><?php echo $newBal; ?> day(s) left</span>
        </div>
        <?php endif; ?>
    </div>

    <div class="btn-row">
        <a href="view_leaves.php" class="btn btn-secondary">← View Requests</a>
        <a href="home.php"        class="btn btn-primary">🏠 Home</a>
    </div>
</div>
</div>
</body>
</html>