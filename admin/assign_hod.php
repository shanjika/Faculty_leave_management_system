<?php
session_start();
include 'connect.php';

if(!isset($_SESSION['adminuser']) || ($_SESSION['role'] ?? '') !== 'HR'){
    header('location:home.php');
    exit;
}

$msg = '';
$err = '';

// Handle assign submit
if(isset($_POST['assign'])){
    $empId       = (int)$_POST['emp_id'];
    $hodUsername = $conn->real_escape_string($_POST['hod_username']);
    $sql = "UPDATE employees SET HodUsername='$hodUsername' WHERE id=$empId";
    if($conn->query($sql)){
        $msg = "Faculty assigned to HOD successfully!";
    } else {
        $err = "Error updating assignment.";
    }
}

// Get all HODs
$hods = [];
$resHods = $conn->query("SELECT username, Dept FROM admins WHERE Role='HOD' OR Role IS NULL OR Role='' ORDER BY Dept");
if($resHods) while($r = $resHods->fetch_assoc()) $hods[] = $r;

// Get all employees
$emps = [];
$resEmps = $conn->query("SELECT id, EmpName, Dept, HodUsername FROM employees ORDER BY Dept, EmpName");
if($resEmps) while($r = $resEmps->fetch_assoc()) $emps[] = $r;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Assign Faculty to HOD</title>
<link rel="stylesheet" href="style.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Segoe UI',sans-serif; background:#f0f4f8; }
.page-wrap { max-width:860px; margin:0 auto; padding:36px 20px; }
h2 { color:#1e3a8a; font-size:1.6rem; margin-bottom:22px; text-align:center; }
.msg-box { background:#d1fae5; color:#065f46; border-radius:10px; padding:12px 18px; margin-bottom:18px; font-weight:600; }
.err-box { background:#fee2e2; color:#991b1b; border-radius:10px; padding:12px 18px; margin-bottom:18px; font-weight:600; }
table { width:100%; border-collapse:collapse; background:#fff; border-radius:14px; overflow:hidden; box-shadow:0 4px 18px rgba(0,0,0,.08); }
th { background:#1e3a8a; color:#fff; padding:13px 14px; text-align:left; font-size:.88rem; text-transform:uppercase; letter-spacing:.5px; }
td { padding:12px 14px; border-bottom:1px solid #f1f5f9; font-size:.93rem; color:#1e293b; }
tr:last-child td { border-bottom:none; }
tr:hover td { background:#f8faff; }
select { padding:7px 12px; border:2px solid #e2e8f0; border-radius:8px; font-size:.9rem; background:#fafafa; color:#1e293b; }
select:focus { border-color:#667eea; outline:none; }
.assign-btn {
    padding:7px 18px; background:linear-gradient(135deg,#667eea,#764ba2);
    color:#fff; border:none; border-radius:8px;
    font-weight:700; font-size:.88rem; cursor:pointer;
}
.assign-btn:hover { opacity:.88; }
.badge-assigned { background:#d1fae5; color:#065f46; padding:3px 10px; border-radius:99px; font-size:.75rem; font-weight:700; }
.badge-none     { background:#fee2e2; color:#991b1b; padding:3px 10px; border-radius:99px; font-size:.75rem; font-weight:700; }
</style>
</head>
<body>
<?php include 'adminnavi.php'; ?>
<div class="page-wrap">
<h2>🔗 Assign Faculty to HOD</h2>

<?php if($msg): ?><div class="msg-box">✅ <?php echo $msg; ?></div><?php endif; ?>
<?php if($err): ?><div class="err-box">❌ <?php echo $err; ?></div><?php endif; ?>

<table>
    <tr>
        <th>#</th>
        <th>Faculty Name</th>
        <th>Department</th>
        <th>Current HOD</th>
        <th>Assign to HOD</th>
        <th>Action</th>
    </tr>
    <?php foreach($emps as $i => $emp): ?>
    <tr>
        <td><?php echo $i+1; ?></td>
        <td><?php echo htmlspecialchars($emp['EmpName']); ?></td>
        <td><?php echo htmlspecialchars($emp['Dept']); ?></td>
        <td>
            <?php if($emp['HodUsername']): ?>
                <span class="badge-assigned"><?php echo htmlspecialchars($emp['HodUsername']); ?></span>
            <?php else: ?>
                <span class="badge-none">Not Assigned</span>
            <?php endif; ?>
        </td>
        <td>
            <form method="post" style="display:inline;">
                <input type="hidden" name="emp_id" value="<?php echo $emp['id']; ?>">
                <select name="hod_username">
                    <option value="">-- Select HOD --</option>
                    <?php foreach($hods as $hod): ?>
                    <option value="<?php echo htmlspecialchars($hod['username']); ?>"
                        <?php echo ($emp['HodUsername'] === $hod['username']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($hod['username']); ?> (<?php echo htmlspecialchars($hod['Dept']); ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="assign" class="assign-btn">Save</button>
            </form>
        </td>
        <td></td>
    </tr>
    <?php endforeach; ?>
</table>
</div>
</body>
</html>