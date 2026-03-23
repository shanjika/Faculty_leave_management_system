<?php
session_start();
include 'connect.php';
if(!isset($_SESSION['user'])){
    header('location:index.php?err='.urlencode('Please Login First!'));
    exit;
}
$user   = $_SESSION['user'];
$empRes = $conn->query("SELECT * FROM employees WHERE UserName='".$conn->real_escape_string($user)."'");
$emp    = ($empRes && $empRes->num_rows) ? $empRes->fetch_assoc() : null;
if(!$emp){ header('location:dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Apply for Leave</title>
<link rel="stylesheet" href="style.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',sans-serif; background:#f0f4f8; min-height:100vh; }
.page-wrap { max-width:750px; margin:0 auto; padding:30px 20px; }

.banner {
    background:linear-gradient(135deg,#667eea,#764ba2);
    color:#fff; padding:24px 28px; border-radius:14px;
    margin-bottom:24px; box-shadow:0 8px 24px rgba(102,126,234,.3);
}
.banner h1 { font-size:1.5rem; margin-bottom:4px; }
.banner p  { font-size:.88rem; opacity:.85; }

.card {
    background:#fff; border-radius:14px;
    box-shadow:0 4px 16px rgba(0,0,0,.07);
    padding:28px;
}

/* Leave type tabs */
.type-grid {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(130px,1fr));
    gap:10px; margin-bottom:28px;
}
.type-btn {
    padding:12px 8px; border:2px solid #e2e8f0;
    border-radius:10px; background:#fafafa;
    font-size:.82rem; font-weight:700; color:#64748b;
    cursor:pointer; text-align:center; transition:all .2s;
}
.type-btn:hover  { border-color:#667eea; color:#667eea; background:#f0f4ff; }
.type-btn.active { border-color:#667eea; background:#667eea; color:#fff; }
.type-icon { font-size:1.4rem; display:block; margin-bottom:5px; }

/* Form */
.form-section { margin-bottom:22px; }
.section-label {
    font-size:.75rem; font-weight:700; color:#94a3b8;
    text-transform:uppercase; letter-spacing:.6px;
    margin-bottom:12px; padding-bottom:8px;
    border-bottom:2px solid #f1f5f9;
}
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.form-group { margin-bottom:16px; }
.form-group label {
    display:block; font-size:.78rem; font-weight:700;
    color:#374151; text-transform:uppercase;
    letter-spacing:.5px; margin-bottom:6px;
}
.form-group input,
.form-group select,
.form-group textarea {
    width:100%; padding:11px 14px;
    border:2px solid #e2e8f0; border-radius:9px;
    font-size:.92rem; font-family:inherit;
    background:#fafafa; color:#1e293b;
    transition:border .2s;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus { border-color:#667eea; outline:none; background:#fff; }
.form-group textarea { resize:vertical; min-height:80px; }

/* Proof upload */
.proof-box {
    border:2px dashed #667eea; border-radius:12px;
    padding:20px; text-align:center; background:#f8faff;
    cursor:pointer; transition:all .2s; position:relative;
}
.proof-box:hover { background:#f0f4ff; border-color:#764ba2; }
.proof-box input[type="file"] {
    position:absolute; inset:0; opacity:0; cursor:pointer; width:100%;
}
.proof-icon  { font-size:2rem; margin-bottom:8px; }
.proof-title { font-weight:700; color:#667eea; font-size:.95rem; }
.proof-sub   { font-size:.78rem; color:#94a3b8; margin-top:4px; }
.proof-preview {
    margin-top:12px; background:#e0e7ff; border-radius:8px;
    padding:8px 14px; font-size:.82rem; color:#4338ca;
    font-weight:600; display:none;
}

.required-note {
    background:#fef3c7; border:1px solid #fcd34d;
    border-radius:10px; padding:12px 16px;
    font-size:.85rem; color:#92400e; margin-bottom:16px;
}

/* Warning boxes */
.warn-box {
    border-radius:10px; padding:14px 16px;
    font-size:.85rem; margin-bottom:16px; display:none;
}
.warn-lop { background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; }
.warn-od  { background:#d1fae5; border:1px solid #6ee7b7; color:#065f46; }
.warn-sp  { background:#fce7f3; border:1px solid #f9a8d4; color:#9d174d; }

/* Activity ID */
.actid-wrap { position:relative; }
.actid-status {
    position:absolute; right:14px; top:50%;
    transform:translateY(-50%); font-size:1.1rem;
}

.submit-btn {
    width:100%; padding:14px;
    background:linear-gradient(135deg,#667eea,#764ba2);
    color:#fff; border:none; border-radius:12px;
    font-size:1rem; font-weight:800;
    cursor:pointer; transition:opacity .2s,transform .2s;
    margin-top:8px;
}
.submit-btn:hover { opacity:.9; transform:translateY(-1px); }

.err-box {
    background:#fee2e2; border:1px solid #fca5a5;
    border-radius:10px; padding:12px 16px;
    color:#991b1b; font-weight:600; margin-bottom:18px;
}

@media(max-width:600px){
    .form-row { grid-template-columns:1fr; }
    .type-grid { grid-template-columns:repeat(3,1fr); }
}
</style>
</head>
<body>
<?php include 'clientnavi.php'; ?>
</div>

<div class="page-wrap">

    <div class="banner">
        <h1>📝 Apply for Leave</h1>
        <p>Hello <?php echo htmlspecialchars($emp['EmpName']); ?> — fill in the details below</p>
    </div>

    <?php if(isset($_GET['err'])): ?>
    <div class="err-box">⚠️ <?php echo htmlspecialchars($_GET['err']); ?></div>
    <?php endif; ?>

    <div class="card">

        <!-- Leave Type Selection -->
        <div class="form-section">
            <div class="section-label">Step 1 — Select Leave Type</div>
            <div class="type-grid">
                <div class="type-btn" onclick="selectType('Medical Leave',this)">
                    <span class="type-icon">🏥</span>Medical Leave
                </div>
                <div class="type-btn" onclick="selectType('Casual Leave',this)">
                    <span class="type-icon">🌴</span>Casual Leave
                </div>
                <div class="type-btn" onclick="selectType('Earn Leave',this)">
                    <span class="type-icon">📅</span>Earn Leave
                </div>
                <div class="type-btn" onclick="selectType('Loss of Pay',this)">
                    <span class="type-icon">💸</span>Loss of Pay
                </div>
                <div class="type-btn" onclick="selectType('On Duty',this)">
                    <span class="type-icon">🏛️</span>On Duty
                </div>
                <div class="type-btn" onclick="selectType('Special Leave',this)">
                    <span class="type-icon">⭐</span>Special Leave
                </div>
            </div>
        </div>

        <form action="request_confirm.php" method="POST"
              enctype="multipart/form-data"
              onsubmit="return validateForm()">

            <input type="hidden" name="leavetype"    id="leavetype_input" required>
            <input type="hidden" name="empname"      value="<?php echo htmlspecialchars($emp['EmpName']); ?>">
            <input type="hidden" name="dept"         value="<?php echo htmlspecialchars($emp['Dept']); ?>">
            <input type="hidden" name="emptype"      value="<?php echo htmlspecialchars($emp['EmpType']); ?>">
            <input type="hidden" name="designation"  value="<?php echo htmlspecialchars($emp['Designation']); ?>">
            <input type="hidden" name="empfee"       value="<?php echo htmlspecialchars($emp['EmpFee'] ?? ''); ?>">

            <!-- Date & Session -->
            <div class="form-section">
                <div class="section-label">Step 2 — Select Dates & Sessions</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>From Date</label>
                        <input type="date" name="from_date" id="from_date" required
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label>From Session</label>
                        <select name="from_session">
                            <option value="FN">FN (Forenoon)</option>
                            <option value="AN">AN (Afternoon)</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>To Date</label>
                        <input type="date" name="to_date" id="to_date" required
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label>To Session</label>
                        <select name="to_session">
                            <option value="AN">AN (Afternoon)</option>
                            <option value="FN">FN (Forenoon)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Warning boxes -->
            <div class="warn-box warn-lop" id="warn_lop">
                💸 <strong>Loss of Pay Warning:</strong> This leave type will result in salary deduction for the applied days.
            </div>
            <div class="warn-box warn-od" id="warn_od">
                🏛️ <strong>On Duty:</strong> You must enter a valid Activity ID and upload proof document.
            </div>
            <div class="warn-box warn-sp" id="warn_sp">
                ⭐ <strong>Special Leave:</strong> You must upload supporting proof document for this leave.
            </div>

            <!-- On Duty: Activity ID -->
            <div class="form-section" id="section_actid" style="display:none;">
                <div class="section-label">Activity Details</div>
                <div class="form-group">
                    <label>Activity ID <span style="color:#e53e3e;">*</span></label>
                    <div class="actid-wrap">
                        <input type="text" name="activity_id" id="activity_id"
                               placeholder="Enter Activity ID (e.g. 12345)"
                               oninput="checkActId(this.value)">
                        <span class="actid-status" id="actid_status"></span>
                    </div>
                </div>
            </div>

            <!-- Special Leave: Sub-type -->
            <div class="form-section" id="section_special" style="display:none;">
                <div class="section-label">Special Leave Type</div>
                <div class="form-group">
                    <label>Select Type <span style="color:#e53e3e;">*</span></label>
                    <select name="special_reason" id="special_reason">
                        <option value="">— Select —</option>
                        <?php if(strtolower($emp['Gender'] ?? '') === 'female'): ?>
                        <option value="Maternity Leave">Maternity Leave</option>
                        <?php endif; ?>
                        <option value="Special Casual Leave">Special Casual Leave</option>
                    </select>
                </div>
            </div>

            <!-- Proof Upload — shown for On Duty and Special Leave -->
            <div class="form-section" id="section_proof" style="display:none;">
                <div class="section-label">Step 3 — Upload Proof Document</div>
                <div class="required-note">
                    📎 <strong>Proof is mandatory</strong> for On Duty and Special Leave.
                    Upload a PDF, image or document as supporting evidence.
                    This will be visible to HOD and HR when reviewing your request.
                </div>
                <div class="proof-box" id="proofBox">
                    <input type="file" name="proof_file" id="proof_file"
                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                           onchange="showPreview(this)">
                    <div class="proof-icon">📎</div>
                    <div class="proof-title">Click to upload proof</div>
                    <div class="proof-sub">PDF, JPG, PNG, DOC — Max 5MB</div>
                    <div class="proof-preview" id="proofPreview"></div>
                </div>
            </div>

            <!-- Reason -->
            <div class="form-section">
                <div class="section-label" id="reason_label">Step 3 — Reason</div>
                <div class="form-group">
                    <label>Reason for Leave</label>
                    <textarea name="leavereason" placeholder="Briefly describe the reason for your leave..."></textarea>
                </div>
            </div>

            <button type="submit" class="submit-btn">📤 Submit Leave Request</button>
        </form>
    </div>
</div>

<script>
var currentType = '';

function selectType(type, el){
    currentType = type;
    document.getElementById('leavetype_input').value = type;

    // Reset all buttons
    document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');

    // Hide all special sections
    document.getElementById('section_actid').style.display  = 'none';
    document.getElementById('section_special').style.display = 'none';
    document.getElementById('section_proof').style.display  = 'none';
    document.getElementById('warn_lop').style.display = 'none';
    document.getElementById('warn_od').style.display  = 'none';
    document.getElementById('warn_sp').style.display  = 'none';
    document.getElementById('reason_label').textContent = 'Step 3 — Reason';

    // Show relevant sections
    if(type === 'On Duty'){
        document.getElementById('section_actid').style.display = 'block';
        document.getElementById('section_proof').style.display = 'block';
        document.getElementById('warn_od').style.display = 'block';
        document.getElementById('reason_label').textContent = 'Step 4 — Reason';
    } else if(type === 'Special Leave'){
        document.getElementById('section_special').style.display = 'block';
        document.getElementById('section_proof').style.display  = 'block';
        document.getElementById('warn_sp').style.display = 'block';
        document.getElementById('reason_label').textContent = 'Step 4 — Reason';
    } else if(type === 'Loss of Pay'){
        document.getElementById('warn_lop').style.display = 'block';
    }
}

function checkActId(val){
    var el = document.getElementById('actid_status');
    if(val === '12345'){ el.textContent = '✅'; }
    else if(val.length > 0){ el.textContent = '❌'; }
    else { el.textContent = ''; }
}

function showPreview(input){
    var preview = document.getElementById('proofPreview');
    if(input.files && input.files[0]){
        var file = input.files[0];
        // Check size (5MB)
        if(file.size > 5 * 1024 * 1024){
            alert('File too large! Maximum size is 5MB.');
            input.value = '';
            preview.style.display = 'none';
            return;
        }
        preview.style.display = 'block';
        preview.textContent   = '📎 ' + file.name + ' (' + (file.size/1024).toFixed(1) + ' KB)';
    }
}

function validateForm(){
    if(!currentType){
        alert('Please select a leave type!');
        return false;
    }
    var fromDate = document.getElementById('from_date').value;
    var toDate   = document.getElementById('to_date').value;
    if(!fromDate || !toDate){
        alert('Please select both From and To dates!');
        return false;
    }
    if(toDate < fromDate){
        alert('To Date cannot be before From Date!');
        return false;
    }
    if(currentType === 'On Duty'){
        var actId = document.getElementById('activity_id').value;
        if(actId !== '12345'){
            alert('Please enter a valid Activity ID!');
            return false;
        }
        var proof = document.getElementById('proof_file');
        if(!proof.files || proof.files.length === 0){
            alert('Please upload proof document for On Duty leave!');
            return false;
        }
    }
    if(currentType === 'Special Leave'){
        var spReason = document.getElementById('special_reason').value;
        if(!spReason){
            alert('Please select a Special Leave type!');
            return false;
        }
        var proof = document.getElementById('proof_file');
        if(!proof.files || proof.files.length === 0){
            alert('Please upload proof document for Special Leave!');
            return false;
        }
    }
    return true;
}
</script>
</body>
</html>