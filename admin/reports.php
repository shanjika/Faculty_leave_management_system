<?php
// admin/reports.php - Admin statistics dashboard
session_start();
if(!isset($_SESSION['adminuser'])){
    header('Location: index.php?err='.urlencode('Please Login First'));
    exit();
}
include 'connect.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>📊 Reports & Statistics - Leave Management</title>
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
            padding: 20px;
        }
        .header-section {
            padding: 20px;
        }
        .header-section h1 {
            color: #000;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .main-container {
            max-width: 1400px;
            margin: 30px auto;
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            margin-bottom: 25px;
        }
        .card h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 22px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            text-align: center;
            border-top: 4px solid #667eea;
        }
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25);
        }
        .stat-icon {
            font-size: 40px;
            margin-bottom: 12px;
        }
        .stat-label {
            color: #666;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        .stat-value {
            font-size: 38px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }
        .stat-percentage {
            font-size: 13px;
            color: #999;
        }
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 10px;
        }
        .progress-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        .status-pending { background: #ffc107; }
        .status-approved { background: #28a745; }
        .status-rejected { background: #dc3545; }
        .stats-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .stats-table tr {
            border-bottom: 1px solid #e0e0e0;
        }
        .stats-table tr:last-child {
            border-bottom: none;
        }
        .stats-table tr:hover {
            background: #f9f9f9;
        }
        .stats-table td {
            padding: 15px;
            text-align: left;
        }
        .stats-table td:first-child {
            font-weight: 600;
            color: #333;
            width: 50%;
        }
        .stats-table td:last-child {
            text-align: right;
            font-size: 18px;
            font-weight: 700;
            color: #667eea;
        }
        .leave-type-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .leave-type-card {
            background: linear-gradient(135deg, #f5f7fa 0%, #f0f4f8 100%);
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            text-align: center;
        }
        .leave-type-card h4 {
            color: #333;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: 600;
        }
        .leave-type-card .value {
            font-size: 28px;
            font-weight: 700;
            color: #667eea;
        }
        .leave-type-card .pct {
            font-size: 12px;
            color: #999;
            margin-top: 8px;
        }
        .dept-stats {
            margin-top: 15px;
        }
        .dept-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #e0e0e0;
            transition: all 0.2s ease;
        }
        .dept-row:hover {
            background: #f9f9f9;
            padding-left: 10px;
        }
        .dept-row:last-child {
            border-bottom: none;
        }
        .dept-name {
            font-weight: 600;
            color: #333;
            font-size: 15px;
        }
        .dept-count {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
    </style>
</head>
<body>
<?php include 'adminnavi.php'; ?>
<div class="header-section">
    <h1>Leave Management System</h1>
</div>

<div class="main-container">
    <!-- Overall Quick Stats -->
    <div class="card">
        <h2>📊 Leave Request Overview</h2>
        <div class="stats-grid">
        <?php
        $total = $conn->query("SELECT COUNT(*) as cnt FROM emp_leaves")->fetch_assoc()['cnt'];
        $pending = $conn->query("SELECT COUNT(*) as cnt FROM emp_leaves WHERE Status='Requested'")->fetch_assoc()['cnt'];
        $approved = $conn->query("SELECT COUNT(*) as cnt FROM emp_leaves WHERE Status IN ('Granted','Approved')")->fetch_assoc()['cnt'];
        $rejected = $conn->query("SELECT COUNT(*) as cnt FROM emp_leaves WHERE Status='Rejected'")->fetch_assoc()['cnt'];
        
        $pending_pct = $total > 0 ? round(($pending/$total)*100, 1) : 0;
        $approved_pct = $total > 0 ? round(($approved/$total)*100, 1) : 0;
        $rejected_pct = $total > 0 ? round(($rejected/$total)*100, 1) : 0;
        ?>
        
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-label">Total Requests</div>
            <div class="stat-value"><?php echo $total; ?></div>
            <div class="stat-percentage">All leave requests</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-label">Pending</div>
            <div class="stat-value"><?php echo $pending; ?></div>
            <div class="stat-percentage"><?php echo $pending_pct; ?>% of total</div>
            <div class="progress-bar">
                <div class="progress-fill status-pending" style="width: <?php echo $pending_pct; ?>%"></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-label">Approved</div>
            <div class="stat-value"><?php echo $approved; ?></div>
            <div class="stat-percentage"><?php echo $approved_pct; ?>% of total</div>
            <div class="progress-bar">
                <div class="progress-fill status-approved" style="width: <?php echo $approved_pct; ?>%"></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">❌</div>
            <div class="stat-label">Rejected</div>
            <div class="stat-value"><?php echo $rejected; ?></div>
            <div class="stat-percentage"><?php echo $rejected_pct; ?>% of total</div>
            <div class="progress-bar">
                <div class="progress-fill status-rejected" style="width: <?php echo $rejected_pct; ?>%"></div>
            </div>
        </div>
        </div>
    </div>

    <!-- Leave Type Statistics -->
    <div class="card">
        <h2>📅 Leave Type Statistics</h2>
        <?php
        $earnleave = $conn->query("SELECT COUNT(*) as cnt FROM emp_leaves WHERE LeaveType='Earn Leave'")->fetch_assoc()['cnt'];
        $sickleave = $conn->query("SELECT COUNT(*) as cnt FROM emp_leaves WHERE LeaveType='Sick Leave'")->fetch_assoc()['cnt'];
        $casualleave = $conn->query("SELECT COUNT(*) as cnt FROM emp_leaves WHERE LeaveType='Casual Leave'")->fetch_assoc()['cnt'];
        ?>
        <div class="leave-type-grid">
            <div class="leave-type-card">
                <h4>🏆 Earn Leave</h4>
                <div class="value"><?php echo $earnleave; ?></div>
                <div class="pct"><?php echo $total > 0 ? round(($earnleave/$total)*100, 1) : 0; ?>% of total</div>
            </div>
            <div class="leave-type-card">
                <h4>🏥 Sick Leave</h4>
                <div class="value"><?php echo $sickleave; ?></div>
                <div class="pct"><?php echo $total > 0 ? round(($sickleave/$total)*100, 1) : 0; ?>% of total</div>
            </div>
            <div class="leave-type-card">
                <h4>🎉 Casual Leave</h4>
                <div class="value"><?php echo $casualleave; ?></div>
                <div class="pct"><?php echo $total > 0 ? round(($casualleave/$total)*100, 1) : 0; ?>% of total</div>
            </div>
        </div>
    </div>

    <!-- Monthly Statistics -->
    <div class="card">
        <h2>📈 Monthly Requests (Current Year)</h2>
        <table class="stats-table">
            <tr>
                <td>Current Month</td>
                <td>
                    <?php
                    $current_month = $conn->query("SELECT COUNT(*) as cnt FROM emp_leaves WHERE MONTH(RequestDate)=MONTH(CURDATE()) AND YEAR(RequestDate)=YEAR(CURDATE())")->fetch_assoc()['cnt'];
                    echo $current_month;
                    ?>
                </td>
            </tr>
            <tr>
                <td>Last 30 Days</td>
                <td>
                    <?php
                    $last_30 = $conn->query("SELECT COUNT(*) as cnt FROM emp_leaves WHERE RequestDate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")->fetch_assoc()['cnt'];
                    echo $last_30;
                    ?>
                </td>
            </tr>
            <tr>
                <td>Last 90 Days</td>
                <td>
                    <?php
                    $last_90 = $conn->query("SELECT COUNT(*) as cnt FROM emp_leaves WHERE RequestDate >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)")->fetch_assoc()['cnt'];
                    echo $last_90;
                    ?>
                </td>
            </tr>
            <tr>
                <td>This Year</td>
                <td>
                    <?php
                    $this_year = $conn->query("SELECT COUNT(*) as cnt FROM emp_leaves WHERE YEAR(RequestDate)=YEAR(CURDATE())")->fetch_assoc()['cnt'];
                    echo $this_year;
                    ?>
                </td>
            </tr>
        </table>
    </div>

    <!-- Department Wise Statistics -->
    <div class="card">
        <h2>🏢 Department Wise Statistics</h2>
        <div class="dept-stats">
            <?php
            $dept_res = $conn->query("SELECT e.Dept, COUNT(el.id) as cnt FROM employees e LEFT JOIN emp_leaves el ON e.id=el.EmpId GROUP BY e.Dept ORDER BY cnt DESC");
            if($dept_res && $dept_res->num_rows > 0){
                while($dept = $dept_res->fetch_assoc()){
                    $dept_count = $dept['cnt'] ?? 0;
                    echo "<div class='dept-row'>";
                    echo "<span class='dept-name'>".$dept['Dept']."</span>";
                    echo "<span class='dept-count'>".$dept_count." requests</span>";
                    echo "</div>";
                }
            }
            ?>
        </div>
    </div>

    <!-- Status Distribution -->
    <div class="card">
        <h2>📊 Status Distribution</h2>
        <table class="stats-table">
            <tr>
                <td>Pending Requests</td>
                <td><?php echo $pending; ?></td>
            </tr>
            <tr>
                <td>Approved Requests</td>
                <td><?php echo $approved; ?></td>
            </tr>
            <tr>
                <td>Rejected Requests</td>
                <td><?php echo $rejected; ?></td>
            </tr>
            <tr style="background:#f3f3f3;font-weight:700;">
                <td>Total</td>
                <td><?php echo $total; ?></td>
            </tr>
        </table>
    </div>

</div>

</body>
</html>
