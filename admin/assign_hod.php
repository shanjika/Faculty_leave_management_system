<?php
session_start();
include 'connect.php';

if(!isset($_SESSION['adminuser'])){
    header('location:index.php?err='.urlencode('Please Login First!'));
    exit;
}
if(($_SESSION['role'] ?? 'HOD') !== 'HR'){
    header('location:home.php?msg='.urlencode('Only HR can assign faculty to HOD.'));
    exit;
}

$successMsg = '';
$errorMsg   = '';

// Handle save
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assignments'])){
    $assignments = $_POST['assignments'];
    $updated = 0;
    foreach($assignments as $empId => $hodUsername){
        $empId      = (int)$empId;
        $hodUsername = $conn->real_escape_string(trim($hodUsername));
        if($empId > 0){
            $conn->query("UPDATE employees SET HodUsername='$hodUsername' WHERE id='$empId'");
            $updated++;
        }
    }
    $successMsg = "✅ Assignments saved successfully! $updated faculty updated.";
}

// Get all HODs
$hodRes = $conn->query("SELECT username, Dept FROM admins WHERE Role='HOD' ORDER BY Dept, username");
$hods   = [];
while($h = $hodRes->fetch_assoc()) $hods[] = $h;

// Get faculty — UNASSIGNED FIRST, then assigned, both sorted by dept+name
$facRes = $conn->query("SELECT id, EmpName, Dept, Designation, HodUsername
    FROM employees
    ORDER BY
        CASE WHEN HodUsername IS NULL OR HodUsername='' THEN 0 ELSE 1 END ASC,
        Dept ASC,
        EmpName ASC");

// Count stats
$totalRes      = $conn->query("SELECT COUNT(*) as cnt FROM employees");
$total         = $totalRes ? (int)$totalRes->fetch_assoc()['cnt'] : 0;
$unassignedRes = $conn->query("SELECT COUNT(*) as cnt FROM employees WHERE HodUsername IS NULL OR HodUsername=''");
$unassigned    = $unassignedRes ? (int)$unassignedRes->fetch_assoc()['cnt'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Assign Faculty to HOD</title>
<link rel="stylesheet" href="style.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',sans-serif; background:#f0f4f8; min-height:100vh; }
.page-wrap { max-width:1100px; margin:0 auto; padding:30px 20px; }

.banner {
    background:linear-gradient(135deg,#065f46 0%,#10b981 100%);
    color:#fff; padding:26px 30px; border-radius:14px;
    margin-bottom:24px;
    box-shadow:0 8px 24px rgba(6,95,70,.2);
    display:flex; justify-content:space-between;
    align-items:center; flex-wrap:wrap; gap:12px;
}
.banner h1 { font-size:1.5rem; margin-bottom:4px; }
.banner p  { font-size:.88rem; opacity:.85; }

/* Summary pills */
.summary-row { display:flex; gap:14px; flex-wrap:wrap; margin-bottom:22px; }
.summary-pill {
    background:#fff; border-radius:10px;
    padding:14px 20px; text-align:center;
    box-shadow:0 3px 12px rgba(0,0,0,.07);
    min-width:130px; flex:1;
    border-top:3px solid #10b981;
}
.summary-pill.warn { border-top-color:#f59e0b; }
.summary-pill.ok   { border-top-color:#10b981; }
.pill-val   { font-size:1.8rem; font-weight:800; color:#1e293b; }
.pill-label { font-size:.75rem; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-top:3px; }

/* Tabs */
.tabs { display:flex; gap:0; margin-bottom:22px; border-bottom:3px solid #e2e8f0; }
.tab-btn {
    padding:12px 28px; background:none; border:none;
    font-size:.95rem; font-weight:600; color:#64748b;
    cursor:pointer; border-bottom:3px solid transparent;
    margin-bottom:-3px; transition:all .2s;
}
.tab-btn.active { color:#065f46; border-bottom-color:#10b981; }
.tab-btn:hover  { color:#065f46; background:#f0fdf4; }
.tab-badge {
    display:inline-block; background:#f59e0b; color:#fff;
    font-size:.7rem; font-weight:800; padding:1px 7px;
    border-radius:99px; margin-left:6px; vertical-align:middle;
}
.tab-content { display:none; }
.tab-content.active { display:block; }

/* Alert boxes */
.success-box {
    background:#d1fae5; border:1px solid #6ee7b7;
    border-radius:10px; padding:14px 18px;
    color:#065f46; font-weight:600; margin-bottom:20px;
}
.info-box {
    background:#fef3c7; border:1px solid #fcd34d;
    border-radius:10px; padding:12px 16px;
    color:#92400e; font-size:.88rem; margin-bottom:18px;
}

/* Table card */
.table-card {
    background:#fff; border-radius:14px;
    box-shadow:0 4px 16px rgba(0,0,0,.07);
    padding:22px 24px; overflow-x:auto;
    margin-bottom:20px;
}
table { width:100%; border-collapse:collapse; }
th {
    padding:12px; text-align:left;
    font-size:.78rem; font-weight:700; color:#64748b;
    text-transform:uppercase; background:#f8fafc;
    border-bottom:2px solid #e2e8f0; white-space:nowrap;
}
td {
    padding:14px 12px; font-size:.88rem;
    border-bottom:1px solid #f1f5f9; color:#1e293b;
}
tr:last-child td { border-bottom:none; }
tr:hover td { background:#f8faff; }
tr.unassigned-row td { background:#fffbeb; }
tr.unassigned-row:hover td { background:#fef9e7; }

.badge { display:inline-block; padding:3px 10px; border-radius:99px; font-size:.74rem; font-weight:700; }
.badge-unassigned { background:#fee2e2; color:#991b1b; }
.badge-assigned   { background:#d1fae5; color:#065f46; }
.badge-dept       { background:#e0e7ff; color:#4338ca; }

/* HOD select dropdown */
.hod-select {
    padding:8px 12px; border:2px solid #e2e8f0;
    border-radius:8px; font-size:.88rem;
    background:#fafafa; color:#1e293b;
    min-width:200px; cursor:pointer;
    transition:border .2s; font-family:inherit;
}
.hod-select:focus { border-color:#10b981; outline:none; background:#fff; }
.hod-select.changed { border-color:#f59e0b; background:#fffbeb; }

/* Avatar */
.avatar {
    display:inline-flex; align-items:center;
    justify-content:center; width:32px; height:32px;
    border-radius:50%; background:linear-gradient(135deg,#667eea,#764ba2);
    color:#fff; font-weight:800; font-size:.82rem; flex-shrink:0;
}
.emp-cell { display:flex; align-items:center; gap:10px; }
.emp-name  { font-weight:700; color:#1e293b; }
.emp-desig { font-size:.75rem; color:#94a3b8; }

/* Section divider */
.section-divider {
    display:flex; align-items:center; gap:12px;
    margin:6px 0 6px; font-size:.8rem;
    font-weight:700; color:#94a3b8; text-transform:uppercase;
}
.section-divider::before,
.section-divider::after {
    content:''; flex:1; height:1px; background:#e2e8f0;
}

/* Save button */
.save-bar {
    position:sticky; bottom:20px;
    background:#fff; border-radius:14px;
    box-shadow:0 8px 32px rgba(0,0,0,.15);
    padding:16px 24px;
    display:flex; justify-content:space-between;
    align-items:center; gap:16px; flex-wrap:wrap;
    border:2px solid #10b981;
}
.save-info { font-size:.88rem; color:#64748b; }
.save-info strong { color:#065f46; }
.btn-save {
    padding:13px 36px;
    background:linear-gradient(135deg,#065f46,#10b981);
    color:#fff; border:none; border-radius:10px;
    font-weight:800; font-size:1rem; cursor:pointer;
    transition:opacity .2s,transform .2s;
}
.btn-save:hover { opacity:.9; transform:translateY(-1px); }

/* HOD overview table */
.hod-card {
    background:#fff; border-radius:14px;
    box-shadow:0 4px 16px rgba(0,0,0,.07);
    padding:22px 24px; margin-bottom:20px;
}
.hod-row {
    display:flex; justify-content:space-between;
    align-items:center; padding:14px 0;
    border-bottom:1px solid #f1f5f9; gap:12px; flex-wrap:wrap;
}
.hod-row:last-child { border-bottom:none; }
.hod-name { font-weight:700; color:#1e293b; font-size:.95rem; }
.hod-dept { font-size:.8rem; color:#94a3b8; margin-top:2px; }
.hod-count {
    background:#e0fdf4; color:#065f46;
    padding:4px 14px; border-radius:99px;
    font-weight:800; font-size:.85rem;
}

.empty-state { text-align:center; padding:40px; color:#94a3b8; }

@media(max-width:600px){
    .save-bar { flex-direction:column; }
    .banner { flex-direction:column; align-items:flex-start; }
}
</style>
</head>
<body>
<?php include 'adminnavi.php'; ?>

<div class="page-wrap">

    <!-- Banner -->
    <div class="banner">
        <div>
            <h1>🔗 Assign Faculty to HOD</h1>
            <p>Manage which HOD is responsible for each faculty member</p>
        </div>
        <a href="faculty_list.php" style="background:rgba(255,255,255,.2);color:#fff;padding:9px 20px;border-radius:8px;font-weight:700;font-size:.88rem;text-decoration:none;">
            👥 View Faculty List
        </a>
    </div>

    <!-- Success message -->
    <?php if($successMsg): ?>
    <div class="success-box"><?php echo htmlspecialchars($successMsg); ?></div>
    <?php endif; ?>

    <!-- Summary pills -->
    <div class="summary-row">
        <div class="summary-pill ok">
            <div class="pill-val"><?php echo $total; ?></div>
            <div class="pill-label">Total Faculty</div>
        </div>
        <div class="summary-pill ok">
            <div class="pill-val"><?php echo $total - $unassigned; ?></div>
            <div class="pill-label">Assigned</div>
        </div>
        <div class="summary-pill warn">
            <div class="pill-val"><?php echo $unassigned; ?></div>
            <div class="pill-label">⚠️ Unassigned</div>
        </div>
        <div class="summary-pill">
            <div class="pill-val"><?php echo count($hods); ?></div>
            <div class="pill-label">HODs Available</div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <button class="tab-btn active" onclick="switchTab('assign',this)">
            📋 Assign Faculty
            <?php if($unassigned > 0): ?>
            <span class="tab-badge"><?php echo $unassigned; ?> unassigned</span>
            <?php endif; ?>
        </button>
        <button class="tab-btn" onclick="switchTab('overview',this)">
            📊 HOD Overview
        </button>
    </div>

    <!-- TAB: Assign Faculty -->
    <div id="tab-assign" class="tab-content active">

        <?php if($unassigned > 0): ?>
        <div class="info-box">
            ⚠️ <strong><?php echo $unassigned; ?> faculty</strong> are not yet assigned to any HOD — they appear at the top highlighted in yellow.
        </div>
        <?php endif; ?>

        <?php if(empty($hods)): ?>
        <div class="info-box">
            ⚠️ No HODs found in the system. Please add HOD accounts first before assigning faculty.
        </div>
        <?php else: ?>

        <form method="POST" action="" id="assignForm">
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Faculty</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Current HOD</th>
                        <th>Assign HOD</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $i = 1;
                $prevSection = null;
                if($facRes && $facRes->num_rows > 0):
                while($fac = $facRes->fetch_assoc()):
                    $isUnassigned = empty($fac['HodUsername']);
                    $section = $isUnassigned ? 'unassigned' : 'assigned';

                    // Section divider between unassigned and assigned
                    if($prevSection !== $section):
                        $prevSection = $section;
                        $label = $isUnassigned ? '⚠️ Unassigned Faculty (needs HOD)' : '✅ Already Assigned Faculty';
                        echo "<tr><td colspan='6'><div class='section-divider'>$label</div></td></tr>";
                    endif;
                ?>
                <tr class="<?php echo $isUnassigned ? 'unassigned-row' : ''; ?>">
                    <td style="color:#94a3b8;"><?php echo $i++; ?></td>
                    <td>
                        <div class="emp-cell">
                            <div class="avatar"><?php echo strtoupper(substr($fac['EmpName'],0,1)); ?></div>
                            <div>
                                <div class="emp-name"><?php echo htmlspecialchars($fac['EmpName']); ?></div>
                                <div class="emp-desig"><?php echo htmlspecialchars($fac['Designation']); ?></div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge badge-dept"><?php echo htmlspecialchars($fac['Dept']); ?></span></td>
                    <td style="color:#64748b;font-size:.83rem;"><?php echo htmlspecialchars($fac['Designation']); ?></td>
                    <td>
                        <?php if($isUnassigned): ?>
                        <span class="badge badge-unassigned">⚠️ Not Assigned</span>
                        <?php else: ?>
                        <span class="badge badge-assigned">✅ <?php echo htmlspecialchars($fac['HodUsername']); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <select
                            name="assignments[<?php echo $fac['id']; ?>]"
                            class="hod-select"
                            data-original="<?php echo htmlspecialchars($fac['HodUsername'] ?? ''); ?>"
                            onchange="markChanged(this)">
                            <option value="">— Select HOD —</option>
                            <?php foreach($hods as $h): ?>
                            <option value="<?php echo htmlspecialchars($h['username']); ?>"
                                <?php echo ($fac['HodUsername'] === $h['username']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($h['username']); ?>
                                (<?php echo htmlspecialchars($h['Dept']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <?php endwhile;
                else: ?>
                <tr><td colspan="6"><div class="empty-state">No faculty found.</div></td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

       <div style="text-align:right; margin-top:16px;">
    <button type="submit" class="btn-save">💾 Save All Assignments</button>
</div>
        </form>
        <?php endif; ?>
    </div>

    <!-- TAB: HOD Overview -->
    <div id="tab-overview" class="tab-content">
        <div class="hod-card">
            <h3 style="font-size:1rem;font-weight:700;color:#1e293b;margin-bottom:16px;">HOD Faculty Load Overview</h3>
            <?php
            if(!empty($hods)):
            foreach($hods as $h):
                $uname   = $conn->real_escape_string($h['username']);
                $countR  = $conn->query("SELECT COUNT(*) as cnt FROM employees WHERE HodUsername='$uname'");
                $cnt     = $countR ? (int)$countR->fetch_assoc()['cnt'] : 0;

                // Get faculty names under this HOD
                $namesR  = $conn->query("SELECT EmpName, Dept FROM employees WHERE HodUsername='$uname' ORDER BY EmpName LIMIT 5");
            ?>
            <div class="hod-row">
                <div>
                    <div class="hod-name">👤 <?php echo htmlspecialchars($h['username']); ?></div>
                    <div class="hod-dept"><?php echo htmlspecialchars($h['Dept']); ?></div>
                    <?php if($namesR && $namesR->num_rows > 0):
                        $names = [];
                        while($n = $namesR->fetch_assoc()) $names[] = htmlspecialchars($n['EmpName']);
                        echo "<div style='font-size:.75rem;color:#94a3b8;margin-top:4px;'>".implode(', ', $names);
                        if($cnt > 5) echo " <em>+ ".($cnt-5)." more</em>";
                        echo "</div>";
                    endif; ?>
                </div>
                <span class="hod-count"><?php echo $cnt; ?> faculty</span>
            </div>
            <?php endforeach;
            else: ?>
            <div class="empty-state">No HODs found in the system.</div>
            <?php endif; ?>

            <?php if($unassigned > 0): ?>
            <div class="hod-row" style="background:#fffbeb;border-radius:8px;padding:14px;margin-top:8px;">
                <div>
                    <div class="hod-name" style="color:#92400e;">⚠️ Not Assigned</div>
                    <div class="hod-dept">Faculty with no HOD assigned</div>
                </div>
                <span class="hod-count" style="background:#fef3c7;color:#92400e;"><?php echo $unassigned; ?> faculty</span>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
function switchTab(name, btn){
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-'+name).classList.add('active');
    btn.classList.add('active');
}

function markChanged(sel){
    if(sel.value !== sel.dataset.original){
        sel.classList.add('changed');
    } else {
        sel.classList.remove('changed');
    }
}
</script>
</body>
</html>