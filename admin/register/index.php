<?php
session_start();
if(!isset($_SESSION['adminuser'])){
    header('location:../index.php?err='.urlencode('Please Login First!'));
    exit();
}
if(($_SESSION['role'] ?? 'HOD') !== 'HR'){
    header('location:../home.php?msg='.urlencode('Only HR can register faculty.'));
    exit();
}

include 'connect.php';
// Get all departments from admins table
$depts = [];
$resDept = $conn->query("SELECT DISTINCT Dept FROM admins ORDER BY Dept");
if($resDept) while($r = $resDept->fetch_assoc()) $depts[] = $r['Dept'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register New Faculty - Leave Management</title>
    <link rel="shortcut icon" type="image/png" href="favicon.png"/>
    <link rel="stylesheet" href="style.css">
    <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
        font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
        background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
        min-height:100vh; padding:20px;
    }
    .container { max-width:900px; margin:0 auto; }
    .header-banner {
        background:#fff; padding:20px; border-radius:10px;
        margin-bottom:30px; box-shadow:0 4px 15px rgba(0,0,0,.1);
        text-align:center;
    }
    .header-banner h1 { color:#667eea; font-size:26px; margin-bottom:5px; }
    .header-banner p  { color:#718096; font-size:14px; }
    .reg-card {
        background:#fff; border-radius:12px;
        box-shadow:0 10px 30px rgba(0,0,0,.15);
        padding:40px; margin-bottom:30px;
    }
    .form-title {
        font-size:22px; color:#2d3748;
        margin-bottom:10px; font-weight:600;
        display:flex; align-items:center; gap:10px;
    }
    .form-subtitle {
        color:#718096; font-size:13px;
        margin-bottom:25px; padding-bottom:15px;
        border-bottom:2px solid #e2e8f0;
    }
    .form-section { margin-bottom:30px; }
    .section-title {
        font-size:16px; font-weight:600; color:#2d3748;
        margin-bottom:15px; padding-bottom:8px;
        border-left:4px solid #667eea; padding-left:12px;
    }
    .form-row {
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
        gap:20px; margin-bottom:20px;
    }
    .form-group { display:flex; flex-direction:column; }
    .form-group label {
        font-size:13px; font-weight:600; color:#4a5568;
        margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px;
    }
    .required { color:#e74c3c; margin-left:2px; }
    .form-group input,
    .form-group select {
        padding:12px; border:2px solid #e2e8f0;
        border-radius:6px; font-size:14px;
        font-family:inherit; transition:all .3s;
        background:#f8f9fa;
    }
    .form-group input:focus,
    .form-group select:focus {
        outline:none; border-color:#667eea;
        background:#fff; box-shadow:0 0 0 3px rgba(102,126,234,.1);
    }
    .form-group input::placeholder { color:#cbd5e0; }
    .date-row { display:flex; gap:12px; align-items:flex-end; }
    .date-row .form-group { flex:1; min-width:80px; }
    .date-row .form-group label { font-size:11px; }
    .date-row input { text-align:center; padding:10px 6px; font-size:13px; }
    .error-message {
        background:#fed7d7; color:#742a2a;
        padding:12px; border-radius:6px;
        margin-bottom:20px; border-left:4px solid #fc8181; font-size:13px;
    }
    .mandatory-note {
        background:#fff5e6; color:#7c2d12;
        padding:10px; border-radius:6px;
        margin-bottom:20px; font-size:12px;
        border-left:4px solid #fed7aa;
    }
    .form-actions {
        display:flex; gap:12px; justify-content:center;
        margin-top:30px; padding-top:25px;
        border-top:2px solid #e2e8f0;
    }
    .btn {
        padding:12px 32px; border:none; border-radius:6px;
        font-size:14px; font-weight:600; cursor:pointer;
        transition:all .3s; text-decoration:none;
        display:inline-block; text-align:center;
    }
    .btn-primary {
        background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
        color:#fff;
    }
    .btn-primary:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(102,126,234,.4); }
    .btn-secondary { background:#e2e8f0; color:#4a5568; }
    .btn-secondary:hover { background:#cbd5e0; }
    @media(max-width:768px){
        .reg-card { padding:20px; }
        .form-row { grid-template-columns:1fr; }
        .date-row { flex-direction:column; }
    }
    </style>
</head>
<body>
    <?php include 'adminnavi.php'; ?>

    <div class="container" style="margin-top:24px;">
        <div class="header-banner">
            <h1>👥 Faculty Registration</h1>
            <p>Add a new faculty member to the Leave Management System</p>
        </div>

        <div class="reg-card">
            <div class="form-title">📝 New Faculty Details</div>
            <div class="form-subtitle">Fill in all required fields to register a new faculty member</div>

            <?php if(isset($_GET['err'])): ?>
            <div class="error-message">
                ⚠️ <strong>Error:</strong> <?php echo htmlspecialchars($_GET['err']); ?>
            </div>
            <?php endif; ?>

            <div class="mandatory-note">
                ℹ️ Fields marked with <span class="required">*</span> are mandatory
            </div>

            <form action="confirm.php" method="post">

                <!-- Personal Information -->
                <div class="form-section">
                    <div class="section-title">👤 Personal Information</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Faculty Name <span class="required">*</span></label>
                            <input type="text" name="empname" placeholder="Full name" required>
                        </div>
                        <div class="form-group">
                            <label>Username <span class="required">*</span></label>
                            <input type="text" name="uname" placeholder="Unique username for login" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email Address <span class="required">*</span></label>
                            <input type="email" name="mailid" placeholder="faculty@university.com" required>
                        </div>
                        <div class="form-group">
                            <label>Designation <span class="required">*</span></label>
                            <input type="text" name="designation" placeholder="e.g. Assistant Professor" required>
                        </div>
                        <div class="form-group">
                            <label>Gender <span class="required">*</span></label>
                            <select name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Date Information -->
                <div class="form-section">
                    <div class="section-title">📅 Date Information</div>
                    <div class="form-group" style="margin-bottom:20px;">
                        <label>Date of Joining (DD / MM / YYYY) <span class="required">*</span></label>
                        <div class="date-row">
                            <div class="form-group">
                                <input type="number" name="date-join" min="1" max="31" placeholder="DD" required>
                            </div>
                            <div class="form-group">
                                <input type="number" name="month-join" min="1" max="12" placeholder="MM" required>
                            </div>
                            <div class="form-group">
                                <input type="number" name="year-join" min="1985" max="<?php echo date('Y'); ?>" placeholder="YYYY" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Date of Birth (DD / MM / YYYY) <span class="required">*</span></label>
                        <div class="date-row">
                            <div class="form-group">
                                <input type="number" name="date-birth" min="1" max="31" placeholder="DD" required>
                            </div>
                            <div class="form-group">
                                <input type="number" name="month-birth" min="1" max="12" placeholder="MM" required>
                            </div>
                            <div class="form-group">
                                <input type="number" name="year-birth" min="1950" max="<?php echo date('Y')-18; ?>" placeholder="YYYY" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employment Information -->
                <div class="form-section">
                    <div class="section-title">💼 Employment Information</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Department <span class="required">*</span></label>
                            <select name="dept" required>
                                <option value="">Select Department</option>
                                <?php foreach($depts as $d): ?>
                                <option value="<?php echo htmlspecialchars($d); ?>"><?php echo htmlspecialchars($d); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Employment Type <span class="required">*</span></label>
                            <select name="factype" required>
                                <option value="">Select type</option>
                                <option>Permanent</option>
                                <option>Ad-hoc</option>
                                <option>Fix</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Fee Structure <span class="required">*</span></label>
                            <select name="facfee" required>
                                <option value="">Select fee</option>
                                <option>Grant In Aid</option>
                                <option>Self Finance</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">✅ Register Faculty</button>
                    <a href="../home.php" class="btn btn-secondary">← Back</a>
                </div>

            </form>
        </div>
    </div>
</body>
</html>