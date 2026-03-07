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
$leave = $resLeave->fetch_assoc();

// Update status to Rejected
$conn->query("UPDATE emp_leaves SET Status='Rejected' WHERE id='$id'");
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Leave Rejected</title>
<link rel="stylesheet" href="style.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',sans-serif; background:#f0f4f8; min-height:100vh; }
.wrap { max-width:560px; margin:60px auto; padding:20px; }
.card {
    background:#fff; border-radius:18px;
    box-shadow:0 8px 32px rgba(0,0,0,.10);
    padding:40px 36px; text-align:center;
}
.big-icon { font-size:4rem; }
.card h2  { color:#991b1b; font-size:1.6rem; margin:16px 0 8px; }
.card p   { color:#374151; font-size:.95rem; margin-bottom:20px; }
.detail-block { margin:20px 0; text-align:left; }
.detail-row {
    display:flex; justify-content:space-between;
    border-bottom:1px solid #f1f5f9; padding:10px 0; font-size:.9rem;
}
.detail-row .lbl { color:#64748b; font-weight:600; }
.detail-row .val { color:#1e293b; font-weight:700; text-align:right; }
.status-rejected { color:#991b1b; font-weight:800; }
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
<?php include 'adminnavi.php'; ?>

<div class="wrap">
<div class="card">
    <div class="big-icon">❌</div>
    <h2>Leave Rejected</h2>
    <p>The leave request for <strong><?php echo htmlspecialchars($leave['EmpName']); ?></strong> has been rejected.</p>

    <div class="detail-block">
        <div class="detail-row">
            <span class="lbl">Faculty Name</span>
            <span class="val"><?php echo htmlspecialchars($leave['EmpName']); ?></span>
        </div>
        <div class="detail-row">
            <span class="lbl">Leave Type</span>
            <span class="val"><?php echo htmlspecialchars($leave['LeaveType']); ?></span>
        </div>
        <div class="detail-row">
            <span class="lbl">Days</span>
            <span class="val"><?php echo htmlspecialchars($leave['LeaveDays']); ?> day(s)</span>
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
            <span class="val status-rejected">❌ Rejected</span>
        </div>
    </div>

    <div class="btn-row">
        <a href="view_leaves.php" class="btn btn-secondary">← View Requests</a>
        <a href="home.php"        class="btn btn-primary">🏠 Home</a>
    </div>
</div>
</div>
</body>
</html>