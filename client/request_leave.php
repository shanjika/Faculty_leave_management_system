<?php
session_start();
include 'connect.php';

if(!isset($_SESSION['user'])){
    header('location:index.php');
    exit;
}

$user = $_SESSION['user'];
$sql  = "SELECT * FROM employees WHERE UserName='".$conn->real_escape_string($user)."'";
$res  = $conn->query($sql);
$emp  = ($res && $res->num_rows) ? $res->fetch_assoc() : null;

$gender = '';
if($emp){
    $gender = strtolower(trim($emp['Gender'] ?? ''));
}
if(!in_array($gender, ['male','female','other'])) $gender = 'male';
?>
<?php include 'clientnavi.php'; ?>
</div>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Apply Leave – Faculty Portal</title>
<link rel="stylesheet" href="style.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; background:#eef2f7; min-height:100vh; }

.page-wrap { max-width: 600px; margin: 0 auto; padding: 40px 20px 60px; }

.page-title { font-size:1.9rem; font-weight:800; color:#1e3a8a; text-align:center; margin-bottom:6px; }
.page-sub   { text-align:center; color:#64748b; font-size:.93rem; margin-bottom:32px; }

.dropdown-wrap { position: relative; width: 100%; }

.search-input-box {
    width: 100%;
    padding: 15px 20px 15px 50px;
    border: 2px solid #dde3f0;
    border-radius: 14px;
    font-size: 1.05rem;
    background: #fff;
    outline: none;
    cursor: pointer;
    transition: border .2s, box-shadow .2s;
    color: #1e293b;
}
.search-input-box:focus,
.search-input-box.open {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102,126,234,.15);
    border-radius: 14px 14px 0 0;
}
.search-input-box::placeholder { color:#94a3b8; }

.search-icon-left {
    position: absolute; left: 16px; top: 50%;
    transform: translateY(-50%);
    font-size: 1.2rem; pointer-events: none; z-index: 2;
}
.arrow-icon {
    position: absolute; right: 16px; top: 50%;
    transform: translateY(-50%);
    font-size: 1rem; color: #94a3b8;
    pointer-events: none;
    transition: transform .2s; z-index: 2;
}
.arrow-icon.rotated { transform: translateY(-50%) rotate(180deg); }

.dropdown-list {
    display: none;
    position: absolute; top: 100%; left: 0; right: 0;
    background: #fff;
    border: 2px solid #667eea;
    border-top: none;
    border-radius: 0 0 14px 14px;
    box-shadow: 0 12px 32px rgba(102,126,234,.18);
    z-index: 100; overflow: hidden;
}
.dropdown-list.show { display: block; }

.drop-item {
    display: flex; align-items: center; gap: 14px;
    padding: 15px 20px; cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
    transition: background .15s;
}
.drop-item:last-child { border-bottom: none; }
.drop-item:hover { background: #f5f3ff; }

.drop-icon { font-size: 1.5rem; flex-shrink: 0; }
.drop-info  { text-align: left; flex: 1; }
.drop-name  { font-weight: 700; font-size: .97rem; color: #1e3a8a; }
.drop-desc  { font-size: .78rem; color: #64748b; margin-top: 2px; }

.drop-badge {
    font-size: .7rem; font-weight: 700;
    padding: 3px 10px; border-radius: 99px;
    white-space: nowrap; flex-shrink: 0;
}
.b-std { background:#e0e7ff; color:#4338ca; }
.b-od  { background:#d1fae5; color:#065f46; }
.b-lop { background:#fee2e2; color:#991b1b; }
.b-sp  { background:#fce7f3; color:#9d174d; }

.no-results {
    padding: 20px; text-align: center;
    color: #94a3b8; font-size: .95rem;
}

.error-box {
    background:#fee2e2; color:#991b1b;
    padding:12px 16px; border-radius:10px;
    margin-bottom:20px; border:1px solid #fca5a5;
    font-weight:500;
}

.gender-debug {
    background:#f0fdf4; border:1px solid #bbf7d0;
    border-radius:8px; padding:8px 14px;
    font-size:.8rem; color:#166534;
    margin-bottom:16px; text-align:center;
}
</style>
</head>
<body>
<div class="page-wrap">

    <div class="page-title">📋 Apply Leave</div>
    <div class="page-sub">Click the search bar and select the leave type you want to apply</div>

    <?php if(isset($_GET['err'])): ?>
    <div class="error-box">⚠️ <?php echo htmlspecialchars($_GET['err']); ?></div>
    <?php endif; ?>

    <div class="dropdown-wrap">
        <span class="search-icon-left">🔍</span>
        <input type="text" id="searchInput" class="search-input-box"
               placeholder="Search or select leave type..."
               autocomplete="off" readonly onclick="toggleDropdown()">
        <span class="arrow-icon" id="arrowIcon">▼</span>

        <div class="dropdown-list" id="dropdownList">

            <!-- 1. Medical Leave -->
            <div class="drop-item leave-option"
                 data-name="medical leave sick illness health"
                 onclick="selectLeave('Medical Leave')">
                <span class="drop-icon">🏥</span>
                <div class="drop-info">
                    <div class="drop-name">Medical Leave</div>
                    <div class="drop-desc">Leave due to illness or medical condition</div>
                </div>
                <span class="drop-badge b-std">Standard</span>
            </div>

            <!-- 2. Casual Leave -->
            <div class="drop-item leave-option"
                 data-name="casual leave personal"
                 onclick="selectLeave('Casual Leave')">
                <span class="drop-icon">🌴</span>
                <div class="drop-info">
                    <div class="drop-name">Casual Leave</div>
                    <div class="drop-desc">Short leave for personal or urgent work</div>
                </div>
                <span class="drop-badge b-std">Standard</span>
            </div>

            <!-- 3. Loss of Pay -->
            <div class="drop-item leave-option"
                 data-name="loss of pay lop unpaid"
                 onclick="selectLeave('Loss of Pay')">
                <span class="drop-icon">💸</span>
                <div class="drop-info">
                    <div class="drop-name">Loss of Pay</div>
                    <div class="drop-desc">Leave without pay when balance is exhausted</div>
                </div>
                <span class="drop-badge b-lop">Unpaid</span>
            </div>

            <!-- 4. On Duty -->
            <div class="drop-item leave-option"
                 data-name="on duty od official activity"
                 onclick="selectLeave('On Duty')">
                <span class="drop-icon">🏛️</span>
                <div class="drop-info">
                    <div class="drop-name">On Duty</div>
                    <div class="drop-desc">Official work outside campus – Activity ID required</div>
                </div>
                <span class="drop-badge b-od">Activity ID</span>
            </div>

            <!-- 5. Special Leave -->
            <div class="drop-item leave-option"
                 data-name="special leave maternity casual"
                 onclick="selectLeave('Special Leave')">
                <span class="drop-icon">⭐</span>
                <div class="drop-info">
                    <div class="drop-name">Special Leave</div>
                    <?php if($gender === 'female'): ?>
                    <div class="drop-desc">Maternity Leave / Special Casual Leave</div>
                    <?php else: ?>
                    <div class="drop-desc">Special Casual Leave</div>
                    <?php endif; ?>
                </div>
                <span class="drop-badge b-sp">Special</span>
            </div>

            <div class="no-results" id="noResults" style="display:none;">
                No leave type found
            </div>

        </div>
    </div>

</div>

<form id="leaveForm" action="leaverequest.php" method="post" style="display:none;">
    <input type="hidden" name="type" id="selectedType">
</form>

<script>
var isOpen = false;

function toggleDropdown() {
    isOpen = !isOpen;
    var list  = document.getElementById('dropdownList');
    var input = document.getElementById('searchInput');
    var arrow = document.getElementById('arrowIcon');
    if(isOpen){
        list.classList.add('show');
        input.classList.add('open');
        input.removeAttribute('readonly');
        input.focus();
        arrow.classList.add('rotated');
        input.oninput = filterItems;
    } else {
        closeDropdown();
    }
}

function closeDropdown() {
    isOpen = false;
    document.getElementById('dropdownList').classList.remove('show');
    var input = document.getElementById('searchInput');
    input.classList.remove('open');
    input.setAttribute('readonly','true');
    document.getElementById('arrowIcon').classList.remove('rotated');
    input.oninput = null;
}

function filterItems() {
    var q     = document.getElementById('searchInput').value.toLowerCase();
    var items = document.querySelectorAll('.leave-option');
    var any   = false;
    items.forEach(function(item){
        var match = item.getAttribute('data-name').toLowerCase().includes(q);
        item.style.display = match ? '' : 'none';
        if(match) any = true;
    });
    document.getElementById('noResults').style.display = any ? 'none' : '';
}

function selectLeave(type) {
    document.getElementById('selectedType').value = type;
    document.getElementById('leaveForm').submit();
}

document.addEventListener('click', function(e){
    var wrap = document.querySelector('.dropdown-wrap');
    if(isOpen && !wrap.contains(e.target)) closeDropdown();
});
</script>
</body>
</html>