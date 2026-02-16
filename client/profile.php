<?php
session_start();
include 'connect.php';
if(!isset($_SESSION['user'])){ header('Location: index.php?err='.urlencode('Please Login First')); exit(); }
$user = $conn->real_escape_string($_SESSION['user']);
$msg = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $phone = $conn->real_escape_string($_POST['phone']);
    $emname = $conn->real_escape_string($_POST['emname']);
    $emphone = $conn->real_escape_string($_POST['emphone']);
    $sql = "UPDATE employees SET PhoneNumber='$phone', EmergencyContactName='$emname', EmergencyContact='$emphone' WHERE UserName='$user'";
    if($conn->query($sql)) $msg = 'Profile updated successfully.'; else $msg = 'Update failed.';
}
$res = $conn->query("SELECT * FROM employees WHERE UserName='$user'");
$emp = $res && $res->num_rows ? $res->fetch_assoc() : null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <link rel="stylesheet" href="style.css">
    <style>.form-row{margin-bottom:12px}.small{font-size:13px;color:#666}</style>
    </head>
<body>
<?php include 'clientnavi.php'; ?>
<div class="container">
    <h1>My Profile</h1>
    <?php if($msg) echo "<div class='msg'>".htmlspecialchars($msg)."</div>"; ?>
    <?php if(!$emp) { echo "<p class='error'>Profile not found.</p>"; } else { ?>
    <div style="display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap">
        <div style="min-width:200px">
            <h3>Photo</h3>
            <?php $pic = 'pro-pic/'.$_SESSION['user'].'.jpg'; if(file_exists($pic)) echo "<img src='$pic' height=160>"; else echo "<div style='width:160px;height:160px;background:#eee;display:flex;align-items:center;justify-content:center'>No Photo</div>"; ?>
            <p><a href="change_pp.php">Change</a> • <a href="delete_pp.php">Delete</a></p>
        </div>
        <div style="flex:1;min-width:280px">
            <h3>Personal Info</h3>
            <p><b>Name:</b> <?php echo htmlspecialchars($emp['EmpName']); ?></p>
            <p><b>Email:</b> <?php echo htmlspecialchars($emp['EmpEmail']); ?></p>
            <p><b>Department:</b> <?php echo htmlspecialchars($emp['Dept']); ?></p>
            <hr>
            <h3>Update Contact</h3>
            <form method="post">
                <div class="form-row">
                    <label>Phone</label><br>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($emp['PhoneNumber']); ?>">
                </div>
                <div class="form-row">
                    <label>Emergency Contact Name</label><br>
                    <input type="text" name="emname" value="<?php echo htmlspecialchars($emp['EmergencyContactName']); ?>">
                </div>
                <div class="form-row">
                    <label>Emergency Contact Phone</label><br>
                    <input type="text" name="emphone" value="<?php echo htmlspecialchars($emp['EmergencyContact']); ?>">
                </div>
                <div class="form-row"><input type="submit" value="Save" class="btn"></div>
            </form>
        </div>
    </div>
    <?php } ?>
</div>
</body>
</html>
