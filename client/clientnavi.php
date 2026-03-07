<!DOCTYPE html>
<html>
<head>

    <style>
    /* ===== CLIENT NAVBAR ENHANCED UI ===== */
    .client-nav {
        background: linear-gradient(90deg, #79b9f4, #6e9cce);
        padding: 0 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        font-family: "Segoe UI", Helvetica, Arial, sans-serif;
    }
    .client-nav ul {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        align-items: center;
    }
    .client-nav li {
        position: relative;
    }
    .client-nav a {
        display: block;
        padding: 14px 18px;
        color: #ffffff;
        text-decoration: none;
        font-size: 16px;
        font-weight: 500;
        border: none;
        background: none;
        outline: none;
        transition: background 0.18s, color 0.18s;
    }
    .client-nav a:hover {
        background-color: rgba(255,255,255,0.15);
        border-radius: 6px;
        color: #1976d2;
    }
    /* DROPDOWN */
    .client-nav .dropdown-content {
        display: none;
        position: absolute;
        top: 48px;
        left: 0;
        background-color: #bad2e1;
        min-width: 220px;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.25);
        overflow: hidden;
        z-index: 1000;
    }
    .client-nav .dropdown-content a {
        color: #1f2937;
        padding: 12px 16px;
        font-size: 15px;
        border-radius: 0;
        background: none;
        font-weight: 500;
        transition: background 0.18s, color 0.18s;
    }
    .client-nav .dropdown-content a.logout-btn {
        color: #d32f2f;
        font-weight: bold;
        background: none;
        border-top: 1px solid #e3eafc;
        margin-top: 6px;
        padding-top: 14px;
    }
    .client-nav .dropdown-content a.logout-btn:hover {
        background: #ffebee;
        color: #b71c1c;
    }
    .client-nav .dropdown-content a.back-btn {
        color: #1976d2;
        font-weight: bold;
        background: none;
        border-bottom: 1px solid #e3eafc;
        margin-bottom: 6px;
        padding-bottom: 14px;
    }
    .client-nav .dropdown-content a.back-btn:hover {
        background: #e3f2fd;
        color: #0d47a1;
    }
    /* SHOW DROPDOWN */
    .client-nav li:hover .dropdown-content {
        display: block;
    }
    </style>

<nav class="client-nav">
    <ul>
        <li><a href="index.php">Faculty Home</a></li>
        <li><a href="dashboard.php">📊 Dashboard</a></li>
        <li><a href="request_leave.php">Request Leave</a></li>
        <li>
            <a href="#">Me</a>
            <div class="dropdown-content">
                <a href="profile.php">👤 My Profile</a>
                <a href="my_leaves.php">My All Leave Requests</a>
                <a href="changepass.php">Change Password</a>
                <a href="javascript:history.back()" class="back-btn">← Back</a>
                <a href="logout.php" class="logout-btn">⎋ Logout</a>
            </div>
        </li>
    </ul>
</nav>
</html>