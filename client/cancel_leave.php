<?php
session_start();
include 'connect.php';

if(!isset($_SESSION['user'])){
    header('location:index.php?err='.urlencode('Please Login First!'));
    exit;
}

$user = $_SESSION['user'];
$id   = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if(!$id){
    header('location:dashboard.php');
    exit;
}

// Get the leave record — make sure it belongs to this user and is still pending
$empRes = $conn->query("SELECT EmpName FROM employees WHERE UserName='".$conn->real_escape_string($user)."'");
if(!$empRes || $empRes->num_rows === 0){
    header('location:dashboard.php');
    exit;
}
$emp     = $empRes->fetch_assoc();
$empName = $conn->real_escape_string($emp['EmpName']);

$leaveRes = $conn->query("SELECT * FROM emp_leaves WHERE id='$id' AND EmpName='$empName'");
if(!$leaveRes || $leaveRes->num_rows === 0){
    header('location:dashboard.php?err='.urlencode('Leave record not found!'));
    exit;
}
$leave = $leaveRes->fetch_assoc();

// Only allow cancel if still pending
if($leave['Status'] !== 'Requested'){
    header('location:dashboard.php?err='.urlencode('Only pending leaves can be cancelled!'));
    exit;
}

// Delete the leave request
$conn->query("DELETE FROM emp_leaves WHERE id='$id' AND EmpName='$empName' AND Status='Requested'");
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Leave Cancelled</title>
<link rel="stylesheet" href="style.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',sans-serif; background:#f0f4f8; min-height:100vh; }
.wrap { max-width:520px; margin:60px auto; padding:20px; }
.card {
    background:#fff; border-radius:18px;
    box-shadow:0 8px 32px rgba(0,0,0,.10);
    padding:40px 36px; text-align:center;
}
.big-icon { font-size:4rem; }
.card h2  { color:#991b1b; font-size:1.5rem; margin:16px 0 8px; }
.card p   { color:#374151; font-size:.95rem; margin-bottom:20px; line-height:1.6; }
.detail-block { margin:20px 0; text-align:left; }
.detail-row {
    display:flex; justify-content:space-between;
    border-bottom:1px solid #f1f5f9; padding:10px 0; font-size:.9rem;
}
.detail-row .lbl { color:#64748b; font-weight:600; }
.detail-row .val { color:#1e293b; font-weight:700; text-align:right; }
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
</style>
</head>
<body>
<?php include 'clientnavi.php'; ?>
</div>

<div class="wrap">
<div class="card">
    <div class="big-icon">🗑️</div>
    <h2>Leave Request Cancelled</h2>
    <p>Your leave request has been successfully cancelled and removed from the system.</p>

    <div class="detail-block">
        <div class="detail-row">
            <span class="lbl">Leave Type</span>
            <span class="val"><?php echo htmlspecialchars($leave['LeaveType']); ?></span>
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
            <span class="lbl">Days</span>
            <span class="val"><?php echo htmlspecialchars($leave['LeaveDays']); ?> day(s)</span>
        </div>
        <div class="detail-row">
            <span class="lbl">Status</span>
            <span class="val" style="color:#991b1b;">🗑 Cancelled</span>
        </div>
    </div>

    <div class="btn-row">
        <a href="request_leave.php" class="btn btn-secondary">📋 New Request</a>
        <a href="dashboard.php"     class="btn btn-primary">📊 Dashboard</a>
    </div>
</div>
</div>
</body>
</html>