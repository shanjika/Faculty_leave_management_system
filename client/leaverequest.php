<?php
echo "<link rel = 'stylesheet' href= 'style.css'>";
echo "<link rel = 'stylesheet' href= 'table.css'>";
echo "<center>";
echo "<div class = 'textview'>";
include 'connect.php';
echo "<h1>Leave Management System</h1>";
include 'clientnavi.php';
session_start();

if(isset($_SESSION['user']))
	{
	$user = $_SESSION['user'];
	$sql = "SELECT * FROM employees WHERE UserName = '".$user."'";
	$result = $conn->query($sql);
	if($result->num_rows > 0)
		{
		while($row = $result->fetch_assoc())
			{
			   echo "<body>";
			   echo "<h2 style='text-align:center; margin-top:2rem; font-size:2.2rem;'>Request For A Leave for : " . $_POST['type'] . "</h2>";
			   echo "<form action='request_confirm.php' method='post' style='display:flex; justify-content:center; align-items:center; min-height:60vh;'>";
			   echo "<div style='background:#f8f9fa; padding:2.5rem 2.5rem 2rem 2.5rem; border-radius:20px; box-shadow:0 4px 24px rgba(0,0,0,0.10); width:400px;'>";
			   echo "<input type='hidden' name='empname' value='" . $row["EmpName"] . "'>";
			   echo "<input type='hidden' name='designation' value='" . $row["Designation"] . "'>";
			   echo "<input type='hidden' name='dept' value='" . $row["Dept"] . "'>";
			   echo "<input type='hidden' name='emptype' value='" . $row["EmpType"] . "'>";
			   echo "<input type='hidden' name='empfee' value='" . $row["EmpFee"] . "'>";
			   echo "<input type='hidden' name='leavetype' value='" . $_POST['type'] . "'>";
			   echo "<div style='margin-bottom:1.5rem;'>";
			   echo "<label style='font-weight:600; font-size:1.1rem;'>* Starting Date :</label><br>";
			   echo "<div style='display:flex; gap:0.5rem;'>";
			   echo "<select name='leavedate' class='textbox shadow selected' style='width:60px; font-size:1rem;'>";
			   for($d=1;$d<=31;$d++) echo "<option value='".str_pad($d,2,'0',STR_PAD_LEFT)."'>".$d."</option>";
			   echo "</select>";
			   echo "<select name='leavemonth' class='textbox shadow selected' style='width:60px; font-size:1rem;'>";
			   for($m=1;$m<=12;$m++) echo "<option value='".str_pad($m,2,'0',STR_PAD_LEFT)."'>".$m."</option>";
			   echo "</select>";
			   echo "<select name='leaveyear' class='textbox shadow selected' style='width:80px; font-size:1rem;'>";
			   echo "<option value='".date('Y')."'>".date('Y')."</option>";
			   echo "</select>";
			   echo "</div></div>";
			   echo "<div style='margin-bottom:1.5rem;'>";
			   echo "<label style='font-weight:600; font-size:1.1rem;'>* Ending Date :</label><br>";
			   echo "<div style='display:flex; gap:0.5rem;'>";
			   echo "<select name='enddate' class='textbox shadow selected' style='width:60px; font-size:1rem;'>";
			   for($d=1;$d<=31;$d++) echo "<option value='".str_pad($d,2,'0',STR_PAD_LEFT)."'>".$d."</option>";
			   echo "</select>";
			   echo "<select name='endmonth' class='textbox shadow selected' style='width:60px; font-size:1rem;'>";
			   for($m=1;$m<=12;$m++) echo "<option value='".str_pad($m,2,'0',STR_PAD_LEFT)."'>".$m."</option>";
			   echo "</select>";
			   echo "<select name='endyear' class='textbox shadow selected' style='width:80px; font-size:1rem;'>";
			   echo "<option value='".date('Y')."'>".date('Y')."</option>";
			   echo "</select>";
			   echo "</div></div>";
			   echo "<div style='margin-bottom:2rem;'>";
			   echo "<label style='font-weight:600; font-size:1.1rem;'>* Reason For Leave :</label><br>";
			   echo "<textarea name='leavereason' class='textbox shadow selected' rows='3' style='width:100%; font-size:1rem; border-radius:8px; padding:0.5rem;'></textarea>";
			   echo "</div>";
			   echo "<div style='text-align:center;'><input type='submit' value='Request a Leave' class='login-button shadow' style='font-size:1.1rem; padding:0.7rem 2.5rem; border-radius:10px; background:#1976d2; color:#fff; border:none; box-shadow:0 2px 8px rgba(25,118,210,0.08); cursor:pointer;'></div>";
			   echo "</div>";
			   echo "</form>";
			   echo "</body>";
			}
		}
	}
else
	{
	header('location:index.php?err='.urlencode('Please Login First To Access This Site !'));
	}
?>
<title>::Leave Management::</title>
<script type="text/javascript">
        function noBack()
         {
             window.history.forward()
         }
        noBack();
        window.onload = noBack;
        window.onpageshow = function(evt) { if (evt.persisted) noBack() }
        window.onunload = function() { void (0) }
    </script>