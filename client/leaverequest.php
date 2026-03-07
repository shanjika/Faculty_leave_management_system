<?php
session_start();
include 'connect.php';

if(!isset($_SESSION['user'])){
    header('location:index.php?err='.urlencode('Please Login First!'));
    exit;
}
if(!isset($_POST['type'])){
    header('location:request_leave.php');
    exit;
}

$user      = $_SESSION['user'];
$leaveType = $_POST['type'];

$sql = "SELECT * FROM employees WHERE UserName='".$conn->real_escape_string($user)."'";
$res = $conn->query($sql);
$emp = ($res && $res->num_rows) ? $res->fetch_assoc() : null;

if(!$emp){
    header('location:request_leave.php?err='.urlencode('Employee record not found!'));
    exit;
}

$gender    = strtolower(trim($emp['Gender'] ?? ''));
if(!in_array($gender,['male','female','other'])) $gender = 'male';

$isOnDuty  = ($leaveType === 'On Duty');
$isSpecial = ($leaveType === 'Special Leave');
$isLOP     = ($leaveType === 'Loss of Pay');

if($gender === 'female'){
    $specialReasons = ['Maternity Leave','Special Casual Leave'];
} else {
    $specialReasons = ['Special Casual Leave'];
}
?>
<?php include 'clientnavi.php'; ?>
</div>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Leave Request – Faculty Portal</title>
<link rel="stylesheet" href="style.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; background:#eef2f7; min-height:100vh; }
.page-wrap { max-width:640px; margin:0 auto; padding:36px 20px 60px; }
.form-card {
    background:#fff; border-radius:18px;
    box-shadow:0 8px 32px rgba(0,0,0,.10);
    padding:36px 36px 30px;
}
.form-header { text-align:center; padding-bottom:22px; border-bottom:2px solid #f1f5f9; margin-bottom:28px; }
.form-header .big-icon { font-size:3rem; }
.form-header h2 { font-size:1.55rem; font-weight:800; color:#1e3a8a; margin:10px 0 4px; }
.form-header p  { color:#64748b; font-size:.88rem; }
.badge-pill { display:inline-block; padding:4px 16px; border-radius:99px; font-size:.75rem; font-weight:700; margin-top:8px; }
.b-std { background:#e0e7ff; color:#4338ca; }
.b-od  { background:#d1fae5; color:#065f46; }
.b-lop { background:#fee2e2; color:#991b1b; }
.b-sp  { background:#fce7f3; color:#9d174d; }
.form-group { margin-bottom:20px; }
.form-group label { display:block; font-weight:700; font-size:.83rem; color:#374151; margin-bottom:7px; text-transform:uppercase; letter-spacing:.5px; }
.form-group label .req { color:#e53e3e; margin-left:2px; }
.form-group input[type=text],
.form-group input[type=date],
.form-group select,
.form-group textarea {
    width:100%; padding:12px 14px; border:2px solid #e2e8f0;
    border-radius:10px; font-size:.98rem; font-family:inherit;
    background:#fafafa; transition:border .2s,box-shadow .2s; color:#1e293b;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color:#667eea; box-shadow:0 0 0 3px rgba(102,126,234,.12);
    outline:none; background:#fff;
}
.form-group textarea { resize:vertical; min-height:85px; }
.row-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.info-box {
    background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px;
    padding:12px 16px; font-size:.86rem; color:#1e40af;
    margin-bottom:22px; display:flex; align-items:flex-start; gap:10px;
}
.warn-box {
    background:#fff7ed; border:1px solid #fed7aa; border-radius:10px;
    padding:12px 16px; font-size:.86rem; color:#92400e; margin-bottom:22px;
}
.error-box {
    background:#fee2e2; border:1px solid #fca5a5; border-radius:10px;
    padding:12px 16px; font-size:.9rem; color:#991b1b;
    margin-bottom:22px; font-weight:600;
}
.divider { border:none; border-top:2px solid #f1f5f9; margin:22px 0; }
.submit-btn {
    width:100%; padding:14px;
    background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    color:#fff; border:none; border-radius:12px;
    font-size:1.05rem; font-weight:800;
    cursor:pointer; transition:opacity .2s,transform .2s;
}
.submit-btn:hover { opacity:.91; transform:translateY(-1px); }
.back-link { display:block; text-align:center; margin-top:16px; color:#667eea; font-weight:600; text-decoration:none; font-size:.9rem; }
.back-link:hover { text-decoration:underline; }
@media(max-width:500px){ .form-card{padding:22px 16px;} .row-2{grid-template-columns:1fr;} }
</style>
</head>
<body>
<div class="page-wrap">
<div class="form-card">

<?php
$icons = [
    'Medical Leave'=>'🏥','Casual Leave'=>'🌴','Loss of Pay'=>'💸',
    'On Duty'=>'🏛️','Special Leave'=>'⭐',
];
$icon = $icons[$leaveType] ?? '📋';
if($isOnDuty)      $bc='b-od';
elseif($isSpecial) $bc='b-sp';
elseif($isLOP)     $bc='b-lop';
else               $bc='b-std';
?>

<div class="form-header">
    <div class="big-icon"><?php echo $icon; ?></div>
    <h2><?php echo htmlspecialchars($leaveType); ?></h2>
    <p>Faculty: <strong><?php echo htmlspecialchars($emp['EmpName']); ?></strong>
       &nbsp;|&nbsp; Dept: <strong><?php echo htmlspecialchars($emp['Dept']); ?></strong></p>
    <span class="badge-pill <?php echo $bc; ?>"><?php echo htmlspecialchars($leaveType); ?></span>
</div>

<?php if(isset($_GET['err'])): ?>
<div class="error-box">⚠️ <?php echo htmlspecialchars($_GET['err']); ?></div>
<?php endif; ?>

<?php if($isOnDuty): ?>
<div class="info-box">
    <span style="font-size:1.1rem;flex-shrink:0;">ℹ️</span>
    <span>On Duty requires a valid <strong>Activity ID</strong> and <strong>session</strong> (FN=Forenoon, AN=Afternoon).</span>
</div>
<?php elseif($isLOP): ?>
<div class="warn-box">⚠️ <strong>Loss of Pay</strong> leave results in salary deduction for the applied days.</div>
<?php elseif($isSpecial): ?>
<div class="info-box">
    <span style="font-size:1.1rem;flex-shrink:0;">⭐</span>
    <span>Select the <strong>type of Special Leave</strong> below. Supporting documents may be required.</span>
</div>
<?php endif; ?>

<form action="request_confirm.php" method="post" id="leaveForm" onsubmit="return validateForm()">
    <input type="hidden" name="leavetype"   value="<?php echo htmlspecialchars($leaveType); ?>">
    <input type="hidden" name="empname"     value="<?php echo htmlspecialchars($emp['EmpName']); ?>">
    <input type="hidden" name="designation" value="<?php echo htmlspecialchars($emp['Designation']); ?>">
    <input type="hidden" name="dept"        value="<?php echo htmlspecialchars($emp['Dept']); ?>">
    <input type="hidden" name="emptype"     value="<?php echo htmlspecialchars($emp['EmpType']); ?>">
    <input type="hidden" name="empfee"      value="<?php echo htmlspecialchars($emp['EmpFee']); ?>">

    <?php if($isSpecial): ?>
    <div class="form-group">
        <label>Type of Special Leave <span class="req">*</span></label>
        <select name="special_reason" id="special_reason" required>
            <option value="">-- Select Type --</option>
            <?php foreach($specialReasons as $sr): ?>
            <option value="<?php echo htmlspecialchars($sr); ?>"><?php echo htmlspecialchars($sr); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>

    <?php if($isOnDuty): ?>
    <!-- ON DUTY: date + session + activity id -->
    <div class="row-2">
        <div class="form-group">
            <label>From Date <span class="req">*</span></label>
            <input type="date" name="from_date" id="from_date" required min="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="form-group">
            <label>From Session <span class="req">*</span></label>
            <select name="from_session" required>
                <option value="">-- Select --</option>
                <option value="FN">FN (Forenoon)</option>
                <option value="AN">AN (Afternoon)</option>
            </select>
        </div>
    </div>
    <div class="row-2">
        <div class="form-group">
            <label>To Date <span class="req">*</span></label>
            <input type="date" name="to_date" id="to_date" required min="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="form-group">
            <label>To Session <span class="req">*</span></label>
            <select name="to_session" required>
                <option value="">-- Select --</option>
                <option value="FN">FN (Forenoon)</option>
                <option value="AN">AN (Afternoon)</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label>Activity ID <span class="req">*</span></label>
        <input type="text" name="activity_id" id="activity_id" placeholder="Enter Activity ID" required maxlength="20">
    </div>

    <?php else: ?>
    <!-- ALL OTHER LEAVES: from date + session + to date + session -->
    <div class="row-2">
        <div class="form-group">
            <label>From Date <span class="req">*</span></label>
            <input type="date" name="from_date" id="from_date" required min="<?php echo date('Y-m-d',strtotime('+1 day')); ?>">
        </div>
        <div class="form-group">
            <label>From Session <span class="req">*</span></label>
            <select name="from_session" required>
                <option value="">-- Select --</option>
                <option value="FN">FN (Forenoon)</option>
                <option value="AN">AN (Afternoon)</option>
            </select>
        </div>
    </div>
    <div class="row-2">
        <div class="form-group">
            <label>To Date <span class="req">*</span></label>
            <input type="date" name="to_date" id="to_date" required min="<?php echo date('Y-m-d',strtotime('+1 day')); ?>">
        </div>
        <div class="form-group">
            <label>To Session <span class="req">*</span></label>
            <select name="to_session" required>
                <option value="">-- Select --</option>
                <option value="FN">FN (Forenoon)</option>
                <option value="AN">AN (Afternoon)</option>
            </select>
        </div>
    </div>
    <?php endif; ?>

    <div class="form-group">
        <label><?php echo $isSpecial ? 'Additional Details' : 'Reason'; ?><?php echo $isSpecial ? '' : ' <span class="req">*</span>'; ?></label>
        <textarea name="leavereason"
            placeholder="<?php echo $isSpecial ? 'Any additional details (optional)...' : 'Briefly describe your reason...'; ?>"
            <?php echo $isSpecial ? '' : 'required'; ?>></textarea>
    </div>

    <hr class="divider">
    <button type="submit" class="submit-btn">📨 Submit Leave Request</button>
</form>

<a href="request_leave.php" class="back-link">← Back to Leave Selection</a>
</div>
</div>

<script>
function validateForm(){
    var fd = new Date(document.getElementById('from_date').value);
    var td = new Date(document.getElementById('to_date').value);
    if(td < fd){ alert('To Date cannot be before From Date.'); return false; }

    <?php if($isOnDuty): ?>
    var actId = document.getElementById('activity_id').value.trim();
    if(actId !== '12345'){
        alert('❌ Invalid Activity ID!\nPlease enter the correct Activity ID.');
        document.getElementById('activity_id').style.borderColor='#e53e3e';
        document.getElementById('activity_id').focus();
        return false;
    }
    <?php endif; ?>

    <?php if($isSpecial): ?>
    var sr = document.getElementById('special_reason').value;
    if(!sr){ alert('Please select the type of Special Leave.'); return false; }
    <?php endif; ?>

    return true;
}
<?php if($isOnDuty): ?>
document.getElementById('activity_id').addEventListener('input',function(){
    this.style.borderColor = this.value.trim()==='12345' ? '#48bb78' : '#e2e8f0';
});
<?php endif; ?>
</script>
</body>
</html>