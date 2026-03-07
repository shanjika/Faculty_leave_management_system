<?php
session_start();
include 'connect.php';

if(!isset($_SESSION['adminuser'])){
    header('location:index.php?err='.urlencode('Please Login First!'));
    exit;
}
if(($_SESSION['role'] ?? 'HOD') !== 'HR'){
    header('location:home.php?msg='.urlencode('Only HR can access faculty list.'));
    exit;
}

// Filters
$filterDept = $conn->real_escape_string($_GET['dept']   ?? 'all');
$filterHod  = $conn->real_escape_string($_GET['hod']    ?? 'all');
$filterAsgn = $conn->real_escape_string($_GET['assign'] ?? 'all');
$search     = $conn->real_escape_string($_GET['search'] ?? '');

// Build WHERE
$where = "1=1";
if($filterDept !== 'all')  $where .= " AND e.Dept='$filterDept'";
if($filterHod  !== 'all')  $where .= " AND e.HodUsername='$filterHod'";
if($filterAsgn === 'assigned')   $where .= " AND e.HodUsername IS NOT NULL AND e.HodUsername != ''";
if($filterAsgn === 'unassigned') $where .= " AND (e.HodUsername IS NULL OR e.HodUsername='')";
if($search !== '')  $where .= " AND (e.EmpName LIKE '%$search%' OR e.UserName LIKE '%$search%' OR e.EmpEmail LIKE '%$search%')";

