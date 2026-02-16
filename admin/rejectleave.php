<link rel="shortcut icon" type="image/png" href="favicon.png"/>
<?php
session_start();
?>
<html>
<head>
<title>::Leave Management::</title>
</head>
<body>
<link rel = "stylesheet" href = "style.css">
<div class = "textview">
<?php
echo "<h1>Leave Management System</h1>";
include 'adminnavi.php';
include 'connect.php';
include 'mailer.php';

if(filter_var($_GET['id'],FILTER_VALIDATE_INT) && filter_var($_GET['empid'],FILTER_VALIDATE_INT))
	{
		$id =$_GET['id'];
		$empid =$_GET['empid'];
	}
else
	{
		header('location:home.php');
	}
if(isset($_SESSION['adminuser']))
	{
	
	$sql = "SELECT * FROM emp_leaves WHERE id='".$id."'";
	$result = $conn->query($sql);
	if($result->num_rows > 0)
		{
		while($row = $result->fetch_assoc())
			{
				$sql2 = "SELECT id,EmpEmail FROM employees WHERE id = '".$empid."'";
				$result2 = $conn->query($sql2);
				if($result2->num_rows > 0)
					{
						while($row2 = $result2->fetch_assoc())
							{
							$email = $row2['EmpEmail'];
							$sql3 = "UPDATE emp_leaves SET Status = 'Rejected' WHERE id = '".$id."'";
							if($conn->query($sql3) === TRUE)
									{
									$msg = "<html><body style='font-family:Arial,sans-serif'><div style='max-width:600px'><h2 style='color:#e74c3c;text-align:center'>❌ Leave Request Status</h2><p>Dear <strong>".$row['EmpName']."</strong>,</p><p>Thank you for your leave request. We regret to inform you that your leave request has been <strong style='color:#e74c3c'>NOT APPROVED</strong> at this time.</p><table style='width:100%;border-collapse:collapse;margin:20px 0'><tr style='background:#f5f5f5'><td style='padding:10px;font-weight:bold;border:1px solid #ddd'>Leave Type:</td><td style='padding:10px;border:1px solid #ddd'>".$row['LeaveType']."</td></tr><tr><td style='padding:10px;font-weight:bold;border:1px solid #ddd;background:#f5f5f5'>Number of Days:</td><td style='padding:10px;border:1px solid #ddd'>".$row['LeaveDays']." days</td></tr><tr style='background:#f5f5f5'><td style='padding:10px;font-weight:bold;border:1px solid #ddd'>Requested Start Date:</td><td style='padding:10px;border:1px solid #ddd'>".$row['StartDate']."</td></tr><tr><td style='padding:10px;font-weight:bold;border:1px solid #ddd;background:#f5f5f5'>Requested End Date:</td><td style='padding:10px;border:1px solid #ddd'>".$row['EndDate']."</td></tr></table><p>Please contact your department head or HR for more information, or feel free to submit another request for alternative dates.</p><p style='color:#666;font-size:12px;margin-top:30px'>Best regards,<br><strong>Leave Management System</strong><br>Human Resources Department</p></div></body></html>";
									$status = mailer($email,$msg);
									if($status === TRUE)
										{
										echo "The Leave Request Status Mail For ".$row['EmpName']." Has been sent to his/her registered email address !<br/>";
										}
									}	
							}
					}
			}
		}
	else
		{
			echo "<div style='background:#fff;padding:40px;text-align:center;border-radius:8px;margin:20px 0;'>";
			echo "<h3 style='color:#e74c3c;margin-bottom:10px;'>❌ Leave Request Not Found</h3>";
			echo "<p style='color:#666;font-size:14px;margin-bottom:15px;'>The leave request you are trying to reject does not exist or has already been processed.</p>";
			echo "<a href='view_leaves.php' style='display:inline-block;background:#3498db;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;margin-top:10px;'>← Back to Requests</a>";
			echo "</div>";
		}
	}
else
	{
	header('location:index.php?err='.urlencode('Please Login First To Access This Page !'));
	}
?>
</div>
</body>
</html>