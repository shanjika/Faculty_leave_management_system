<?php
session_start();
include 'connect.php';
if(!isset($_SESSION['user'])){
    header('Location: index.php?err='.urlencode('Please Login First To Access This Page !'));
    exit();
}
$user = $_SESSION['user'];
$sql = "SELECT * FROM employees WHERE UserName = '".$conn->real_escape_string($user)."'";
$res = $conn->query($sql);
$emp = $res && $res->num_rows ? $res->fetch_assoc() : null;

$total = 0; $used = 0;
if($emp){
    $earn = (int)$emp['EarnLeave'];
    $sick = (int)$emp['SickLeave'];
    $casual = (int)$emp['CasualLeave'];
    $total = $earn + $sick + $casual;
    $empName = $conn->real_escape_string($emp['EmpName']);
    $q = "SELECT SUM(LeaveDays) as used FROM emp_leaves WHERE EmpName='".$empName."' AND Status IN ('Granted','Approved')";
    $r = $conn->query($q);
    $row = $r && $r->num_rows ? $r->fetch_assoc() : null;
    $used = $row && $row['used'] ? (int)$row['used'] : 0;
}
$remaining = max(0, $total - $used);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Balance Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="table.css">
    <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; color: #333; }
    .page-wrapper { display: flex; flex-direction: column; min-height: 100vh; }
    .page-content { flex: 1; padding: 30px 20px; }
    .container { max-width: 1200px; margin: 0 auto; }
    
    .banner {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
    }
    
    .banner h1 { font-size: 32px; margin-bottom: 8px; }
    .banner p { font-size: 14px; opacity: 0.9; }
    
    h2 { font-size: 22px; margin: 30px 0 20px 0; color: #1a202c; font-weight: 600; }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 25px;
        margin-bottom: 40px;
    }
    
    .stat-card {
        background: #fff;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s, box-shadow 0.3s;
        border-left: 5px solid #667eea;
    }
    
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12); }
    
    .stat-card.used { border-left-color: #f6ad55; }
    .stat-card.remaining { border-left-color: #48bb78; }
    
    .stat-label { font-size: 12px; text-transform: uppercase; color: #718096; font-weight: 600; letter-spacing: 0.5px; }
    .stat-value { font-size: 42px; font-weight: 700; color: #2d3748; margin: 15px 0; }
    .stat-detail { font-size: 13px; color: #718096; line-height: 1.6; }
    
    .progress-container { margin: 15px 0; }
    .progress-bar {
        background: #e2e8f0;
        height: 8px;
        border-radius: 4px;
        overflow: hidden;
        margin: 8px 0;
    }
    
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        border-radius: 4px;
        transition: width 0.5s ease;
    }
    
    .stat-card.used .progress-fill { background: linear-gradient(90deg, #f6ad55 0%, #ed8936 100%); }
    .stat-card.remaining .progress-fill { background: linear-gradient(90deg, #48bb78 0%, #38a169 100%); }
    
    .progress-label { display: flex; justify-content: space-between; font-size: 12px; color: #718096; }
    
    .table-card {
        background: #fff;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        overflow-x: auto;
    }
    
    .table-card h3 { margin-bottom: 20px; color: #2d3748; font-size: 18px; }
    
    .table-card table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table-card thead {
        background: #f7fafc;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .table-card th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #4a5568;
        font-size: 13px;
        text-transform: uppercase;
    }
    
    .table-card td {
        padding: 15px 12px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 14px;
    }
    
    .table-card tbody tr:hover { background: #f7fafc; }
    
    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .status-requested { background: #bee3f8; color: #2c5282; }
    .status-granted { background: #c6f6d5; color: #22543d; }
    .status-approved { background: #c6f6d5; color: #22543d; }
    .status-rejected { background: #fed7d7; color: #742a2a; }
    
    .action-link {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s;
    }
    
    .action-link:hover { color: #764ba2; text-decoration: underline; }
    
    .empty-state {
        text-align: center;
        padding: 40px;
        color: #718096;
    }
    
    .empty-state p { font-size: 16px; }
    
    @media (max-width: 768px) {
        .banner { padding: 20px; }
        .banner h1 { font-size: 24px; }
        .page-content { padding: 20px 15px; }
        .stat-value { font-size: 32px; }
        .stats-grid { grid-template-columns: 1fr; gap: 15px; }
    }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <?php include 'clientnavi.php'; ?>
        
        <div class="page-content">
            <div class="container">
                <?php if(!$emp): ?>
                    <div class="banner">
                        <h1>Dashboard</h1>
                        <p>Employee record not found. Please contact HR.</p>
                    </div>
                <?php else: ?>
                    
                    <div class="banner">
                        <h1>Welcome, <?php echo htmlspecialchars($emp['EmpName']); ?></h1>
                        <p>Your Leave Balance Overview</p>
                    </div>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-label">📊 Total Leaves</div>
                            <div class="stat-value"><?php echo $total; ?></div>
                            <div class="stat-detail">
                                Earn: <strong><?php echo (int)$emp['EarnLeave']; ?></strong> | 
                                Sick: <strong><?php echo (int)$emp['SickLeave']; ?></strong> | 
                                Casual: <strong><?php echo (int)$emp['CasualLeave']; ?></strong>
                            </div>
                        </div>
                        
                        <div class="stat-card used">
                            <div class="stat-label">✓ Used Leaves</div>
                            <div class="stat-value"><?php echo $used; ?></div>
                            <div class="progress-container">
                                <div class="progress-label">
                                    <span>Progress</span>
                                    <span><?php echo ($total>0 ? round(($used/$total)*100) : 0); ?>%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo ($total>0 ? ($used/$total)*100 : 0); ?>%"></div>
                                </div>
                            </div>
                            <div class="stat-detail">Approved & granted requests</div>
                        </div>
                        
                        <div class="stat-card remaining">
                            <div class="stat-label">🎯 Remaining Leaves</div>
                            <div class="stat-value"><?php echo $remaining; ?></div>
                            <div class="progress-container">
                                <div class="progress-label">
                                    <span>Available</span>
                                    <span><?php echo ($total>0 ? round(($remaining/$total)*100) : 0); ?>%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo ($total>0 ? ($remaining/$total)*100 : 0); ?>%"></div>
                                </div>
                            </div>
                            <div class="stat-detail">Available quota remaining</div>
                        </div>
                    </div>
                    
                    <h2>📋 Recent Leave Requests</h2>
                    
                    <div class="table-card">
                        <?php
                        $sql2 = "SELECT * FROM emp_leaves WHERE EmpName='".$conn->real_escape_string($emp['EmpName'])."' ORDER BY RequestDate DESC LIMIT 10";
                        $res2 = $conn->query($sql2);
                        
                        if($res2 && $res2->num_rows){
                            echo "<h3>Last 10 Requests</h3>";
                            echo "<table>";
                            echo "<thead><tr><th>Request Date</th><th>Leave Type</th><th>Days</th><th>Start Date</th><th>End Date</th><th>Status</th><th>Download</th></tr></thead>";
                            echo "<tbody>";
                            while($rr = $res2->fetch_assoc()){
                                $statusClass = 'status-'.strtolower(str_replace(' ', '', $rr['Status']));
                                echo "<tr>";
                                echo "<td>".htmlspecialchars($rr['RequestDate'])."</td>";
                                echo "<td>".htmlspecialchars($rr['LeaveType'])."</td>";
                                echo "<td><strong>".htmlspecialchars($rr['LeaveDays'])."</strong></td>";
                                echo "<td>".htmlspecialchars($rr['StartDate'])."</td>";
                                echo "<td>".htmlspecialchars($rr['EndDate'])."</td>";
                                echo "<td><span class='status-badge $statusClass'>".htmlspecialchars($rr['Status'])."</span></td>";
                                echo "<td><a href='download.php?id=".urlencode($rr['id'])."' class='action-link'>📄 Download</a></td>";
                                echo "</tr>";
                            }
                            echo "</tbody>";
                            echo "</table>";
                        } else {
                            echo "<div class='empty-state'><p>No leave requests yet. <a href='request_leave.php' class='action-link'>Submit your first request →</a></p></div>";
                        }
                        ?>
                    </div>
                    
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
