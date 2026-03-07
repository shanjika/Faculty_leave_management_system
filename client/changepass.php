<?php
session_start();
include 'connect.php';
if(!isset($_SESSION['user'])){
    header('location:index.php?err='.urlencode('Please Login First!'));
    exit;
}
$user = $_SESSION['user'];

// Get employee name
$res = $conn->query("SELECT EmpName FROM employees WHERE UserName='".$conn->real_escape_string($user)."'");
$emp = ($res && $res->num_rows) ? $res->fetch_assoc() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change Password</title>
<link rel="stylesheet" href="style.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',sans-serif; background:#f0f4f8; min-height:100vh; }
.wrap { max-width:480px; margin:50px auto; padding:20px; }
.card {
    background:#fff; border-radius:18px;
    box-shadow:0 8px 32px rgba(0,0,0,.10);
    padding:36px 36px 30px;
}
.card-header { text-align:center; margin-bottom:28px; }
.card-header .icon { font-size:2.8rem; }
.card-header h2 { color:#1e3a8a; font-size:1.45rem; margin:10px 0 4px; }
.card-header p  { color:#64748b; font-size:.88rem; }

.form-group { margin-bottom:20px; }
.form-group label {
    display:block; font-weight:700; font-size:.82rem;
    color:#374151; margin-bottom:7px;
    text-transform:uppercase; letter-spacing:.5px;
}
.input-wrap { position:relative; }
.input-wrap input {
    width:100%; padding:12px 44px 12px 14px;
    border:2px solid #e2e8f0; border-radius:10px;
    font-size:.97rem; font-family:inherit;
    background:#fafafa; color:#1e293b;
    transition:border .2s,box-shadow .2s;
}
.input-wrap input:focus {
    border-color:#667eea;
    box-shadow:0 0 0 3px rgba(102,126,234,.12);
    outline:none; background:#fff;
}
.toggle-eye {
    position:absolute; right:14px; top:50%;
    transform:translateY(-50%);
    cursor:pointer; font-size:1.1rem;
    user-select:none; color:#94a3b8;
}
.toggle-eye:hover { color:#667eea; }

.strength-bar { height:5px; border-radius:3px; margin-top:7px; background:#e2e8f0; overflow:hidden; }
.strength-fill { height:100%; border-radius:3px; transition:width .3s,background .3s; width:0%; }
.strength-label { font-size:.75rem; color:#94a3b8; margin-top:4px; }

.divider { border:none; border-top:2px solid #f1f5f9; margin:22px 0; }

.err-box {
    background:#fee2e2; border:1px solid #fca5a5;
    border-radius:10px; padding:12px 16px;
    color:#991b1b; font-size:.88rem;
    font-weight:600; margin-bottom:20px;
}
.suc-box {
    background:#d1fae5; border:1px solid #6ee7b7;
    border-radius:10px; padding:12px 16px;
    color:#065f46; font-size:.88rem;
    font-weight:600; margin-bottom:20px;
}

.submit-btn {
    width:100%; padding:13px;
    background:linear-gradient(135deg,#667eea,#764ba2);
    color:#fff; border:none; border-radius:12px;
    font-size:1rem; font-weight:800;
    cursor:pointer; transition:opacity .2s,transform .2s;
}
.submit-btn:hover { opacity:.9; transform:translateY(-1px); }

.tips {
    background:#f8faff; border:1px solid #e0e7ff;
    border-radius:10px; padding:14px 16px;
    margin-bottom:22px; font-size:.83rem; color:#4338ca;
}
.tips ul { margin:6px 0 0 16px; }
.tips li { margin-bottom:3px; }

.back-link {
    display:block; text-align:center;
    margin-top:16px; color:#667eea;
    font-weight:600; text-decoration:none; font-size:.9rem;
}
.back-link:hover { text-decoration:underline; }
</style>
</head>
<body>
<?php include 'clientnavi.php'; ?>
</div>

<div class="wrap">
<div class="card">

    <div class="card-header">
        <div class="icon">🔐</div>
        <h2>Change Password</h2>
        <p>Hello <strong><?php echo htmlspecialchars($emp['EmpName'] ?? $user); ?></strong> — update your login password below</p>
    </div>

    <?php if(isset($_GET['err'])): ?>
    <div class="err-box">⚠️ <?php echo htmlspecialchars($_GET['err']); ?></div>
    <?php endif; ?>

    <?php if(isset($_GET['msg'])): ?>
    <div class="suc-box">✅ <?php echo htmlspecialchars($_GET['msg']); ?></div>
    <?php endif; ?>

    <div class="tips">
        <strong>💡 Password Tips:</strong>
        <ul>
            <li>Minimum 6 characters</li>
            <li>Mix letters, numbers and symbols</li>
            <li>Do not share your password with anyone</li>
        </ul>
    </div>

    <form action="password.php" method="post" onsubmit="return validateForm()">

        <div class="form-group">
            <label>Current Password</label>
            <div class="input-wrap">
                <input type="password" name="oldpass" id="oldpass" placeholder="Enter your current password" required>
                <span class="toggle-eye" onclick="togglePass('oldpass',this)">👁</span>
            </div>
        </div>

        <hr class="divider">

        <div class="form-group">
            <label>New Password</label>
            <div class="input-wrap">
                <input type="password" name="newpass" id="newpass" placeholder="Enter new password" required oninput="checkStrength(this.value)">
                <span class="toggle-eye" onclick="togglePass('newpass',this)">👁</span>
            </div>
            <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
            <div class="strength-label" id="strengthLabel">Enter a password</div>
        </div>

        <div class="form-group">
            <label>Confirm New Password</label>
            <div class="input-wrap">
                <input type="password" name="cnfnewpass" id="cnfnewpass" placeholder="Re-enter new password" required>
                <span class="toggle-eye" onclick="togglePass('cnfnewpass',this)">👁</span>
            </div>
            <div id="matchMsg" style="font-size:.78rem;margin-top:5px;"></div>
        </div>

        <button type="submit" class="submit-btn">🔐 Change Password</button>
    </form>

    <a href="profile.php" class="back-link">← Back to Profile</a>
</div>
</div>

<script>
function togglePass(id, el){
    var inp = document.getElementById(id);
    if(inp.type === 'password'){ inp.type = 'text'; el.textContent = '🙈'; }
    else { inp.type = 'password'; el.textContent = '👁'; }
}

function checkStrength(val){
    var fill  = document.getElementById('strengthFill');
    var label = document.getElementById('strengthLabel');
    var score = 0;
    if(val.length >= 6)  score++;
    if(val.length >= 10) score++;
    if(/[A-Z]/.test(val)) score++;
    if(/[0-9]/.test(val)) score++;
    if(/[^A-Za-z0-9]/.test(val)) score++;

    var colors = ['#e53e3e','#ed8936','#ecc94b','#38a169','#667eea'];
    var labels = ['Very Weak','Weak','Fair','Strong','Very Strong'];
    var widths = ['20%','40%','60%','80%','100%'];

    if(val.length === 0){
        fill.style.width = '0%';
        label.textContent = 'Enter a password';
        label.style.color = '#94a3b8';
        return;
    }
    var idx = Math.min(score-1, 4);
    if(idx < 0) idx = 0;
    fill.style.width     = widths[idx];
    fill.style.background= colors[idx];
    label.textContent    = labels[idx];
    label.style.color    = colors[idx];
}

document.getElementById('cnfnewpass').addEventListener('input', function(){
    var msg  = document.getElementById('matchMsg');
    var np   = document.getElementById('newpass').value;
    if(this.value === ''){
        msg.textContent = '';
    } else if(this.value === np){
        msg.textContent = '✅ Passwords match';
        msg.style.color = '#38a169';
    } else {
        msg.textContent = '❌ Passwords do not match';
        msg.style.color = '#e53e3e';
    }
});

function validateForm(){
    var np  = document.getElementById('newpass').value;
    var cnf = document.getElementById('cnfnewpass').value;
    if(np.length < 6){
        alert('New password must be at least 6 characters!');
        return false;
    }
    if(np !== cnf){
        alert('Passwords do not match!');
        return false;
    }
    return true;
}
</script>
</body>
</html>