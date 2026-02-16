<?php // Removed extraneous '>' symbol at top left ?>
<link rel="shortcut icon" type="image/png" href="favicon.png"/>
<?php
session_start();
?>
<html>
<head>
<title>::Leave Management::</title>
<style>
	* {
		margin: 0;
		padding: 0;
		box-sizing: border-box;
	}
	body {
		font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
		background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
		min-height: 100vh;
		padding: 40px 20px;
	}
	.layout {
		max-width: 1200px;
		margin: 0 auto;
		display: flex;
		flex-direction: column; /* stack vertically */
		gap: 40px;
		align-items: flex-start;
		padding-top: 10px;
	}
	.left {
		width: 100%;
	}
	.right {
		width: 100%;
		display: flex;
		justify-content: center;
		align-items: flex-start;
	}
	.container {
		max-width: 520px;
		width: 100%;
	}
	.card {
		background: white;
		border-radius: 12px;
		padding: 40px;
		box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
	}
	h2 {
		color: #333;
		margin-bottom: 10px;
		font-size: 24px;
	}
	.subtitle {
		color: #666;
		margin-bottom: 30px;
		font-size: 14px;
	}
	.form-group {
		margin-bottom: 25px;
	}
	label {
		display: block;
		color: #333;
		font-weight: 600;
		margin-bottom: 8px;
		font-size: 14px;
	}
	select {
		width: 100%;
		padding: 12px;
		border: 2px solid #e0e0e0;
		border-radius: 6px;
		font-size: 14px;
		background: white;
		cursor: pointer;
		transition: all 0.3s ease;
	}
	select:hover {
		border-color: #764ba2;
	}
	select:focus {
		outline: none;
		border-color: #667eea;
		box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
	}
	.button-group {
		display: flex;
		gap: 10px;
		margin-top: 30px;
	}
	button {
		flex: 1;
		padding: 12px 20px;
		border: none;
		border-radius: 6px;
		font-size: 14px;
		font-weight: 600;
		cursor: pointer;
		transition: all 0.3s ease;
	}
	.btn-delete {
		background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
		color: white;
	}
	.btn-delete:hover {
		transform: translateY(-2px);
		box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
	}
	.btn-delete:active {
		transform: translateY(0);
	}
	.btn-cancel {
		background: #f0f0f0;
		color: #333;
	}
	.btn-cancel:hover {
		background: #e0e0e0;
	}
	.warning-box {
		background: #fff3cd;
		border: 2px solid #ffc107;
		border-radius: 6px;
		padding: 15px;
		margin-bottom: 20px;
		color: #856404;
		font-size: 13px;
		line-height: 1.6;
	}
	.warning-box strong {
		display: block;
		margin-bottom: 5px;
	}
	.textview {
		padding: 10px 0 0 0;
	}
	.header-section {
		padding: 20px 0 10px 0;
	}
	.header-section h1 {
		font-size: 48px;
		color: #0b0b0b;
		margin-bottom: 8px;
	}
	.main-container {
		max-width: 1200px;
		margin: 20px auto;
		padding: 0 20px;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: flex-start;
		min-height: 60vh;
	}
	.container {
		max-width: 520px;
		width: 100%;
		display: flex;
		flex-direction: column;
		align-items: center;
	}
</style>
</head>
<body>
<?php
include 'connect.php';

if(isset($_SESSION['adminuser']))
	{
	// Handle deletion request
	if(isset($_POST['confirm_delete']) && isset($_POST['employee_id']))
		{
		$id = filter_var($_POST['employee_id'], FILTER_VALIDATE_INT);
		if($id)
			{
			// Get employee username for profile pic deletion
			$sql_user = "SELECT EmpUserName FROM employees WHERE id='".$id."'";
			$result_user = $conn->query($sql_user);
			if($result_user->num_rows > 0)
				{
				$row_user = $result_user->fetch_assoc();
				$user = $row_user['EmpUserName'];
				$file = "../client/pro-pic/".$user.".jpg";
				if(file_exists($file))
					{
					unlink($file);
					}
				}
			// Delete employee
			$sql = "DELETE FROM employees WHERE id='".$id."'";
			if ($conn->query($sql) === TRUE)
				{
				header('location:home.php?msg='.urlencode('Employee Successfully Removed !'));
				}
			else
				{
				header('location:home.php?msg='.urlencode('Error Removing Employee !'));
				}
			}
		else
			{
			header('location:home.php?msg='.urlencode('Invalid Employee ID !'));
			}
		$conn->close();
		exit();
		}
	

	// Remove top Back buttons, add single Back button at bottom
	echo "<div class='main-container'>";
	echo "<div class='container'>";
	?>
		<div class="card">
			<h2>🗑️ Remove Employee</h2>
			<p class="subtitle">Select an employee to remove from the system</p>
			<div class="warning-box">
				<strong>⚠️ Warning:</strong> Deleting an employee will permanently remove all their records including leave history. This action cannot be undone.
			</div>
			<form method="post" action="">
				<div class="form-group">
					<label for="employee_id">Select Employee:</label>
					<select name="employee_id" id="employee_id" required>
						<option value="">-- Choose an employee --</option>
						<?php
						$sql_emp = "SELECT id, EmpName, EmpEmail FROM employees ORDER BY EmpName ASC";
						$result_emp = $conn->query($sql_emp);
						if($result_emp->num_rows > 0)
							{
							while($row_emp = $result_emp->fetch_assoc())
								{
								echo "<option value='".$row_emp['id']."'>".$row_emp['EmpName']." (".$row_emp['EmpEmail'].")</option>";
								}
							}
						?>
					</select>
				</div>
				<div class="button-group">
					<button type="submit" name="confirm_delete" class="btn-delete" onclick="return confirm('Are you sure you want to permanently delete this employee? This action cannot be undone.');">Delete Employee</button>
					
				</div>
			</form>
			<div style="display: flex; justify-content: center; margin-top: 32px;">
				<button type="button" class="btn-cancel" style="font-size: 18px; padding: 12px 36px; font-weight: 700;" onclick="window.location.href='home.php';">← Back</button>
			</div>
		</div>
	</div>
	<?php
	echo "</div>"; // .container
	echo "</div>"; // .main-container

	}
else
	{
	header('location:index.php?err='.urlencode('Please login first to access this page !'));
	}
?>