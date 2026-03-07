<?php
session_start();
include 'connect.php';

// Only HR can delete employees
if(!isset($_SESSION['adminuser'])){
    header('location:index.php?err='.urlencode('Please login first!'));
    exit;
}
if(($_SESSION['role'] ?? 'HOD') !== 'HR'){
    header('location:home.php?msg='.urlencode('Only HR can delete faculty.'));
    exit;
}
?>
<link rel="shortcut icon" type="image/png" href="favicon.png"/>
<html>
<head>
<title>Delete Faculty – Leave Management</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
    background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    min-height:100vh; padding:40px 20px;
}
.main-container {
    max-width:560px; margin:20px auto;
    display:flex; flex-direction:column;
    align-items:center;
}
.card {
    background:#fff; border-radius:14px;
    padding:40px; width:100%;
    box-shadow:0 10px 40px rgba(0,0,0,.2);
}
h2 { color:#333; margin-bottom:8px; font-size:24px; }
.subtitle { color:#666; margin-bottom:28px; font-size:14px; }
.form-group { margin-bottom:24px; }
label {
    display:block; color:#333; font-weight:600;
    margin-bottom:8px; font-size:14px;
}
select {
    width:100%; padding:12px;
    border:2px solid #e0e0e0; border-radius:6px;
    font-size:14px; background:#fff; cursor:pointer;
    transition:all .3s ease;
}
select:focus { outline:none; border-color:#667eea; box-shadow:0 0 0 3px rgba(102,126,234,.1); }
.warning-box {
    background:#fff3cd; border:2px solid #ffc107;
    border-radius:6px; padding:15px;
    margin-bottom:22px; color:#856404;
    font-size:13px; line-height:1.6;
}
.warning-box strong { display:block; margin-bottom:5px; }
.button-group { display:flex; gap:10px; margin-top:28px; }
button {
    flex:1; padding:12px 20px; border:none;
    border-radius:6px; font-size:14px;
    font-weight:600; cursor:pointer;
    transition:all .3s ease;
}
.btn-delete {
    background:linear-gradient(135deg,#e74c3c,#c0392b);
    color:#fff;
}
.btn-delete:hover { transform:translateY(-2px); box-shadow:0 5px 15px rgba(231,76,60,.3); }
.btn-cancel { background:#f0f0f0; color:#333; }
.btn-cancel:hover { background:#e0e0e0; }
</style>
</head>
<body>
<?php include 'adminnavi.php'; ?>

<?php
if(isset($_SESSION['adminuser'])){

    // Handle delete
    if(isset($_POST['confirm_delete']) && isset($_POST['employee_id'])){
        $id = filter_var($_POST['employee_id'], FILTER_VALIDATE_INT);
        if($id){
            // Get employee username (correct column is UserName)
            $sql_user   = "SELECT UserName FROM employees WHERE id='".$id."'";
            $result_user = $conn->query($sql_user);
            if($result_user && $result_user->num_rows > 0){
                $row_user = $result_user->fetch_assoc();
                $uname    = $row_user['UserName'];
                // Delete profile pic if exists
                $file = "../client/pro-pic/".$uname.".jpg";
                if(file_exists($file)) unlink($file);
            }
            // Delete employee record
            $sql = "DELETE FROM employees WHERE id='".$id."'";
            if($conn->query($sql) === TRUE){
                header('location:home.php?msg='.urlencode('Faculty Successfully Removed!'));
            } else {
                header('location:home.php?msg='.urlencode('Error Removing Faculty!'));
            }
        } else {
            header('location:home.php?msg='.urlencode('Invalid Faculty ID!'));
        }
        $conn->close();
        exit();
    }
?>

<div class="main-container">
<div class="card">
    <h2>🗑️ Delete Faculty</h2>
    <p class="subtitle">Select a faculty member to permanently remove from the system</p>

    <div class="warning-box">
        <strong>⚠️ Warning:</strong>
        Deleting a faculty member will permanently remove all their records including leave history.
        This action cannot be undone.
    </div>

    <form method="post" action="">
        <div class="form-group">
            <label for="employee_id">Select Faculty:</label>
            <select name="employee_id" id="employee_id" required>
                <option value="">-- Choose a faculty member --</option>
                <?php
                $sql_emp    = "SELECT id, EmpName, UserName, Dept FROM employees ORDER BY EmpName ASC";
                $result_emp = $conn->query($sql_emp);
                if($result_emp && $result_emp->num_rows > 0){
                    while($row_emp = $result_emp->fetch_assoc()){
                        echo "<option value='".$row_emp['id']."'>"
                            .htmlspecialchars($row_emp['EmpName'])
                            ." (".htmlspecialchars($row_emp['Dept']).")"
                            ."</option>";
                    }
                }
                ?>
            </select>
        </div>

        <div class="button-group">
            <button type="submit" name="confirm_delete" class="btn-delete"
                onclick="return confirm('Are you sure you want to permanently delete this faculty member? This cannot be undone.');">
                🗑️ Delete Faculty
            </button>
        </div>
    </form>

    <div style="display:flex;justify-content:center;margin-top:28px;">
        <button type="button" class="btn-cancel"
            style="font-size:16px;padding:12px 36px;font-weight:700;"
            onclick="window.location.href='home.php';">
            ← Back
        </button>
    </div>
</div>
</div>

<?php
} else {
    header('location:index.php?err='.urlencode('Please login first!'));
}
?>
</body>
</html>