// Get faculty
$facRes = $conn->query("SELECT e.*, a.Dept as HodDept
    FROM employees e
    LEFT JOIN admins a ON a.username = e.HodUsername
    WHERE $where
    ORDER BY e.Dept, e.EmpName");

// Get all depts for filter
$deptRes = $conn->query("SELECT DISTINCT Dept FROM employees ORDER BY Dept");
$depts   = [];
while($d = $deptRes->fetch_assoc()) $depts[] = $d['Dept'];

// Get all HODs for filter
$hodRes = $conn->query("SELECT username, Dept FROM admins ORDER BY Dept");
$hods   = [];
while($h = $hodRes->fetch_assoc()) $hods[] = $h;

// Count totals
$totalRes      = $conn->query("SELECT COUNT(*) as cnt FROM employees");
$total         = $totalRes ? (int)$totalRes->fetch_assoc()['cnt'] : 0;
$unassignedRes = $conn->query("SELECT COUNT(*) as cnt FROM employees WHERE HodUsername IS NULL OR HodUsername=''");
$unassigned    = $unassignedRes ? (int)$unassignedRes->fetch_assoc()['cnt'] : 0;
$shown         = $facRes ? $facRes->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Faculty List – HR</title>
<link rel="stylesheet" href="style.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',sans-serif; background:#f0f4f8; min-height:100vh; }
.page-wrap { max-width:1200px; margin:0 auto; padding:30px 20px; }

.banner {
    background:linear-gradient(135deg,#065f46 0%,#10b981 100%);
    color:#fff; padding:26px 30px; border-radius:14px;
    margin-bottom:24px;
    box-shadow:0 8px 24px rgba(6,95,70,.2);
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
.pill-val   { font-size:1.8rem; font-weight:800; color:#1e293b; }
.pill-label { font-size:.75rem; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-top:3px; }

/* Filter bar */
.filter-bar {
    background:#fff; border-radius:12px;
    box-shadow:0 3px 12px rgba(0,0,0,.07);
    padding:18px 20px; margin-bottom:20px;
    display:flex; gap:14px; flex-wrap:wrap; align-items:flex-end;
}
.filter-group { display:flex; flex-direction:column; gap:5px; flex:1; min-width:150px; }
.filter-group label { font-size:.78rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.5px; }
.filter-group input,
.filter-group select {
    padding:10px 12px; border:2px solid #e2e8f0;
    border-radius:8px; font-size:.9rem;
    background:#fafafa; color:#1e293b;
    transition:border .2s; font-family:inherit;
}
.filter-group input:focus,
.filter-group select:focus { border-color:#10b981; outline:none; }
.filter-btn {
    padding:10px 22px; background:linear-gradient(135deg,#065f46,#10b981);
    color:#fff; border:none; border-radius:8px;
    font-weight:700; font-size:.9rem; cursor:pointer;
    align-self:flex-end; transition:opacity .2s;
}
.filter-btn:hover { opacity:.88; }
.reset-btn {
    padding:10px 16px; background:#f1f5f9; color:#374151;
    border:none; border-radius:8px; font-weight:700;
    font-size:.9rem; cursor:pointer; align-self:flex-end;
    text-decoration:none; display:inline-block; text-align:center;
}
.reset-btn:hover { background:#e2e8f0; }
.result-count { font-size:.82rem; color:#64748b; align-self:flex-end; padding-bottom:10px; white-space:nowrap; }

/* Table */
.table-card {
    background:#fff; border-radius:14px;
    box-shadow:0 4px 16px rgba(0,0,0,.07);
    padding:22px 24px; overflow-x:auto;
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

.badge { display:inline-block; padding:3px 10px; border-radius:99px; font-size:.74rem; font-weight:700; }
.badge-assigned   { background:#d1fae5; color:#065f46; }
.badge-unassigned { background:#fee2e2; color:#991b1b; }
.badge-dept       { background:#e0e7ff; color:#4338ca; }
.badge-type       { background:#f3f4f6; color:#374151; }

.action-link { color:#10b981; text-decoration:none; font-weight:600; font-size:.85rem; }
.action-link:hover { text-decoration:underline; }
.delete-link { color:#ef4444; text-decoration:none; font-weight:600; font-size:.85rem; }
.delete-link:hover { text-decoration:underline; }

.empty-state { text-align:center; padding:50px 20px; color:#94a3b8; }
.empty-state .icon { font-size:3rem; margin-bottom:12px; }

.active-tag {
    display:inline-block; background:#d1fae5; color:#065f46;
    padding:3px 12px; border-radius:99px;
    font-size:.78rem; font-weight:700; margin-left:6px;
}

/* Avatar initials */
.avatar {
    display:inline-flex; align-items:center;
    justify-content:center; width:34px; height:34px;
    border-radius:50%; background:linear-gradient(135deg,#667eea,#764ba2);
    color:#fff; font-weight:800; font-size:.85rem;
    flex-shrink:0;
}
.emp-cell { display:flex; align-items:center; gap:10px; }
.emp-details { display:flex; flex-direction:column; }
.emp-name  { font-weight:700; color:#1e293b; }
.emp-user  { font-size:.75rem; color:#94a3b8; }

@media(max-width:700px){
    .filter-bar { flex-direction:column; }
    .summary-row { gap:8px; }
}
</style>
</head>
<body>
<?php include 'adminnavi.php'; ?>

<div class="page-wrap">

    <!-- Banner -->
    <div class="banner">
        <h1>👥 Faculty List</h1>
        <p>View, search and manage all registered faculty members</p>
    </div>

    <!-- Summary -->
    <div class="summary-row">
        <div class="summary-pill">
            <div class="pill-val"><?php echo $total; ?></div>
            <div class="pill-label">Total Faculty</div>
        </div>
        <div class="summary-pill">
            <div class="pill-val"><?php echo $total - $unassigned; ?></div>
            <div class="pill-label">Assigned</div>
        </div>
        <div class="summary-pill warn">
            <div class="pill-val"><?php echo $unassigned; ?></div>
            <div class="pill-label">Unassigned</div>
        </div>
        <div class="summary-pill">
            <div class="pill-val"><?php echo count($depts); ?></div>
            <div class="pill-label">Departments</div>
        </div>
    </div>

    <!-- Filter bar -->
    <form method="GET" action="">
    <div class="filter-bar">
        <div class="filter-group" style="min-width:200px;">
            <label>🔍 Search</label>
            <input type="text" name="search" placeholder="Name, username or email..."
                value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
        </div>
        <div class="filter-group">
            <label>Department</label>
            <select name="dept">
                <option value="all">All Departments</option>
                <?php foreach($depts as $d): ?>
                <option value="<?php echo htmlspecialchars($d); ?>"
                    <?php echo $filterDept===$d?'selected':''; ?>>
                    <?php echo htmlspecialchars($d); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label>HOD</label>
            <select name="hod">
                <option value="all">All HODs</option>
                <?php foreach($hods as $h): ?>
                <option value="<?php echo htmlspecialchars($h['username']); ?>"
                    <?php echo $filterHod===$h['username']?'selected':''; ?>>
                    <?php echo htmlspecialchars($h['username']); ?> (<?php echo htmlspecialchars($h['Dept']); ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label>Assignment</label>
            <select name="assign">
                <option value="all"        <?php echo $filterAsgn==='all'        ?'selected':''; ?>>All Faculty</option>
                <option value="assigned"   <?php echo $filterAsgn==='assigned'   ?'selected':''; ?>>✅ Assigned</option>
                <option value="unassigned" <?php echo $filterAsgn==='unassigned' ?'selected':''; ?>>⚠️ Unassigned</option>
            </select>
        </div>
        <button type="submit" class="filter-btn">🔍 Search</button>
        <a href="faculty_list.php" class="reset-btn">↺ Reset</a>
        <span class="result-count">Showing <strong><?php echo $shown; ?></strong> of <strong><?php echo $total; ?></strong></span>
    </div>
    </form>

    <!-- Active filter tags -->
    <?php if($search !== '' || $filterDept !== 'all' || $filterHod !== 'all' || $filterAsgn !== 'all'): ?>
    <div style="margin-bottom:14px;font-size:.85rem;color:#64748b;">
        Active filters:
        <?php if($search !== ''): ?>
        <span class="active-tag">Search: "<?php echo htmlspecialchars($search); ?>"</span>
        <?php endif; ?>
        <?php if($filterDept !== 'all'): ?>
        <span class="active-tag">Dept: <?php echo htmlspecialchars($filterDept); ?></span>
        <?php endif; ?>
        <?php if($filterHod !== 'all'): ?>
        <span class="active-tag">HOD: <?php echo htmlspecialchars($filterHod); ?></span>
        <?php endif; ?>
        <?php if($filterAsgn !== 'all'): ?>
        <span class="active-tag"><?php echo $filterAsgn==='assigned'?'Assigned Only':'Unassigned Only'; ?></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="table-card">
    <?php if($facRes && $facRes->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Faculty</th>
                <th>Email</th>
                <th>Department</th>
                <th>Designation</th>
                <th>Type</th>
                <th>Date of Join</th>
                <th>HOD Assigned</th>
                <th>Leave Balance</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $i = 1;
        while($fac = $facRes->fetch_assoc()):
            $initials = strtoupper(substr($fac['EmpName'],0,1));
            $isAssigned = !empty($fac['HodUsername']);
        ?>
        <tr>
            <td style="color:#94a3b8;"><?php echo $i++; ?></td>
            <td>
                <div class="emp-cell">
                    <div class="avatar"><?php echo $initials; ?></div>
                    <div class="emp-details">
                        <span class="emp-name"><?php echo htmlspecialchars($fac['EmpName']); ?></span>
                        <span class="emp-user">@<?php echo htmlspecialchars($fac['UserName']); ?></span>
                    </div>
                </div>
            </td>
            <td style="color:#64748b;font-size:.83rem;"><?php echo htmlspecialchars($fac['EmpEmail']); ?></td>
            <td><span class="badge badge-dept"><?php echo htmlspecialchars($fac['Dept']); ?></span></td>
            <td style="color:#64748b;font-size:.83rem;"><?php echo htmlspecialchars($fac['Designation']); ?></td>
            <td>
                <span class="badge badge-type"><?php echo htmlspecialchars($fac['EmpType']); ?></span>
            </td>
            <td style="color:#94a3b8;font-size:.8rem;">
                <?php echo $fac['DateOfJoin'] ? date('d M Y',strtotime($fac['DateOfJoin'])) : '—'; ?>
            </td>
            <td>
                <?php if($isAssigned): ?>
                <span class="badge badge-assigned">✅ <?php echo htmlspecialchars($fac['HodUsername']); ?></span>
                <?php else: ?>
                <span class="badge badge-unassigned">⚠️ Not Assigned</span>
                <?php endif; ?>
            </td>
            <td style="font-size:.8rem;color:#64748b;">
                <div>Medical: <strong><?php echo $fac['SickLeave']; ?></strong></div>
                <div>Casual: <strong><?php echo $fac['CasualLeave']; ?></strong></div>
                <div>Earn: <strong><?php echo $fac['EarnLeave']; ?></strong></div>
            </td>
            <td style="white-space:nowrap;">
                <a href="assign_hod.php" class="action-link">🔗 Assign</a>
                &nbsp;
                <a href="empdelete.php" class="delete-link">🗑 Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty-state">
        <div class="icon">👥</div>
        <p>
        <?php if($search !== '' || $filterDept !== 'all' || $filterHod !== 'all' || $filterAsgn !== 'all'): ?>
            No faculty found matching your filters.
            <a href="faculty_list.php" style="color:#10b981;font-weight:600;">Clear filters</a>
        <?php else: ?>
            No faculty registered yet.
            <a href="register" style="color:#10b981;font-weight:600;">Add Faculty →</a>
        <?php endif; ?>
        </p>
    </div>
    <?php endif; ?>
    </div>

</div>
</body>
</html>