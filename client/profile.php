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
    <style>
        body { background: #f6fbff; }
        .profile-main {
            max-width: 700px;
            margin: 40px auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 32px 36px 28px 36px;
        }
        .profile-flex {
            display: flex;
            gap: 32px;
            align-items: flex-start;
            flex-wrap: wrap;
        }
        .profile-photo {
            min-width: 180px;
            text-align: center;
        }
        .profile-photo img {
            width: 160px;
            height: 160px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid #e3eafc;
        }
        .profile-photo .no-photo {
            width: 160px;
            height: 160px;
            background: #e3eafc;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            color: #888;
            font-size: 1.1rem;
        }
        .profile-info {
            flex: 1;
            min-width: 260px;
        }
        .profile-info h3 {
            margin-top: 0;
            margin-bottom: 10px;
        }
        .profile-info p {
            margin: 6px 0 0 0;
            font-size: 1.08rem;
        }
        .profile-info hr {
            margin: 18px 0;
            border: none;
            border-top: 1px solid #e3eafc;
        }
        .form-row { margin-bottom: 14px; }
        label { font-weight: 500; color: #1976d2; }
        input[type="text"] {
            width: 100%;
            padding: 7px 10px;
            border-radius: 7px;
            border: 1px solid #b0c4de;
            font-size: 1rem;
        }
        .btn {
            background: #1976d2;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 28px;
            font-size: 1.08rem;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(25,118,210,0.08);
        }
        .btn:hover { background: #1565c0; }
        .msg { background: #e3f2fd; color: #1976d2; padding: 8px 16px; border-radius: 7px; margin-bottom: 18px; }
        .error { color: #d32f2f; background: #ffebee; padding: 8px 16px; border-radius: 7px; margin-bottom: 18px; }
    </style>
</head>
<body>
<?php include 'clientnavi.php'; ?>
<div class="profile-main">
    <h1 style="margin-top:0;">My Profile</h1>
    <?php if($msg) echo "<div class='msg'>".htmlspecialchars($msg)."</div>"; ?>
    <?php if(!$emp) { echo "<p class='error'>Profile not found.</p>"; } else { ?>
    <div class="profile-flex">
        <div class="profile-photo">
            <h3>Photo</h3>
            <?php $pic = 'pro-pic/'.$_SESSION['user'].'.jpg'; if(file_exists($pic)) echo "<img src='$pic' alt='Profile Photo'>"; else echo "<div class='no-photo'>No Photo</div>"; ?>
            <p style="margin-top:10px;"><a href="change_pp.php">Change</a> • <a href="delete_pp.php">Delete</a></p>
        </div>
        <div class="profile-info">
            <h3>Personal Info</h3>
            <p><b>Name:</b> <?php echo htmlspecialchars($emp['EmpName']); ?></p>
            <p><b>Email:</b> <?php echo htmlspecialchars($emp['EmpEmail']); ?></p>
            <p><b>Department:</b> <?php echo htmlspecialchars($emp['Dept']); ?></p>
            <hr>
            <h3>Update Contact</h3>
            <form method="post">
                <div class="form-row">
                    <label>Phone</label><br>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($emp['PhoneNumber'] ?? ''); ?>">
                </div>
                <div class="form-row">
                    <label>Emergency Contact Name</label><br>
                    <input type="text" name="emname" value="<?php echo htmlspecialchars($emp['EmergencyContactName'] ?? ''); ?>">
                </div>
                <div class="form-row">
                    <label>Emergency Contact Phone</label><br>
                    <input type="text" name="emphone" value="<?php echo htmlspecialchars($emp['EmergencyContact'] ?? ''); ?>">
                </div>
                <div class="form-row"><input type="submit" value="Save" class="btn"></div>
            </form>
        </div>
    </div>
    <?php } ?>
</div>
</body>
</html>
